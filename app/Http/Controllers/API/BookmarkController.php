<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Tag;
use Embed\Embed;
use Embed\Http\Crawler;
use Embed\Http\CurlClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BookmarkController extends Controller
{
    // Liste aller Bookmarks des authentifizierten Users
    public function index(Request $request)
    {
        $bookmarks = Auth::user()->bookmarks()->with('tags')->latest()->get();
  
        if ($request->expectsJson()) {    
           return response()->json($bookmarks);
        }   

        // dd($bookmarks->count());

        // Wenn der Request eine Inertia-Seite erwartet
        return Inertia::render('bookmarks', [
            'bookmarks' => $bookmarks
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'url' => 'required|url|unique:bookmarks,url',
            'url' => [
            'required',
                'url',
                Rule::unique('bookmarks')
                    ->where(fn ($query) =>
                        $query->where('user_id', Auth::id())
                ),
            ],
            'is_favorite' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        $url = $validated['url'];

        // Cookie pro Domain speichern
        $host = parse_url($url, PHP_URL_HOST);
        $cookiesPath = storage_path("app/cookies/{$host}.json");

        if (!file_exists(dirname($cookiesPath))) {
            mkdir(dirname($cookiesPath), 0777, true);
        }

        $client = new CurlClient();

        $client->setSettings([
            'follow_location' => true,
            'max_redirects' => 10,
            'timeout' => 15,
            'connect_timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124 Safari/537.36',
            'cookies_path' => $cookiesPath,
            'headers' => [
                'Referer' => 'https://www.google.com/',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ]);

        $title = null;
        $description = null;
        $image = null;

        $embed = new Embed(new Crawler($client));

        try {
            $info = $embed->get($url);

            $html = (string) $info->getResponse()->getBody();

            libxml_use_internal_errors(true);

            $dom = new \DOMDocument();
            $dom->loadHTML($html);

            $metaTags = [];

            foreach ($dom->getElementsByTagName('meta') as $meta) {

                $name = $meta->getAttribute('name');
                $property = $meta->getAttribute('property');
                $content = $meta->getAttribute('content');

                if ($name && $content) {
                    $metaTags[$name] = $content;
                }

                if ($property && $content) {
                    $metaTags[$property] = $content;
                }
            }

            $titleNodes = $dom->getElementsByTagName('title');

            $fallbackTitle = $titleNodes->length > 0
                ? trim($titleNodes->item(0)->textContent)
                : null;

            $title =
                $metaTags['og:title']
                ?? $metaTags['twitter:title']
                ?? $fallbackTitle;

            $description =
                $metaTags['og:description']
                ?? $metaTags['description']
                ?? $metaTags['twitter:description']
                ?? null;

            $image =
                $metaTags['og:image']
                ?? $metaTags['twitter:image']
                ?? null;

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Could not fetch metadata',
                'message' => $e->getMessage(),
            ], 500);
        }

        $host = preg_replace('/^www\./', '', $host);

        $bookmark = Auth::user()->bookmarks()->create([
            'title'       => $title ?? $host,
            'url'         => $url,
            'description' => $description,
            'image'       => $image,
            'is_favorite' => $validated['is_favorite'] ?? false,
        ]);

        if (!empty($validated['tags'])) {

            $tagIds = [];

            foreach ($validated['tags'] as $tagName) {
                $tag = Tag::firstOrCreate([
                    'name' => $tagName
                ]);

                $tagIds[] = $tag->id;
            }

            $bookmark->tags()->sync($tagIds);
        }

        return response()->json(
            $bookmark->load('tags'),
            201
        );
    }

    /**
     * Wenn über das WebView Website als Bookmark gesetzt werden könnten man direkt 
     * die Meta Tags extrahieren das der User sowieso denn CookieBanner zulässt.
     * 
     * Die App könnte einfach ein zusatz element wie:
     * "webview" = []
     * "webview = [
     *  'title' => '',
     *  ...
     * ]
     */
    public function webViewBookmark()
    {

    }

    /*
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bookmark $bookmark)
    {
        Gate::authorize('update', $bookmark);

        $validated = $request->validate([
            'is_favorite' => 'required|boolean',
        ]);

        $bookmark->update([
            'is_favorite' => $validated['is_favorite']
        ]);

        return response()->json($bookmark);
    }

    // Bookmark löschen
    public function destroy(Bookmark $bookmark)
    {
        Gate::authorize('delete', $bookmark);
        $bookmark->delete();
        return response()->json(['message' => 'Bookmark deleted successfully']);
    }

    public function search(Request $request)
    {
        $query = Bookmark::query()
            ->with('tags')
            ->select('id', 'url', 'title')
            ->where('user_id', auth()->id());

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q
                    ->where('title', 'like', "%$search%")
                    ->orWhere('url', 'like', "%$search%");
            })->orderByRaw("title LIKE ? DESC", ["%$search%"]); 
        } else {
            $query->latest();
        }

        return $query   
            ->latest()
            ->paginate(20);
    }
}

