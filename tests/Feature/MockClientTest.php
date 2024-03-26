<?php

namespace Tests\Feature;

use App\LlmDriver\Responses\EmbeddingsResponseDto;
use App\LlmDriver\MockClient;
use App\LlmDriver\Responses\CompletionResponse;
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

    public function test_completion(): void
    {

        $client = new MockClient();

        $results = $client->completion('test');

        $this->assertInstanceOf(CompletionResponse::class, $results);
        
    }
}
