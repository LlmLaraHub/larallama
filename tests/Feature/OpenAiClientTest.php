<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Completions\CreateResponse as CompletionsCreateResponse;
use OpenAI\Responses\Embeddings\CreateResponse;

class OpenAiClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_openai_client(): void
    {
        OpenAI::fake([
            CreateResponse::fake([
                'embeddings' => [
                    [
                        'embedding' => 'awesome!',
                    ],
                ],
            ]),
        ]);

        $openaiClient = new \App\LlmDriver\OpenAiClient();
        $response = $openaiClient->embedData('test');
        $this->assertInstanceOf(EmbeddingsResponseDto::class, $response);
    }

    public function test_completion(): void
    {
        OpenAI::fake([
            CompletionsCreateResponse::fake([
                'choices' => [
                    [
                        'choice' => 'awesome!',
                    ],
                ],
            ]),
        ]);

        $openaiClient = new \App\LlmDriver\OpenAiClient();
        $response = $openaiClient->completion('test');
        $this->assertInstanceOf(CompletionResponse::class, $response);
    }
}
