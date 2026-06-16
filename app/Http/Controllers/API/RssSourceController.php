<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RssSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Validator;

class RssSourceController extends Controller
{
    /**
     * Display all RSS sources for the authenticated user.
     */
    public function index(Request $request)
    {
        $rssSources = RssSource::whereHas('users', function ($query) {
            $query->where('user_id', auth()->id());
        })->with(['users' => function ($query) {
            $query->withPivot('name', 'is_active'); // Pivot-Daten 'name' und 'is_active' laden
        }])->get();

        $rssSources->map(function ($source) {
            $cacheKey = 'rss_feed_data_' . $source->id;
            $items = Cache::get($cacheKey);

            if ($items && is_array($items)) {
                $source->item_count = count($items);
                $source->last_activity_at = $items[0]['pubDate'] ?? null;
            } else {
                $source->item_count = 0;
                $source->last_activity_at = null;
            }

            return $source;
        });

        /*
        return response()->json([
            'rss_sources' => $rssSources,
        ], 200);
        */

        if ($request->expectsJson()) {    
            return response()->json([
                'rss_sources' => $rssSources
            ]);
        }   

        // print_r($rssSources);

        // Wenn der Request eine Inertia-Seite erwartet
        return Inertia::render('rss-sources', [
            'rss_sources' => $rssSources
        ]);
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
        if ($this->isOpml($request->input('url'))) {
            return response()->json([
                'error' => 'Die angegebene URL verweist auf eine OPML-Datei, die nicht gespeichert werden kann.',
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
            $urlHost = parse_url($request->input('url'), PHP_URL_HOST);

            $domainParts = explode('.', $urlHost);
            
            if (count($domainParts) > 2) {
                $name = ucfirst($domainParts[count($domainParts) - 2]);
            } else {
                $name = ucfirst($domainParts[0]);
            } 
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
     * Überprüfen, ob die URL ein gültiger RSS-Feed ist.
     */
    private function isValidRssFeed($url)
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true); // Nur den Header abrufen
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout für die Anfrage
            curl_exec($ch);

            // Content-Type überprüfen
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            return (strpos($contentType, 'rss+xml') !== false) || (strpos($contentType, 'xml') !== false);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Überprüfen, ob die URL eine OPML-Datei ist.
     */
    private function isOpml($url)
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true); // Nur den Header abrufen
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout für die Anfrage
            curl_exec($ch);

            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if (strpos($contentType, 'xml') === false) {
                return false;
            }

            // Versuche den Inhalt der URL zu lesen und zu überprüfen
            $xmlContent = file_get_contents($url);
            $xml = simplexml_load_string($xmlContent);

            return ($xml && $xml->getName() === 'opml');
        } catch (\Exception $e) {
            return false;
        }
    }

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
    public function updateSubscription(Request $request, $rssSourceId)
    {
        $rssSource = RssSource::find($rssSourceId);

        if (!$rssSource) {
            return response()->json([
                'error' => 'RSS-Quelle nicht gefunden.',
            ], 404);
        }

        // Überprüfen, ob der Benutzer bereits abonniert hat
        if (!$rssSource->users->contains(auth()->id())) {
            return response()->json([
                'error' => 'Benutzer ist nicht für diese Quelle abonniert.',
            ], 400);
        }

        // Abonnement aktualisieren (z. B. das `subscribed_at`-Datum setzen)
        $rssSource->users()->updateExistingPivot(auth()->id(), [
            'subscribed_at' => now(),
            'is_active' => true, // Optional, falls du die Aktivität des Abonnements ändern willst
            'name' => $request->input('name'), // Optional, falls der Name des Abonnements geändert wird
        ]);

        return response()->json([
            'message' => 'Abonnement für diese RSS-Quelle wurde aktualisiert.',
        ], 200);
    }
}
