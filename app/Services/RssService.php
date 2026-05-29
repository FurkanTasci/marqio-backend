<?php

namespace App\Services;

use App\Models\RssSource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimplePie;

class RssService
{
    /**
     * Fetch feeds for the given sources.
     * Uses optimized concurrent fetching for uncached sources.
     *
     * @param  Collection<int, RssSource>  $sources
     * @return array
     */
    public function fetchFeeds(Collection $sources): array
    {
        $results = [];
        $uncachedSources = collect();

        // 1. Check Cache
        foreach ($sources as $source) {
            $cacheKey = $this->getCacheKey($source);
            $cachedData = Cache::get($cacheKey);

            if ($cachedData) {
                // Return cached data immediately
                $results[] = $this->formatFeedData($source, $cachedData);
            } else {
                // Mark for fetching
                $uncachedSources->push($source);
            }
        }

        if ($uncachedSources->isEmpty()) {
            return $this->sortResultsByNewestPubDate($results);
        }

        // 2. Concurrent Fetching using Http Pool
        $responses = Http::pool(function ($pool) use ($uncachedSources) {
            $requests = [];
            foreach ($uncachedSources as $source) {
                $requests[] = $pool->as($source->id)->get($source->url);
            }
            return $requests;
        });

        // 3. Process Responses
        foreach ($uncachedSources as $source) {
            $response = $responses[$source->id] ?? null;

            if ($response && $response->ok()) {
                $feedContent = $response->body();
                $items = $this->parseFeed($feedContent, $source->url);

                if ($items !== null) {
                    // Cache the parsed items
                    Cache::put($this->getCacheKey($source), $items, now()->addMinutes(10));
                    $results[] = $this->formatFeedData($source, $items);
                }
            } else {
                Log::warning("Failed to fetch RSS source [{$source->id}]: {$source->url}");
            }
        }

        return $this->sortResultsByNewestPubDate($results);
    }

    /**
     * Sort results by the newest pubDate of each source's items.
     * Sources with the most recent articles appear first.
     *
     * @param  array  $results
     * @return array
     */
    private function sortResultsByNewestPubDate(array $results): array
    {
        usort($results, function ($a, $b) {
            // Get the newest pubDate from each source's items
            $newestA = $this->getNewestPubDate($a['items'] ?? []);
            $newestB = $this->getNewestPubDate($b['items'] ?? []);
            
            // Sort in descending order (newest first)
            return $newestB <=> $newestA;
        });

        return $results;
    }

    /**
     * Get the newest pubDate timestamp from an array of items.
     *
     * @param  array  $items
     * @return int
     */
    private function getNewestPubDate(array $items): int
    {
        if (empty($items)) {
            return 0; // No items, put at the end
        }

        $newest = 0;
        foreach ($items as $item) {
            $timestamp = strtotime($item['pubDate'] ?? '1970-01-01 00:00:00');
            if ($timestamp > $newest) {
                $newest = $timestamp;
            }
        }

        return $newest;
    }

    private function getCacheKey(RssSource $source): string
    {
        // Cache key depends on URL and User? Or just URL?
        // Original code used md5($source->url . $userId).
        // Since RssSource belongs to a user (based on previous code analysis), using source->id is safest/simplest unique key.
        return 'rss_feed_data_' . $source->id;
    }

    private function parseFeed(string $content, string $url): ?array
    {
        // Suppress SimplePie deprecated warnings if any
        $feed = new SimplePie();
        $feed->set_raw_data($content);
        // We don't need file cache for SimplePie since we cache the final array result manually
        $feed->enable_cache(false);
        $feed->init();
        $feed->handle_content_type();

        if ($feed->error()) {
            Log::error('RSS Parse Error', ['url' => $url, 'error' => $feed->error()]);
            return null;
        }

        $items = [];
        foreach ($feed->get_items() as $item) {
            $image = $this->extractImage($item);
            
            $items[] = [
                'title' => $item->get_title(),
                'link' => $item->get_link(),
                'description' => strip_tags($item->get_description()),
                'pubDate' => $item->get_date('Y-m-d H:i:s'),
                'image' => $image,
                'thumbnail' => $image, // Alias für Kompatibilität
            ];
        }

        return $items;
    }

    private function extractImage($item): ?string
    {
        // Versuche die Bild-URL zu extrahieren von verschiedenen Quellen
        
        // 1. Versuche Image-URL aus dem Item zu bekommen
        if (method_exists($item, 'get_image_url')) {
            $imageUrl = $item->get_image_url();
            if ($imageUrl && $this->isValidImageUrl($imageUrl)) {
                return $imageUrl;
            }
        }
        
        // 2. Versuche Media RSS Enclosures (Bilder)
        $enclosures = $item->get_enclosures();
        if ($enclosures) {
            foreach ($enclosures as $enclosure) {
                $type = $enclosure->get_type();
                if ($type && str_starts_with($type, 'image/')) {
                    return $enclosure->get_link();
                }
            }
        }
        
        // 3. Versuche Bild aus der Beschreibung zu extrahieren (erste img Tag)
        $description = $item->get_description();
        if ($description && preg_match('/<img[^>]+src="([^">]+)"/', $description, $matches)) {
            $imageUrl = $matches[1];
            if ($this->isValidImageUrl($imageUrl)) {
                return $imageUrl;
            }
        }
        
        return null;
    }

    private function isValidImageUrl(string $url): bool
    {
        // Whitelist von gültigen Bildendungen
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
        
        // Parse URL und extrahiere den Pfad
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        
        // Prüfe Dateiendung
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        // Wenn klare Bildendung, akzeptieren
        if (in_array($extension, $imageExtensions)) {
            return true;
        }
        
        // Wenn kein Query-String mit verdächtigen Parametern (z.B. cpx.php)
        if (str_ends_with($path, '.php') || str_ends_with($path, '.cgi') || str_ends_with($path, '.html')) {
            return false;
        }
        
        // Für URLs ohne klare Bildendung: HEAD-Request mit Timeout
        try {
            $response = Http::timeout(2)->head($url);
            $contentType = $response->header('Content-Type') ?? '';
            
            return str_starts_with($contentType, 'image/');
        } catch (\Exception $e) {
            Log::debug("Failed to validate image URL: {$url}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function formatFeedData(RssSource $source, array $items): array
    {
        // Sort items by pubDate in descending order (newest first)
        usort($items, function ($a, $b) {
            $dateA = strtotime($a['pubDate'] ?? '1970-01-01 00:00:00');
            $dateB = strtotime($b['pubDate'] ?? '1970-01-01 00:00:00');
            return $dateB <=> $dateA; // Descending order
        });

        return [
            'id' => $source->id,
            'name' => $source->name,
            'url' => $source->url,
            'items' => $items,
        ];
    }
}
