<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\FavoriteRssItem;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FavoriteRssItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $favoriteRssItems = $user->favoriteRssItems; 

        return response()->json($favoriteRssItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            // 'rss_source_id' => 'required|exists:rss_sources,id',
            'title' => 'required|string|max:255',
            'item_url' => 'required|url|unique:favorite_rss_items,item_url',
            'description' => 'nullable|string',
            'published_at' => 'required|date',
        ]);

        // Erstelle ein neues favorisiertes RSS-Item
        $favoriteRssItem = FavoriteRssItem::create([
            // 'rss_source_id' => $request->rss_source_id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'item_url' => $request->item_url,
            'published_at' => $request->published_at,
        ]);

        return response()->json($favoriteRssItem, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
         // Hole das favorisierte RSS-Item für den authentifizierten Benutzer
        $favoriteRssItem = FavoriteRssItem::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$favoriteRssItem) {
            return response()->json(['message' => 'Favorite RSS item not found'], 404);
        }

        return response()->json($favoriteRssItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validierung der Eingabedaten
        $request->validate([
            // 'rss_source_id' => 'required|exists:rss_sources,id',
            'title' => 'required|string|max:255',
            'item_url' => 'required|url|unique:favorite_rss_items,item_url,' . $id,
            'description' => 'nullable|string',
            'published_at' => 'required|date',
        ]);

        // Hole das favorisierte RSS-Item
        $favoriteRssItem = FavoriteRssItem::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$favoriteRssItem) {
            return response()->json(['message' => 'Favorite RSS item not found'], 404);
        }

        // Aktualisiere das favorisierte RSS-Item
        $favoriteRssItem->update([
            'rss_source_id' => $request->rss_source_id,
            'title' => $request->title,
            'description' => $request->description,
            'item_url' => $request->item_url,
            'published_at' => $request->published_at,
        ]);

        return response()->json($favoriteRssItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Hole das favorisierte RSS-Item
        $favoriteRssItem = FavoriteRssItem::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$favoriteRssItem) {
            return response()->json(['message' => 'Favorite RSS item not found'], 404);
        }

        // Lösche das favorisierte RSS-Item
        $favoriteRssItem->delete();

        return response()->json(['message' => 'Favorite RSS item deleted successfully']);
    }
}
