<?php

namespace LlmLaraHub\LlmDriver;

use App\Models\Setting;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;

class ClaudeClient extends BaseClient
{
    protected string $baseUrl = 'https://api.anthropic.com/v1';

    protected string $version = '2023-06-01';

    protected string $driver = 'claude';

    public function embedData(string $data): EmbeddingsResponseDto
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @param  MessageInDto[]  $messages
     */
    public function chat(array $messages): CompletionResponse
    {
        $model = $this->getConfig('claude')['models']['completion_model'];
        $maxTokens = $this->getConfig('claude')['max_tokens'];

        Log::info('LlmDriver::Claude::completion');

        /**
         * I need to iterate over each item
         * then if there are two rows with role assistant I need to insert
         * in betwee a user row with some copy to make it work like "And the user search results had"
         * using the Laravel Collection library
         */
        $messages = $this->remapMessages($messages);

        $results = $this->getClient()->post('/messages', [
            'model' => $model,
            'system' => 'Return a markdown response.',
            'max_tokens' => $maxTokens,
            'messages' => $messages,
        ]);

        if (! $results->ok()) {
            $error = $results->json()['error']['type'];
            $message = $results->json()['error']['message'];
            Log::error('Claude API Error ', [
                'type' => $error,
                'message' => $message,
            ]);
            throw new \Exception('Claude API Error '.$message);
        }

        $data = null;

        foreach ($results->json()['content'] as $content) {
            $data = $content['text'];
        }

        return CompletionResponse::from([
            'content' => $data,
        ]);
    }

    public function completion(string $prompt): CompletionResponse
    {
        $model = $this->getConfig('claude')['models']['completion_model'];
        $maxTokens = $this->getConfig('claude')['max_tokens'];

        Log::info('LlmDriver::Claude::completion');

        $results = $this->getClient()->post('/messages', [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        if (! $results->ok()) {
            $error = $results->json()['error']['type'];
            Log::error('Claude API Error '.$error);
            throw new \Exception('Claude API Error '.$error);
        }

        $data = null;

        foreach ($results->json()['content'] as $content) {
            $data = $content['text'];
        }

        return CompletionResponse::from([
            'content' => $data,
        ]);
    }

    /**
     * @return CompletionResponse[]
     *
     * @throws \Exception
     */
    public function completionPool(array $prompts, int $temperature = 0): array
    {
        $api_token = Setting::getSecret('claude', 'api_key');
        $model = $this->getConfig('claude')['models']['completion_model'];
        $maxTokens = $this->getConfig('claude')['max_tokens'];

        if (is_null($api_token)) {
            throw new \Exception('Missing Claude api key');
        }

        $responses = Http::pool(function (Pool $pool) use (
            $prompts,
            $api_token,
            $model,
            $maxTokens) {
            foreach ($prompts as $prompt) {
                $pool->retry(3, 6000)->withHeaders([
                    'x-api-key' => $api_token,
                    'anthropic-beta' => 'tools-2024-04-04',
                    'anthropic-version' => $this->version,
                    'content-type' => 'application/json',
                ])->baseUrl($this->baseUrl)
                    ->timeout(240)
                    ->post('/messages', [
                        'model' => $model,
                        'max_tokens' => $maxTokens,
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt,
                            ],
                        ],
                    ]);
            }

        });

        $results = [];

        foreach ($responses as $index => $response) {
            if ($response->ok()) {
                foreach ($response->json()['content'] as $content) {
                    $results[] = CompletionResponse::from([
                        'content' => $content['text'],
                    ]);
                }
            } else {
                Log::error('Claude API Error ', [
                    'index' => $index,
                    'error' => $response->body(),
                ]);
            }
        }

        return $results;
    }

    protected function getError(Response $response)
    {
        return $response->json()['error']['type'];
    }

    protected function getClient()
    {
        $api_token = Setting::getSecret('claude', 'api_key');

        if (! $api_token) {
            throw new \Exception('Claude API Token not found');
        }

        return Http::retry(3, 6000)->withHeaders([
            'x-api-key' => $api_token,
            'anthropic-beta' => 'tools-2024-04-04',
            'anthropic-version' => $this->version,
            'content-type' => 'application/json',
        ])->baseUrl($this->baseUrl);
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
        $messages = $this->remapMessages($messages);
        Log::info('LlmDriver::ClaudeClient::functionPromptChat', $messages);

        $functions = $this->getFunctions();

        $model = $this->getConfig('claude')['models']['completion_model'];
        $maxTokens = $this->getConfig('claude')['max_tokens'];

        $results = $this->getClient()->post('/messages', [
            'model' => $model,
            'system' => 'Return a markdown response.',
            'max_tokens' => $maxTokens,
            'messages' => $messages,
            'tools' => $this->getFunctions(),
        ]);

        $functions = [];

        if (! $results->ok()) {
            $error = $results->json()['error']['type'];
            $message = $results->json()['error']['message'];
            Log::error('Claude API Error ', [
                'type' => $error,
                'message' => $message,
            ]);
            throw new \Exception('Claude API Error '.$message);
        }

        $stop_reason = $results->json()['stop_reason'];

        if ($stop_reason === 'tool_use') {

            foreach ($results->json()['content'] as $content) {
                if (data_get($content, 'type') === 'tool_use') {
                    $functions[] = [
                        'name' => data_get($content, 'name'),
                        'arguments' => data_get($content, 'input'),
                    ];
                }
            }
        }

        /**
         * @TODO
         * make this a dto
         */
        return $functions;
    }

    /**
     * @NOTE
     * Since this abstraction layer is based on OpenAi
     * Not much needs to happen here
     * but on the others I might need to do XML?
     */
    public function getFunctions(): array
    {
        $functions = LlmDriverFacade::getFunctions();

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
                    'enum' => data_get($property, 'enum', []),
                    'default' => data_get($property, 'default', null),
                ];
            }

            return [
                'name' => data_get($function, 'name'),
                'description' => data_get($function, 'description'),
                'input_schema' => [
                    'type' => 'object',
                    'properties' => $properties,
                    'required' => $required,
                ],
            ];
        })->toArray();
    }

    /**
     * @see https://docs.anthropic.com/claude/reference/messages_post
     * The order of the messages has to be start is oldest
     * then descending is the current
     * with each one alternating between user and assistant
     *
     * @param  MessageInDto[]  $messages
     */
    protected function remapMessages(array $messages): array
    {
        $messages = collect($messages)->map(function ($item) {
            if ($item->role === 'system') {
                $item->role = 'assistant';
            }

            return $item->toArray();
        })
            ->values();

        $lastRole = null;

        $newMessagesArray = [];

        foreach ($messages as $index => $message) {
            $currentRole = data_get($message, 'role');

            if ($currentRole === $lastRole) {
                if ($currentRole === 'assistant') {
                    $newMessagesArray[] = [
                        'role' => 'user',
                        'content' => 'Using the surrounding context to continue this response thread',
                    ];
                } else {
                    $newMessagesArray[] = [
                        'role' => 'assistant',
                        'content' => 'Using the surrounding context to continue this response thread',
                    ];
                }

                $newMessagesArray[] = $message;
            } else {
                $newMessagesArray[] = $message;
            }

            $lastRole = $currentRole;

        }

        return $newMessagesArray;
    }

    public function onQueue(): string
    {
        return 'claude';
    }
}
