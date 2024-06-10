<?php

namespace App\Domains\WebhookClient;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_sends_to_system(): void
    {
        $data = get_fixture('saved.json');

        $client = new \App\Domains\WebhookClient\Client();
        $results = $client->handle($data);

        $this->assertTrue($results);
    }
}
