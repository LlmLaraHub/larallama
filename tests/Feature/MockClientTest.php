<?php

namespace Tests\Feature;

use App\LlmDriver\Responses\EmbeddingsResponseDto;
use App\LlmDriver\MockClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use OpenAI\Resources\Embeddings;
use Tests\TestCase;

class MockClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_embeddings(): void
    {

        $client = new MockClient();

        $results = $client->embedData('test');

        $this->assertInstanceOf(EmbeddingsResponseDto::class, $results);
        
    }
}
