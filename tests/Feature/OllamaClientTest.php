<?php

namespace Tests\Feature;

use App\LlmDriver\ClaudeClient;
use App\LlmDriver\OllamaClient;
use App\LlmDriver\Requests\MessageInDto;
use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_embeddings(): void
    {
        $this->markTestSkipped('@TODO: Requires another server');

        $client = new ClaudeClient();

        $results = $client->embedData('test');

        $this->assertInstanceOf(EmbeddingsResponseDto::class, $results);

    }

    public function test_completion(): void
    {
        $client = new OllamaClient();

        $data = get_fixture('ollama_results.json');

        Http::fake([
            '127.0.0.1:11434/*' => Http::response($data, 200),
        ]);

        $results = $client->completion('test');

        $this->assertInstanceOf(CompletionResponse::class, $results);

    }

    public function test_chat(): void
    {
        $client = new OllamaClient();

        $data = get_fixture('ollama_chat_results.json');

        Http::fake([
            '127.0.0.1:11434/*' => Http::response($data, 200),
        ]);


        $results = $client->chat([
            MessageInDto::from([
                'content' => 'test',
                'role' => 'system',
            ]),
            MessageInDto::from([
                'content' => 'test',
                'role' => 'user',
            ]),
        ]);

        $this->assertInstanceOf(CompletionResponse::class, $results);

        Http::assertSent(function ($request) {
            $message1 = $request->data()['messages'];

            return count($message1) === 2;
        });

    }

    
}
