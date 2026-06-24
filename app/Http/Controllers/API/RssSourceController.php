<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RssSource;
use App\Models\RssSourceUser;
use App\Models\RssSourceCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Validator;

class RssSourceController extends Controller
{
    /**
     * Display all RSS sources for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $rssSources = RssSource::query()
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with([
                'users' => function ($query) use ($userId) {
                    $query
                        ->where('user_id', $userId)
                        ->withPivot([
                            'rss_source_id',
                            'user_id',
                            'subscribed_at',
                            'is_active',
                            'name',
                            'created_at',
                            'updated_at',
                        ]);
                }
            ])
            ->latest('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Cache optimiert laden
        |--------------------------------------------------------------------------
        */

        $cacheKeys = $rssSources
            ->mapWithKeys(fn ($source) => [
                'rss_feed_data_' . $source->id => null
            ])
            ->keys()
            ->toArray();

        $cachedFeeds = Cache::many($cacheKeys);

        /*
        |--------------------------------------------------------------------------
        | Daten anreichern
        |--------------------------------------------------------------------------
        */

        $rssSources->transform(function ($source) use ($cachedFeeds) {

            $items = $cachedFeeds['rss_feed_data_' . $source->id] ?? null;

            if (is_array($items)) {
                $source->item_count = count($items);
                $source->last_activity_at = $items[0]['pubDate'] ?? null;
            } else {
                $source->item_count = 0;
                $source->last_activity_at = null;
            }

            return $source;
        });

        $response = [
            'rss_sources' => $rssSources
        ];

        if ($request->expectsJson()) {
            return response()->json($response);
        }

        return Inertia::render('rss-sources', $response);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validierung der eingehenden Daten
        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'url' => ['required', 'url'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Überprüfen, ob es sich um eine OPML-Datei handelt
        /*
        if ($this->isOpml($request->input('url'))) {
            return response()->json([
                'error' => 'Die angegebene URL verweist auf eine OPML-Datei, die nicht gespeichert werden kann.',
            ], 422);
        }
        */
        if (! $this->isFeedUrl($request->input('url'))) {
            return response()->json([
                'error' => 'Die URL scheint kein gültiger RSS/Atom Feed zu sein.',
            ], 422);
        }

        // Überprüfen, ob die URL bereits existiert
        $rssSource = RssSource::where('url', $request->input('url'))->first();

        // Falls die Quelle noch nicht existiert, erstelle sie
        if (!$rssSource) {
            $rssSource = new RssSource();
            $rssSource->url = $request->input('url');
            $rssSource->save();
        }

        $name = $request->input('name');
        if (empty($name)) {
            $name = self::getDomainName($request->input('url')); 
        }

        // Die 'name' und 'is_active' in der Pivot-Tabelle 'rss_source_user' speichern
        $rssSource->users()->syncWithoutDetaching([auth()->id() => [
            'subscribed_at' => now(),
            'name' => $name, // 'name' in der Pivot-Tabelle speichern
            'is_active' => true, // 'is_active' in der Pivot-Tabelle speichern
        ]]);

        return response()->json([
            'message' => 'RSS-Feed wurde erfolgreich hinzugefügt.',
            'rss_source' => $rssSource,
        ], 201);
    }

    /**
     * Anstatt von isOpml nutzen
     */
    private function isFeedUrl(string $url): bool
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'FeedValidator/1.0',
                    'Accept' => 'application/rss+xml, application/xml, text/xml, */*',
                ])
                ->get($url);

            if (!$response->successful()) {
                return false;
            }

            $body = trim($response->body());

            // 1. schnelle Heuristik (Performance wichtig)
            if (
                stripos($body, '<rss') === false &&
                stripos($body, '<feed') === false &&
                stripos($body, '<rdf:RDF') === false &&
                stripos($body, 'xml') === false
            ) {
                return false;
            }

            // 2. XML parsing (robuster Check)
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($body);

            if (!$xml) {
                return false;
            }

            $root = strtolower($xml->getName());

            // 3. erlaubte Feed-Typen
            return in_array($root, [
                'rss',     // RSS 2.0
                'feed',    // Atom
                'rdf'      // RSS 1.0
            ], true);

        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Überprüfen, ob die URL eine OPML-Datei ist.
        private function isOpml(string $url): bool
        {
            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'User-Agent' => 'RSSChecker/1.0',
                    ])
                    ->get($url);

                if (!$response->successful()) {
                    return false;
                }

                $body = $response->body();

                // schneller Check bevor XML parsing
                if (stripos($body, '<opml') === false) {
                    return false;
                }

                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($body);

                if (!$xml) {
                    return false;
                }

                return strtolower($xml->getName()) === 'opml';

            } catch (\Throwable $e) {
                return false;
            }
        }
    */

    /**
     * Remove the specified RSS source.
     */
    public function destroy($id)
    {
        $rssSource = RssSource::find($id);

        if (!$rssSource) {
            return response()->json([
                'error' => 'RSS-Quelle nicht gefunden.',
            ], 404);
        }

        // Sicherstellen, dass nur der Besitzer die Quelle löschen kann
        if (!$rssSource->users->contains(auth()->id())) {
            return response()->json([
                'error' => 'Nicht autorisiert, diese RSS-Quelle zu löschen.',
            ], 403);
        }

        // Lösche verbundene Favoriten
        try {
            $rssSource->favoriteRssItems()->delete();
        } catch (\Exception $e) {
            // Fehler ignorieren
        }

        // Cache für diese Quelle entfernen
        $cacheKey = 'rss_feed_data_' . $rssSource->id;
        Cache::forget($cacheKey);

        // Source wird auf nicht aktive gesetzt die Quellen bleibt bestehen
        $rssSource->users()->detach(auth()->id());

        return response()->json([
            'message' => 'RSS-Quelle wurde erfolgreich gelöscht.',
        ], 200);
    }

    /**
     * Aktualisiere das Abonnement eines Benutzers für eine RSS-Quelle.
     */
    public function unsubscribe(Request $request, $rssSourceId)
    {
        $rssSource = RssSource::find($rssSourceId);

        if (!$rssSource) {
            return response()->json([
                'error' => 'RSS-Quelle nicht gefunden.',
            ], 404);
        }

        if (!$rssSource->users->contains(auth()->id())) {
            return response()->json([
                'error' => 'Benutzer ist nicht für diese Quelle abonniert.',
            ], 400);
        }

        // Pivot-Eintrag komplett entfernen
        $rssSource->users()->detach(auth()->id());

        return response()->json([
            'message' => 'Abonnement wurde erfolgreich entfernt.',
        ], 200);
    }

     private static function getDomainName(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! $host) {
            $host = parse_url('https://' . ltrim($url, '/'), PHP_URL_HOST);
        }

        if (! $host) {
            return '';
        }

        $host = strtolower(trim($host));

        // www entfernen
        $host = preg_replace('/^www\./i', '', $host);

        // localhost / IP-Adressen direkt zurückgeben
        if (
            filter_var($host, FILTER_VALIDATE_IP) ||
            $host === 'localhost'
        ) {
            return $host;
        }

        $parts = explode('.', $host);

        if (count($parts) <= 2) {
            return $host;
        }

        $secondLevelDomains = [
            'ac',
            'co',
            'com',
            'edu',
            'gov',
            'net',
            'org',
            'mil',
        ];

        $tld = $parts[count($parts) - 1];
        $sld = $parts[count($parts) - 2];

        // z.B. bbc.co.uk, abc.com.au, foo.org.nz
        if (
            strlen($tld) === 2 &&
            in_array($sld, $secondLevelDomains, true) &&
            count($parts) >= 3
        ) {
            return implode('.', array_slice($parts, -3));
        }

        return implode('.', array_slice($parts, -2));
    }

   public function subscribe(Request $request, $rssSourceId)
    {
        $rssSource = RssSource::findOrFail($rssSourceId);

        $userId = $request->user()->id;

        $existing = $rssSource->users()
            ->where('user_id', $userId)
            ->first();

        $payload = [
            'subscribed_at' => now(),
            'is_active' => true,
        ];

        $existingName = $existing?->pivot?->name;

        if ($existingName) {
            $payload['name'] = $existingName;
        } else {
            $payload['name'] = $request->filled('name')
                ? $request->input('name')
                : self::getDomainName($rssSource->url);
        }

        $rssSource->users()->syncWithoutDetaching([
            $userId => $payload
        ]);

        return response()->json([
            'message' => 'Successfully subscribed',
        ]);
    }

    public function getCatalog(Request $request)
    {
        $userId = $request->user()->id;

        // Alle abonnierten RSS Source IDs des Users (über Model)
        $subscribedIds = RssSourceUser::where('user_id', $userId)
            ->pluck('rss_source_id')
            ->toArray();

        // Catalog Query
        $query = RssSourceCatalog::with('source');

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $catalog = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('rank')
            ->get()
            ->map(function ($item) use ($subscribedIds) {
                return [
                    'id' => $item->id,
                    'rss_source_id' => $item->rss_source_id,
                    'country' => $item->country,
                    'category' => $item->category,
                    'rank' => $item->rank,
                    'is_featured' => $item->is_featured,

                    'source' => [
                        'id' => $item->source->id,
                        'url' => $item->source->url,
                    ],

                    'is_subscribed' => in_array(
                        $item->rss_source_id,
                        $subscribedIds
                    ),
                ];
            });

        return response()->json([
            'data' => $catalog
        ]);
    }
}
