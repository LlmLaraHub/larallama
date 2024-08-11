<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use LlmLaraHub\LlmDriver\GroqClient;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class GroqClientTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Setting::factory()->all_have_keys()->create();
    }

    public function test_completion(): void
    {
        $client = new GroqClient();

        $data = get_fixture('groq_completion.json');

        Http::fake([
            'api.groq.com/*' => Http::response($data, 200),
        ]);

        $results = $client->completion('test');

        $this->assertInstanceOf(CompletionResponse::class, $results);

    }

    public function test_remap_messages(): void
    {
        $client = new GroqClient();
        $messages = [
            MessageInDto::from([
                'content' => 'test',
                'role' => 'user',
            ]),
        ];
        $remapped = $client->remapMessages($messages);

        $first = $remapped[0];

        $this->assertEquals('test', $first['content']);
        $this->assertEquals('user', $first['role']);
        $this->assertCount(2, $first);
    }

    public function test_completion_pool(): void
    {
        Setting::factory()->all_have_keys()->create();

        $client = new GroqClient();

        $data = get_fixture('groq_completion.json');

        Http::fake([
            'api.groq.com/*' => Http::response($data, 200),
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

        $client = new GroqClient();

        $data = get_fixture('groq_completion.json');

        Http::fake([
            'api.groq.com/*' => Http::response($data, 200),
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
    }

    public function test_functions_prompt(): void
    {
        $data = get_fixture('groq_functions_response.json');

        Http::fake([
            'api.groq.com/*' => Http::response($data, 200),
        ]);

        $openaiClient = new \LlmLaraHub\LlmDriver\GroqClient();
        $response = $openaiClient->functionPromptChat([
            MessageInDto::from([
                'content' => 'test',
                'role' => 'user',
            ]),
        ]);

        $this->assertIsArray($response);

        $this->assertCount(1, $response);
    }
}
