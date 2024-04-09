<?php

namespace Tests\Feature;

use App\LlmDriver\Requests\MessageInDto;
use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use Laravel\Pennant\Feature;
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
        $this->assertArrayHasKey('type', $first);
        $this->assertArrayHasKey('function', $first);
        $expected = get_fixture('openai_client_get_functions.json');

        $this->assertEquals($expected, $response);
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

    public function test_functions_prompt(): void
    {



        $data = get_fixture('openai_response_with_functions_summarize_collection.json');

        $response = [
            'choices' => data_get($data, 'choices', []),
        ];

        // OpenAI::fake([
        //     ChatCreateResponse::fake($response)
        // ]);
        OpenAI::fake([
            ChatCreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => '',
                            'tool_calls' => [
                                [
                                    'id' => 'call_u3GOeiE4LaSJvOqV2uOqeXK2',
                                    'type' => 'function',
                                    'function' => [
                                        'name' => 'summarize_collection',
                                        'arguments' => "{\"prompt\":\"TLDR this collection for me'\"}",
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $openaiClient = new \App\LlmDriver\OpenAiClient();
        $response = $openaiClient->functionPromptChat([
            MessageInDto::from([
                'content' => 'test',
                'role' => 'system',
            ]),
            MessageInDto::from([
                'content' => 'test',
                'role' => 'user',
            ]),
        ]);

        $this->assertIsArray($response);
        $this->assertCount(1, $response);
    }
}
