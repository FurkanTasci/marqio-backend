<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RssSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach (config('rss_sources') as $source) {
            DB::table('rss_sources')->updateOrInsert(
                ['url' => $source['url']],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $rssSource = DB::table('rss_sources')
                ->where('url', $source['url'])
                ->first();

            DB::table('rss_source_catalog')->updateOrInsert(
                ['rss_source_id' => $rssSource->id],
                [
                    'country' => $source['country'],
                    'category' => $source['category'],
                    'rank' => $source['rank'],
                    'is_featured' => $source['is_featured'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
