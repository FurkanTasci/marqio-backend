<?php

namespace App\Jobs;

use SimplePie\SimplePie;
use App\Models\RssSource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class FetchFeedJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(RssSource $source)
    {
        $this->source = $source;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Sicherstellen, dass die Quelle aktiv ist
            if ($this->source->is_active) {
                $feedItems = $this->fetchFeedItems($this->source);
                Cache::put('rss_feed_' . md5($this->source->url), $feedItems, now()->addMinutes(10));
            } else {
                Log::info('Die RSS-Quelle ist nicht aktiv, Feed wird nicht abgerufen.', ['source' => $this->source->url]);
            }
        } catch (\Exception $e) {
            Log::error('Fehler beim Abrufen des Feeds: ' . $this->source->url, ['error' => $e->getMessage()]);
        }
    }

    private function fetchFeedItems(RssSource $source)
    {
        $feed = new SimplePie();
        $feed->set_feed_url($source->url);
        $feed->init();
        $feed->handle_content_type();

        return $feed->get_items();
    }
}
