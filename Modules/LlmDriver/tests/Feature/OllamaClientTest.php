<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\OllamaClient;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use Tests\TestCase;

class OllamaClientTest extends TestCase
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

        $client = new OllamaClient();

        $data = get_fixture('ollama_embedings.json');

        Http::fake([
            'localhost:11434/*' => Http::response($data, 200),
        ]);

        $results = $client->embedData('test');

        $this->assertInstanceOf(EmbeddingsResponseDto::class, $results);

    }

    public function test_completion(): void
    {
        $client = new OllamaClient();

        $data = get_fixture('ollama_results.json');

        Http::fake([
            'localhost:11434/*' => Http::response($data, 200),
        ]);

        Http::preventStrayRequests();

        $results = $client->completion('test');

        $this->assertInstanceOf(CompletionResponse::class, $results);

    }

    public function test_not_async()
    {
        $client = new OllamaClient();
        $this->assertFalse($client->isAsync());
    }

    public function test_chat(): void
    {
        $client = new OllamaClient();

        $data = get_fixture('ollama_chat_results.json');

        Http::fake([
            'localhost:11434/*' => Http::response($data, 200),
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

    public function test_functions()
    {
        $client = new OllamaClient();
        $functions = $client->getFunctions();

        $function = Arr::first($functions);

        $this->assertArrayHasKey('type', $function);
        $this->assertArrayHasKey('function', $function);
        $function = data_get($function, 'function');
        $this->assertArrayHasKey('name', $function);
        $this->assertArrayHasKey('description', $function);
        $this->assertArrayHasKey('parameters', $function);

        $parameters = data_get($function, 'parameters');
        $this->assertArrayHasKey('type', $parameters);
        $this->assertArrayHasKey('properties', $parameters);
        $this->assertArrayHasKey('required', $parameters);

    }

    public function test_remap_functions()
    {
        $functions = LlmDriverFacade::getFunctions();
        $results = (new OllamaClient)->remapFunctions($functions);
        put_fixture('ollama_functions.json', $results);
    }

    public function test_functions_prompt(): void
    {
        if (! Feature::active('ollama-functions')) {
            $this->markTestSkipped('Feature ollama-functions is not active');
        }
        $data = get_fixture('ollamas_function_response.json');

        Http::fake([
            'localhost:11434/*' => Http::response($data, 200),
        ]);

        $openaiClient = new \LlmLaraHub\LlmDriver\OllamaClient();
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
