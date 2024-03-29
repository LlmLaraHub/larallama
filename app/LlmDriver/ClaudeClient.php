<?php

namespace App\LlmDriver;

use App\LlmDriver\Requests\MessageInDto;
use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeClient
{

    protected string $baseUrl = 'https://api.anthropic.com/v1';

    protected string $version = '2023-06-01';

    protected string $driver = 'claude';

    public function embedData(string $data): EmbeddingsResponseDto
    {
        
        Log::info('LlmDriver::ClaudeClient::embedData');



        return EmbeddingsResponseDto::from([
            'embedding' => data_get($data, 'data.0.embedding'),
            'token_count' => 1000,
        ]);
    }

    /**
     * @param  MessageInDto[]  $messages
     */
    public function chat(array $messages): CompletionResponse
    {
        if (! app()->environment('testing')) {
            sleep(2);
        }

        Log::info('LlmDriver::MockClient::completion');

        $data = <<<'EOD'
        Voluptate irure cillum dolor anim officia reprehenderit dolor. Eiusmod veniam nostrud consectetur incididunt proident id. Anim adipisicing pariatur amet duis Lorem sunt veniam veniam est. Deserunt ea aliquip cillum pariatur consectetur. Dolor in reprehenderit adipisicing consectetur cupidatat ad cupidatat reprehenderit. Nostrud mollit voluptate aliqua anim pariatur excepteur eiusmod velit quis exercitation tempor quis excepteur.        
EOD;

        return new CompletionResponse($data);
    }

    public function completion(string $prompt): CompletionResponse
    {
        $model = $this->getConfig('claude')['models']['completion_model']; 
        $maxTokens = $this->getConfig('claude')['max_tokens']; 

        Log::info('LlmDriver::Claude::completion');

        $results = $this->getClient()->post('/messages', [
            'model' => $model,
            "max_tokens" => $maxTokens,
            'messages' => [
                [
                    'role' => "user",
                    'content' => $prompt
                ]
            ],
        ]);


        if(!$results->ok()) {
            $error = $results->json()['error']['type'];
            Log::error('Claude API Error ' . $error);
            throw new \Exception('Claude API Error ' . $error);
        }

        $data = null;

        foreach($results->json()['content'] as $content) {
            $data = $content['text'];
        }

        return CompletionResponse::from([
            'content' => $data,
        ]);
    }

    protected function getError(Response $response) {
        return $response->json()['error']['type'];
    }

    protected function getClient() {
        $api_token = $this->getConfig('claude')['api_key'];
        if(!$api_token) {
            throw new \Exception('Claude API Token not found');
        }

        return Http::withHeaders([
            'x-api-key' => $api_token,
            'anthropic-version' => $this->version,
            'content-type' => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    protected function getConfig(string $driver): array
    {
        return config("llmdriver.drivers.$driver");
    }
}
