<?php

namespace App\LlmDriver;

use App\LlmDriver\Requests\MessageInDto;
use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $messages = collect($messages)->map(function ($item) {
            if ($item->role === 'system') {
                $item->role = 'assistant';
            }

            return $item->toArray();
        })->reverse()->values();

        $messages = $messages->flatMap(function ($item, $index) use ($messages) {
            if ($index > 0 && $item['role'] === 'assistant' && optional($messages->get($index + 1))['role'] === 'assistant') {
                return [
                    $item,
                    ['role' => 'user', 'content' => 'Continuation of search results'],
                ];
            }

            return [$item];
        })->toArray();

        put_fixture('claude_messages_debug.json', $messages);

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

    protected function getError(Response $response)
    {
        return $response->json()['error']['type'];
    }

    protected function getClient()
    {
        $api_token = $this->getConfig('claude')['api_key'];
        if (! $api_token) {
            throw new \Exception('Claude API Token not found');
        }

        return Http::withHeaders([
            'x-api-key' => $api_token,
            'anthropic-version' => $this->version,
            'content-type' => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

}
