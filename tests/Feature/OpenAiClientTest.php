<?php

namespace Tests\Feature;

use App\LlmDriver\Requests\MessageInDto;
use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse as ChatCreateResponse;
use OpenAI\Responses\Embeddings\CreateResponse;
use Tests\TestCase;

class OpenAiClientTest extends TestCase
{

    public function test_get_functions(): void
    {
        $openaiClient = new \App\LlmDriver\OpenAiClient();
        $response = $openaiClient->getFunctions();
        $this->assertNotEmpty($response);
        $this->assertIsArray($response);
        $first = $response[0];
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('description', $first);
        $this->assertArrayHasKey('parameters', $first);


    }

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
            ChatCreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'awesome!',
                        ],
                    ],
                ],
            ]),
        ]);

        $openaiClient = new \App\LlmDriver\OpenAiClient();
        $response = $openaiClient->completion('test');
        $this->assertInstanceOf(CompletionResponse::class, $response);
    }

    public function test_chat(): void
    {
        OpenAI::fake([
            ChatCreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'awesome!',
                        ],
                    ],
                ],
            ]),
        ]);

        $openaiClient = new \App\LlmDriver\OpenAiClient();
        $response = $openaiClient->chat([
            MessageInDto::from([
                'content' => 'test',
                'role' => 'system',
            ]),
            MessageInDto::from([
                'content' => 'test',
                'role' => 'user',
            ]),
        ]);
        $this->assertInstanceOf(CompletionResponse::class, $response);
    }
}
