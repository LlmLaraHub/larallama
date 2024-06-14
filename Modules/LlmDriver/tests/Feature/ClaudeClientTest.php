<?php

namespace Tests\Feature;

use App\Models\Setting;
use Feature;
use Illuminate\Support\Facades\Http;
use LlmLaraHub\LlmDriver\ClaudeClient;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use Tests\TestCase;

class ClaudeClientTest extends TestCase
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

        $results = $client->completionPool([
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
        $expected = get_fixture('claude_client_get_functions.json');

        $this->assertEquals($expected, $response);
    }

    public function test_functions_prompt(): void
    {
        Feature::define('llm-driver.claude.functions', function () {
            return true;
        });
        $data = get_fixture('cloud_client_tool_use_response.json');

        Http::fake([
            'api.anthropic.com/*' => Http::response($data, 200),
        ]);

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
        ]);

        $this->assertIsArray($response);
        $this->assertCount(1, $response);
    }
}
