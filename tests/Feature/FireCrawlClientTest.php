<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FireCrawlClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_parse(): void
    {
        Setting::factory()->create([
            'secrets' => [
                'fire_crawl' => [
                    'api_url' => 'https://api.firecrawl.dev',
                    'api_key' => 'foo',
                ],
            ],
        ]);

        $data = get_fixture('test_firecrawl_parse.json');

        Http::fake([
            'api.firecrawl.dev/*' => Http::response($data, 200),
        ]);

        Http::preventStrayRequests();

        $client = new \App\Domains\WebParser\FireCrawlClient();
        $results = $client->scrape('https://www.mendable.ai/');
        $this->assertEquals('Mendable | AI for CX and Sales', $results->title);
        $this->assertEquals('AI for CX and Sales', $results->description);
        $this->assertEquals('# Markdown Content', $results->content);
        $this->assertEquals('https://www.mendable.ai/', $results->url);

    }
}
