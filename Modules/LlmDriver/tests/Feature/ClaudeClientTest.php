<?php

namespace Tests\Feature;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Messages\RoleEnum;
use App\Models\Setting;
use Feature;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use LlmLaraHub\LlmDriver\ClaudeClient;
use LlmLaraHub\LlmDriver\Functions\FunctionDto;
use LlmLaraHub\LlmDriver\Functions\ParametersDto;
use LlmLaraHub\LlmDriver\Functions\PropertyDto;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use Tests\TestCase;

class ClaudeClientTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Setting::factory()->all_have_keys()->create();
    }

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
        $client = new ClaudeClient();

        $data = get_fixture('claude_completion.json');

        Http::fake([
            'api.anthropic.com/*' => Http::response($data, 200),
        ]);

        $results = $client->completion('test');

        $this->assertInstanceOf(CompletionResponse::class, $results);

    }

    public function test_completion_pool(): void
    {
        Setting::factory()->all_have_keys()->create();

        $client = new ClaudeClient();

        $data = get_fixture('claude_completion.json');

        Http::fake([
            'api.anthropic.com/*' => Http::response($data, 200),
        ]);

        Http::preventStrayRequests();

        $results = $client->setFormatJson()->completionPool([
            'test1',
            'test2',
            'test3',
        ]);

        $this->assertCount(3, $results);

    }

    public function test_chat(): void
    {
        $client = new ClaudeClient();

        $data = get_fixture('claude_completion.json');

        Http::fake([
            'api.anthropic.com/*' => Http::response($data, 200),
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
            $messageAssistant = $request->data()['messages'][0]['role'];
            $messageUser = $request->data()['messages'][1]['role'];

            return $messageAssistant === 'assistant' &&
                $messageUser === 'user';
        });

    }

    public function test_chat_with_multiple_assistant_messages(): void
    {
        $client = new ClaudeClient();

        $data = get_fixture('claude_completion.json');

        Http::fake([
            'api.anthropic.com/*' => Http::response($data, 200),
        ]);

        $results = $client->chat([
            MessageInDto::from([
                'content' => 'test',
                'role' => 'user',
            ]),
            MessageInDto::from([
                'content' => 'test 1',
                'role' => 'assistant',
            ]),
            MessageInDto::from([
                'content' => 'test 2',
                'role' => 'assistant',
            ]),
            MessageInDto::from([
                'content' => 'test 3',
                'role' => 'assistant',
            ]),
        ]);

        $this->assertInstanceOf(CompletionResponse::class, $results);

        Http::assertSent(function ($request) {
            $messageAssistant = $request->data()['messages'][1]['role'];
            $messageUser = $request->data()['messages'][2]['role'];

            return $messageAssistant === 'assistant' &&
                $messageUser === 'user';
        });

    }

    public function test_get_functions(): void
    {
        Feature::define('llm-driver.claude.functions', function () {
            return true;
        });
        $openaiClient = new \LlmLaraHub\LlmDriver\ClaudeClient();
        $response = $openaiClient->getFunctions();

        $this->assertNotEmpty($response);
        $this->assertIsArray($response);
        $first = $response[0];
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('input_schema', $first);

        $this->assertNotEmpty(data_get($first, 'input_schema.properties.prompt'));

        $this->assertNotEmpty($response);
    }

    public function test_functions_prompt(): void
    {
        Setting::factory()->all_have_keys()->create();

        Feature::define('llm-driver.claude.functions', function () {
            return true;
        });
        $data = get_fixture('cloud_client_tool_use_response.json');

        Http::fake([
            'api.anthropic.com/*' => Http::response($data, 200),
        ]);

        Http::preventStrayRequests();

        $openaiClient = new \LlmLaraHub\LlmDriver\ClaudeClient();
        $response = $openaiClient->functionPromptChat([
            MessageInDto::from([
                'content' => 'test',
                'role' => 'system',
            ]),
            MessageInDto::from([
                'content' => 'test',
                'role' => 'user',
            ]),
            MessageInDto::from([
                'content' => 'test should not be last',
                'role' => 'assistant',
            ]),
        ]);

        $this->assertIsArray($response);
        $this->assertCount(1, $response);

        Http::assertSent(function (Request $request) {
            $last = Arr::last($request['messages']);

            return $last['role'] === 'user' && count($request['messages']) === 4;
        });
    }

    public function test_remap_array(): void
    {

        $payload = get_fixture('payload_modified.json');

        $shouldBe = data_get($payload, 'tools');

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

        $results = (new ClaudeClient)->remapFunctions([$dto]);

        $this->assertEquals(
            $shouldBe,
            $results
        );
    }

    public function test_tool_response(): void
    {

        $data = get_fixture('response_tools_with_modified.json');

        $data = [
            'stop_reason' => 'tool_use',
            'stop_sequence' => null,
            'usage' => [
                'input_tokens' => 808,
                'output_tokens' => 254,
            ],
            'content' => $data['content'],
        ];

        $client = new ClaudeClient();

        Http::fake([
            'api.anthropic.com/*' => Http::response($data, 200),
        ]);

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

        $results = (new ClaudeClient)->setForceTool($dto)->completion('test');

        $content = $results->content;

        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(3, $decoded);
    }

    public function test_remap_messages_with_tools_as_history()
    {
        $messages = [];
        $messages[] = MessageInDto::from([
            'content' => 'test1',
            'role' => 'user',
        ]);
        $messages[] = MessageInDto::from([
            'content' => 'test2',
            'role' => 'assistant',
        ]);
        $messages[] = MessageInDto::from([
            'content' => 'test3',
            'role' => RoleEnum::Tool->value,
            'tool' => 'test',
            'tool_id' => 'test_id',
            'meta_data' => MetaDataDto::from([]),
        ]);

        $results = (new ClaudeClient)->remapMessages($messages);

        $this->assertCount(5, $results);

        $this->assertEquals('user', $results[0]['role']);
        $this->assertEquals('assistant', $results[1]['role']);
        $this->assertEquals('<thinking>test3</thinking>', $results[3]['content'][0]['text']);

        $this->assertEquals('user', $results[2]['role']);
        $this->assertEquals('tool_use', $results[3]['content'][1]['type']);
        $this->assertEquals('test', $results[3]['content'][1]['name']);
        $this->assertEquals('test_id', $results[3]['content'][1]['id']);
    }
}
