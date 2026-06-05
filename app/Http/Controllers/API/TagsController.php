<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    public function update(Request $request, Bookmark $bookmark)
    {
        /**
         * bookmark_tag (bookmark_id, tag_id)
         * tags (id, name)
         */
        $validated = $request->validate([
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255']
        ]);

        $tagIds = [];

        if (!empty($validated['tags'])) {
            foreach ($validated['tags'] as $tagName) {
                $tag = Tag::firstOrCreate([
                    'name' => trim($tagName)
                ]);

                $tagIds[] = $tag->id;
            }
        }

        // Wenn tags leer oder null → sync([]) entfernt alle
        $bookmark->tags()->sync($tagIds);

        return response()->json([
            'message' => 'Tags aktualisiert',
            'tags' => $bookmark->tags
        ]);
    }
}
