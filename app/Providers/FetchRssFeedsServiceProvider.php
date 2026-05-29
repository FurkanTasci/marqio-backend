<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\FetchRssFeeds;

class FetchRssFeedsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function boot(Schedule $schedule)
    {
        // Den FetchRssFeeds Command im Scheduler registrieren
        $schedule->command(FetchRssFeeds::class)->hourly(); // Hier kannst du das Intervall nach Bedarf anpassen
    }

    /**
     * Bootstrap services.
     */
    public function register(): void
    {
        //
    }
}
