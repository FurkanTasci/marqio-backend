<?php

namespace Tests\Feature;

use App\Models\RssSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RssFeedOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_fetch_feeds_optimized()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create 2 RSS sources
        $source1 = RssSource::create([
            'url' => 'https://example.com/feed1.xml',
        ]);
        $source1->users()->attach($user->id, ['is_active' => true, 'name' => 'Feed 1']);

        $source2 = RssSource::create([
            'url' => 'https://example.com/feed2.xml',
        ]);
        $source2->users()->attach($user->id, ['is_active' => true, 'name' => 'Feed 2']);

        // Mock HTTP responses
        Http::fake([
            'https://example.com/feed1.xml' => Http::response($this->getMockRssContent('Feed 1', 'Item 1'), 200),
            'https://example.com/feed2.xml' => Http::response($this->getMockRssContent('Feed 2', 'Item 2'), 200),
        ]);

        // 2. Act
        $response = $this->getJson('/api/rss-feed');

        // 3. Assert
        // Logic might need adjustment if the route is different, but assuming based on RssController::getUserFeeds
        // We need to check if we can actually hit the controller.
        // Based on "RssController.php" content and method "getUserFeeds", I'll assume the route maps there.
        // If route is unknown, I'll need to check routes/api.php first.
        // Let's assume standard resource or custom route.

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'source_id',
                    'source_name',
                    'source_url',
                    'items' => [
                        '*' => ['title', 'link', 'description', 'pubDate'],
                    ],
                ],
            ],
        ]);

        $response->assertJsonFragment(['source_name' => $source1->name]);
        $response->assertJsonFragment(['source_name' => $source2->name]);
    }

    public function test_feed_pub_date_is_returned_with_timezone()
    {
        Cache::flush();

        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $source = RssSource::create([
            'url' => 'https://example.com/feed.xml',
        ]);
        $source->users()->attach($user->id, ['is_active' => true, 'name' => 'Feed']);

        Http::fake([
            'https://example.com/feed.xml' => Http::response($this->getMockRssContent('Feed', 'Item'), 200),
        ]);

        $response = $this->getJson('/api/rss-feed');

        $response->assertOk();
        $response->assertJsonPath('data.0.items.0.pubDate', '2021-09-06T16:20:00+00:00');
    }

    private function getMockRssContent($title, $itemTitle)
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
  <title>$title</title>
  <link>https://example.com</link>
  <description>Mock Feed</description>
  <item>
    <title>$itemTitle</title>
    <link>https://example.com/item</link>
    <description>Test Description</description>
    <pubDate>Mon, 06 Sep 2021 16:20:00 +0000</pubDate>
  </item>
</channel>
</rss>
XML;
    }
}
