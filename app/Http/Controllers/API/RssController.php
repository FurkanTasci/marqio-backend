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
     * Liste aller RSS Sources (optional gefiltert)
     */
    public function index(Request $request)
    {
        $query = RssSource::query();

        if ($request->filled('country_code')) {
            $query->where('country_code', $request->input('country_code'));
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', filter_var($request->input('is_featured'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        return response()->json($query->orderByDesc('subscriber_count')->get());
    }

    /**
     * Einzelne RSS Source anzeigen
     */
    public function show($id)
    {
        $rss = RssSource::findOrFail($id);
        return response()->json($rss);
    }

    /**
     * Neue RSS Source erstellen
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url|unique:rss_sources,url',
            'title' => 'nullable|string|max:255',
            'site_url' => 'nullable|url|max:255',
            'country_code' => 'nullable|string|size:2',
            'language' => 'nullable|string|max:5',
            'category' => 'nullable|string|max:50',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
            'subscriber_count' => 'integer|min:0',
        ]);

        $rss = RssSource::create($validated);

        return response()->json($rss, 201);
    }

    /**
     * RSS Source aktualisieren
     */
    public function update(Request $request, $id)
    {
        $rss = RssSource::findOrFail($id);

        $validated = $request->validate([
            'url' => 'nullable|url|unique:rss_sources,url,' . $rss->id,
            'title' => 'nullable|string|max:255',
            'site_url' => 'nullable|url|max:255',
            'country_code' => 'nullable|string|size:2',
            'language' => 'nullable|string|max:5',
            'category' => 'nullable|string|max:50',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
            'subscriber_count' => 'integer|min:0',
        ]);

        $rss->update($validated);

        return response()->json($rss);
    }

    /**
     * RSS Source löschen
     */
    public function destroy($id)
    {
        $rss = RssSource::findOrFail($id);
        $rss->delete();

        return response()->json(['message' => 'RSS Source deleted']);
    }


    /**
     * Top RSS Sources nach Land
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topByCountry(Request $request)
    {
        $request->validate([
            'country_code' => 'required|string|size:2',
            'limit' => 'nullable|integer|min:1|max:100',
            'featured_only' => 'nullable|boolean',
        ]);

        $countryCode = strtoupper($request->input('country_code'));
        $limit = $request->input('limit', 10); // default 10
        $featuredOnly = $request->boolean('featured_only', false);

        $query = 
            RssSource::where('country_code', $countryCode)
                ->where('is_public', true);

        if ($featuredOnly) {
            $query->where('is_featured', true);
        }

        $rssSources = 
            $query->orderByDesc('subscriber_count')
                ->take($limit)
                ->get(['id', 'title', 'url', 'site_url', 'category', 'subscriber_count', 'is_featured']);

        return response()->json([
            'country' => $countryCode,
            'limit' => $limit,
            'featured_only' => $featuredOnly,
            'data' => $rssSources,
        ]);
    }

    //=> bis hier neue Methoden

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
