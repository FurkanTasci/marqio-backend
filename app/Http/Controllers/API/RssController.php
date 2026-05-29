<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RssSource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RssController extends Controller
{
    public function __construct(
        protected \App\Services\RssService $rssService
    ) {
    }

    /**
     * Get all RSS feeds for the authenticated user.
     */
    public function getUserFeeds(Request $request)
    {
        $userId = $request->user()->id;

        // Holen der RSS-Quellen für den Benutzer (nur aktive Quellen)
        $rssSources = RssSource::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId)->where('rss_source_user.is_active', true); // Pivot-Tabelle berücksichtigen
        })->with(['users' => function ($query) use ($userId) {
            $query->where('user_id', $userId)->withPivot('name', 'is_active'); // Auch die Pivot-Daten mitladen
        }])->get();

        // Abrufen der Feeds über den Service (optimiert & parallel)
        $feedsData = $this->rssService->fetchFeeds($rssSources);

        // Wenn der Request eine JSON-Antwort erwartet (API-Anfrage)
        if ($request->expectsJson()) {    
            return \App\Http\Resources\FeedResource::collection($feedsData);
        }

        // Wenn der Request eine Inertia-Seite erwartet
        return Inertia::render('dashboard', [
            'feeds' => $feedsData
        ]);
    }

    /**
     * Get the feeds of a specific RSS source for the authenticated user.
     */
    public function getSourceFeeds(Request $request, $sourceId)
    {
        $userId = $request->user()->id;

        // Validierung: Überprüfung, dass die Source dem Benutzer gehört
        $rssSource = RssSource::whereHas('users', function ($query) use ($userId, $sourceId) {
        $query->where('user_id', $userId)
            ->where('rss_source_id', $sourceId)
            ->where('rss_source_user.is_active', true); // Pivot-Tabelle berücksichtigen
        })->with(['users' => function ($query) use ($userId, $sourceId) {
            $query->where('user_id', $userId)
                ->where('rss_source_id', $sourceId)
                ->withPivot('name', 'is_active'); // Auch die Pivot-Daten mitladen
        }])->firstOrFail();

        // Abrufen der Feeds für diese spezifische Source
        $feedsData = $this->rssService->fetchFeeds(collect([$rssSource]));

        // Rückgabe als standardisierte Resource Collection
        return \App\Http\Resources\FeedResource::collection($feedsData);
    }
}
