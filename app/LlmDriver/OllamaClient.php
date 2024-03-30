<?php

namespace App\LlmDriver;

use App\LlmDriver\Requests\MessageInDto;
use App\LlmDriver\Responses\CompletionResponse;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI;

class OllamaClient extends BaseClient
{

    protected string $driver = 'ollama';

    /**
     * 
     * @param MessageInDto[] $messages 
     * @return CompletionResponse 
     * @throws BindingResolutionException 
     */
    public function chat(array $messages): CompletionResponse
    {
        Log::info('LlmDriver::OllamaClient::completion');

        $response = $this->getClient()->post('/chat', [
            'model' => $this->getConfig('ollama')['models']['completion_model'],
            'messages' => collect($messages)->map(function ($message) {
                return $message->toArray();
            })->toArray(),
            'stream' => false,
        ]);

        $results =$response->json()['message']['content'];

        return new CompletionResponse($results);
    }
    
    public function completion(string $prompt): CompletionResponse
    {
        Log::info('LlmDriver::Ollama::completion');

        $response = $this->getClient()->post('/generate', [
            'model' => $this->getConfig('ollama')['models']['completion_model'],
            'prompt' => $prompt,
            'stream' => false,
        ]);

        $results =$response->json()['response'];

        return new CompletionResponse($results);
    }

    protected function getClient() {
        $api_token = $this->getConfig('ollama')['api_key'];
        $baseUrl = $this->getConfig('ollama')['api_url'];
        if (! $api_token || ! $baseUrl) {
            throw new \Exception('Ollama API Base URL or Token not found');
        }

        return Http::withHeaders([
            'content-type' => 'application/json',
        ])->baseUrl($baseUrl);
    }
}
