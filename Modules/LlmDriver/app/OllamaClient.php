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
        Log::info('LlmDriver::OllamaClient::completion');

        $messages = $this->remapMessages($messages);

        put_fixture('messages_llama3.json', $messages);

        $response = $this->getClient()->post('/chat', [
            'model' => $this->getConfig('ollama')['models']['completion_model'],
            'messages' => $messages,
            'stream' => false,
        ]);

        $results = $response->json()['message']['content'];

        return new CompletionResponse($results);
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
                $pool->withHeaders([
                    'content-type' => 'application/json',
                ])->timeout(120)
                    ->baseUrl($baseUrl)
                    ->post('/generate', [
                        'model' => $model,
                        'prompt' => $prompt,
                        'stream' => false,
                    ]);
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

        $results = $response->json()['response'];

        return new CompletionResponse($results);
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
            ->timeout(120)
            ->baseUrl($baseUrl);
    }

    public function getFunctions(): array
    {
        $functions = LlmDriverFacade::getFunctions();

        if (! Feature::activate('ollama-functions')) {
            return [];
        }

        return collect($functions)->map(function ($function) {
            $function = $function->toArray();
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
                'name' => data_get($function, 'name'),
                'description' => data_get($function, 'description'),
                'parameters' => $properties,
                'required' => $required,
            ];

        })->toArray();
    }

    public function isAsync(): bool
    {
        return false;
    }

    public function onQueue(): string
    {
        return 'ollama';
    }

    protected function remapMessages(array $messages): array
    {
        $messages = collect($messages)->map(function ($message) {
            return $message->toArray();
        });

        if (in_array('llama3', [
            $this->getConfig('ollama')['models']['completion_model']])) {
            Log::info('[LaraChain] LlmDriver::OllamaClient::remapMessages');
            $messages = collect($messages)->reverse();
        }

        return $messages->values()->toArray();

    }
}
