<?php

namespace LlmLaraHub\LlmDriver;

use App\Models\Setting;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use LlmLaraHub\LlmDriver\Responses\OllamaChatCompletionResponse;

class OllamaClient extends BaseClient
{
    protected string $driver = 'ollama';

    public function embedData(string $prompt): EmbeddingsResponseDto
    {
        Log::info('LlmDriver::Ollama::embedData');

        $response = $this->getClient()->post('/embeddings', [
            'model' => $this->getConfig('ollama')['models']['embedding_model'],
            'prompt' => $prompt,
        ]);

        $results = $response->json();

        return EmbeddingsResponseDto::from([
            'embedding' => data_get($results, 'embedding'),
            'token_count' => 1000,
        ]);
    }

    public function addJsonFormat(array $payload): array
    {
        //@NOTE Just too hard if it is an array of objects
        //$payload['format'] = 'json';
        return $payload;
    }

    /**
     * This is to get functions out of the llm
     * if none are returned your system
     * can error out or try another way.
     *
     * @param  MessageInDto[]  $messages
     */
    public function functionPromptChat(array $messages, array $only = []): array
    {
        Log::info('LlmDriver::OllmaClient::functionPromptChat', $messages);

        $functions = [];

        if (Feature::active('ollama-functions')) {
            $messages = $this->insertFunctionsIntoMessageArray($messages);

            $response = $this->getClient()->post('/chat', [
                'model' => $this->getConfig('ollama')['models']['completion_model'],
                'messages' => $messages,
                'format' => 'json',
                'stream' => false,
            ]);

            $results = $response->json()['message']['content'];
            $functionsFromResults = json_decode($results, true);
            $functions = []; //reset this
            if ($functionsFromResults) {
                if (
                    array_key_exists('arguments', $functionsFromResults) &&
                    array_key_exists('name', $functionsFromResults) &&
                    data_get($functionsFromResults, 'name') !== 'search_and_summarize') {
                    $functions[] = $functionsFromResults;
                }
            }
        } else {
            Log::info('LlmDriver::OllamaClient::functionPromptChat is not active');
        }

        /**
         * @TODO
         * make this a dto
         */
        return $functions;
    }

    /**
     * @param  MessageInDto[]  $messages
     *
     * @throws BindingResolutionException
     */
    public function chat(array $messages): CompletionResponse
    {
        Log::info('LlmDriver::OllamaClient::chat');

        $messages = $this->remapMessages($messages);

        $payload = [
            'model' => $this->getConfig('ollama')['models']['completion_model'],
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => 0,
            ],
        ];

        $payload = $this->modifyPayload($payload);

        $response = $this->getClient()->post('/chat', $payload);

        if ($response->failed()) {
            Log::error('Ollama API Error ', [
                'error' => $response->body(),
            ]);
            throw new \Exception('Ollama API Error Chat');
        }

        return OllamaChatCompletionResponse::from($response->json());
    }

    /**
     * @return CompletionResponse[]
     *
     * @throws \Exception
     */
    public function completionPool(array $prompts, int $temperature = 0): array
    {
        Log::info('LlmDriver::Ollama::completionPool');
        $baseUrl = Setting::getSecret('ollama', 'api_url');

        if (! $baseUrl) {
            throw new \Exception('Ollama API Base URL or Token not found');
        }

        $model = $this->getConfig('ollama')['models']['completion_model'];
        $responses = Http::pool(function (Pool $pool) use (
            $prompts,
            $model,
            $baseUrl
        ) {
            foreach ($prompts as $prompt) {
                $payload = [
                    'model' => $model,
                    'prompt' => $prompt,
                    'stream' => false,
                ];

                $payload = $this->modifyPayload($payload);

                Log::info('Ollama Request', [
                    'prompt' => $prompt,
                    'payload' => $payload,
                ]);

                $pool->withHeaders([
                    'content-type' => 'application/json',
                ])->timeout(300)
                    ->baseUrl($baseUrl)
                    ->post('/generate', $payload);
            }
        });

        $results = [];

        foreach ($responses as $index => $response) {
            if ($response->ok()) {
                $results[] = CompletionResponse::from([
                    'content' => $response->json()['response'],
                ]);
            } else {
                Log::error('Ollama API Error ', [
                    'index' => $index,
                    'error' => $response->body(),
                ]);
            }
        }

        return $results;
    }

    public function completion(string $prompt): CompletionResponse
    {
        Log::info('LlmDriver::Ollama::completion');

        $response = $this->getClient()->post('/generate', [
            'model' => $this->getConfig('ollama')['models']['completion_model'],
            'prompt' => $prompt,
            'stream' => false,
        ]);

        /**
         * @see https://github.com/ollama/ollama/blob/main/docs/api.md#generate-a-chat-completion
         */
        $results = $response->json()['response'];

        return CompletionResponse::from([
            'content' => $results,
            'stop_reason' => 'stop',
        ]);
    }

    protected function getClient()
    {
        $api_token = Setting::getSecret('ollama', 'api_key');
        $baseUrl = Setting::getSecret('ollama', 'api_url');

        Log::info('LlmDriver::OllamaClient::getClient', [
            'api_token' => $api_token,
            'baseUrl' => $baseUrl,
        ]);

        if (! $api_token || ! $baseUrl) {
            throw new \Exception('Ollama API Base URL or Token not found');
        }

        return Http::withHeaders([
            'content-type' => 'application/json',
        ])
            ->retry(3, 6000)
            ->timeout(120)
            ->baseUrl($baseUrl);
    }

    public function getFunctions(): array
    {
        if (Feature::active('ollama-functions')) {
            $functions = parent::getFunctions();

            return $this->remapFunctions($functions);
        } else {
            return [];
        }
    }

    public function remapFunctions(array $functions): array
    {
        $results = collect($functions)->map(function ($function) {
            $properties = [];
            $required = [];

            foreach (data_get($function, 'parameters.properties', []) as $property) {
                $name = data_get($property, 'name');

                if (data_get($property, 'required', false)) {
                    $required[] = $name;
                }

                $properties[$name] = [
                    'description' => data_get($property, 'description', null),
                    'type' => data_get($property, 'type', 'string'),
                    'default' => data_get($property, 'default', null),
                ];
            }

            return [
                'type' => 'function',
                'function' => [
                    'name' => data_get($function, 'name'),
                    'description' => data_get($function, 'description'),
                    'parameters' => [
                        'type' => 'object',
                        'properties' => $properties,
                        'required' => $required,
                    ],
                ],

            ];

        })->values()->toArray();

        put_fixture('ollama_functions_remapped.json', $functions);

        return $functions;
    }

    public function isAsync(): bool
    {
        return false;
    }

    public function onQueue(): string
    {
        return 'ollama';
    }

    /**
     * @param  MessageInDto[]  $messages
     */
    public function remapMessages(array $messages): array
    {
        $messages = collect($messages)->transform(function (MessageInDto $message): array {
            return collect($message->toArray())
                ->only(['content', 'role', 'tool_calls', 'tool_used', 'input_tokens', 'output_tokens', 'model'])
                ->toArray();
        })->toArray();

        return $messages;
    }
}
