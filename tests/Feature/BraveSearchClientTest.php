<?php

namespace Tests\Feature;

use App\Domains\Sources\WebSearch\Drivers\BraveSearchClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BraveSearchClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_search(): void
    {
        $data = get_fixture('brave_response.json');

        Http::fake([
            'api.search.brave.com/*' => Http::response($data, 200),
        ]);

        Http::preventStrayRequests();

        $results = (new BraveSearchClient())->search('test');

        $this->assertNotEmpty($results->getVideos());
        $this->assertNotEmpty($results->getWeb());

    }
}
