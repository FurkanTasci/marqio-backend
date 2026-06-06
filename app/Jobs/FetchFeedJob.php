<?php

namespace App\Jobs;

use App\Models\RssSource;
use App\Services\RssService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

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
            $rssService->fetchFeeds(collect([$this->source]), forceRefresh: true);
        } catch (\Exception $e) {
            Log::error('Fehler beim Abrufen des Feeds: '.$this->source->url, ['error' => $e->getMessage()]);
        }
    }
}
