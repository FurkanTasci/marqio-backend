<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Expecting the array structure from RssService::formatFeedData
        return [
            'source_id' => $this['id'],
            'source_name' => $this['name'],
            'source_url' => $this['url'],
            'items' => $this['items'],
        ];
    }
}
