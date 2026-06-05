<?php

namespace App\Jobs;

use App\Models\RssSource;
use App\Services\RssService;
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
            $rssService = app(RssService::class);
            $feedData = $rssService->fetchFeeds(collect([$this->source]));
            $items = $feedData[0]['items'] ?? [];

            Cache::put('rss_feed_data_' . $this->source->id, $items, now()->addMinutes(10));
        } catch (\Exception $e) {
            Log::error('Fehler beim Abrufen des Feeds: ' . $this->source->url, ['error' => $e->getMessage()]);
        }
    }
}
