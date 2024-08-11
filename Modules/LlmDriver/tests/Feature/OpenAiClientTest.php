<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use LlmLaraHub\LlmDriver\Functions\FunctionDto;
use LlmLaraHub\LlmDriver\Functions\ParametersDto;
use LlmLaraHub\LlmDriver\Functions\PropertyDto;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse as ChatCreateResponse;
use OpenAI\Responses\Embeddings\CreateResponse;
use Tests\TestCase;

class OpenAiClientTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Setting::factory()->all_have_keys()->create();
    }

    public function test_get_functions(): void
    {
        $this->markTestSkipped('@TODO waiting on this');
        $openaiClient = new \LlmLaraHub\LlmDriver\OpenAiClient();
        $response = $openaiClient->getFunctions();
        $this->assertNotEmpty($response);
        $this->assertIsArray($response);
        $first = $response[0];
        $this->assertArrayHasKey('type', $first);
        $this->assertArrayHasKey('function', $first);
        $expected = get_fixture('openai_client_get_functions.json');

        $this->assertNotEmpty($response);
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

        $openaiClient = new \LlmLaraHub\LlmDriver\OpenAiClient();
        $response = $openaiClient->embedData('test');
        $this->assertInstanceOf(EmbeddingsResponseDto::class, $response);
    }

    public function test_completion(): void
    {

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    'messages' => [
                        'content' => 'Foo bar',
                    ],
                ],
            ]),
        ]);

        $openaiClient = new \LlmLaraHub\LlmDriver\OpenAiClient();
        $response = $openaiClient->completion('"Foo bar');
        $this->assertInstanceOf(CompletionResponse::class, $response);
    }

    public function test_remap_array(): void
    {
        $dto = FunctionDto::from([
            'name' => 'reporting_json',
            'description' => 'JSON Summary of the report',
            'parameters' => ParametersDto::from([
                'type' => 'array',
                'properties' => [
                    PropertyDto::from([
                        'name' => 'title',
                        'description' => 'The title of the section',
                        'type' => 'string',
                        'required' => true,
                    ]),
                    PropertyDto::from([
                        'name' => 'content',
                        'description' => 'The content of the section',
                        'type' => 'string',
                        'required' => true,
                    ]),
                ],
            ]),
        ]);

        $openaiClient = new \LlmLaraHub\LlmDriver\OpenAiClient();
        $response = $openaiClient->remapFunctions([$dto]);
        $shouldBe = get_fixture('openai_payload_modified.json');
        $shouldBe = data_get($shouldBe, 'tools', []);
        $this->assertEquals($shouldBe, $response);

    }

    public function test_chat(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    'messages' => [
                        'content' => 'Foo bar',
                    ],
                ],
            ]),
        ]);

        Http::preventStrayRequests();

        $openaiClient = new \LlmLaraHub\LlmDriver\OpenAiClient();
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

        $openaiClient = new \LlmLaraHub\LlmDriver\OpenAiClient();
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
