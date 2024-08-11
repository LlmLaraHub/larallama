<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\Functions\FunctionDto;
use LlmLaraHub\LlmDriver\Functions\ParametersDto;
use LlmLaraHub\LlmDriver\Functions\PropertyDto;
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

        $openaiClient = new \LlmLaraHub\LlmDriver\OllamaClient();
        $response = $openaiClient->remapFunctions([$dto]);
        $shouldBe = get_fixture('ollama_functions_remapped.json');
        $this->assertEquals(
            json_encode($shouldBe), json_encode($response));
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
