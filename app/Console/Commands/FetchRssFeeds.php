<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RssSource;
use App\Jobs\FetchFeedJob; // Falls du Jobs verwendest
use Illuminate\Support\Facades\Log;

class FetchRssFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:rss-feeds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ruft alle RSS-Feeds ab und speichert sie im Cache.';

    /**
     * Execute the console command.
     * 
     * @example
     *  - php artisan fetch:rss-feeds
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Starte Abruf aller aktiven RSS-Feeds...');

        // Variante 1: Speicherschonend mit Query-Builder chunk (empfohlen)
        $count = 0;
        $errors = 0;

        RssSource::whereHas('users', function ($query) {
                $query->where('rss_source_user.is_active', true);
            })
            ->chunk(100, function ($chunk) use (&$count, &$errors) {
                foreach ($chunk as $source) {
                    try {
                        // Hier kannst du entweder direkt verarbeiten oder (besser) einen Job dispatchen
                        FetchFeedJob::dispatch($source);

                        $count++;
                        $this->info("Queued: {$source->url}");
                    } catch (\Exception $e) {
                        $errors++;
                        Log::error("Fehler beim Queuen des Feeds: {$source->url}", [
                            'error' => $e->getMessage(),
                            'source_id' => $source->id ?? null,
                            'trace' => $e->getTraceAsString(),
                        ]);

                        $this->error("Fehler bei {$source->url}: " . $e->getMessage());
                    }
                }
            });

        $this->newLine();
        $this->info("Fertig! {$count} Feeds erfolgreich in die Queue gestellt.");
            
        if ($errors > 0) {
            $this->warn("Es traten {$errors} Fehler auf – siehe logs für Details.");
        }

        return self::SUCCESS;
    }
}

