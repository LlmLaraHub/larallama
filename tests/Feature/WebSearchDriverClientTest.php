<?php

namespace Tests\Feature;

use App\Domains\Sources\WebSearch\Drivers\MockSearchClient;
use App\Domains\Sources\WebSearch\WebSearchFacade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WebSearchDriverClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_facade(): void
    {
        $results = WebSearchFacade::driver('mock');

        $this->assertInstanceOf(MockSearchClient::class, $results);
    }
}
