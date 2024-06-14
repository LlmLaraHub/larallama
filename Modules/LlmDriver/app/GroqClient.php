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

class GroqClient extends BaseClient
{
    protected string $baseUrl = 'https://api.groq.com/openai/v1';

    protected string $driver = 'groq';

    public function embedData(string $data): EmbeddingsResponseDto
    {
        throw new \Exception('Not implemented in Groq');
    }

    /**
     * @param  MessageInDto[]  $messages
     */
    public function chat(array $messages): CompletionResponse
    {
        $model = $this->getConfig('groq')['models']['completion_model'];
        $maxTokens = $this->getConfig('groq')['max_tokens'];

        Log::info('LlmDriver::Groq::chat');

        $messages = $this->remapMessages($messages);

        $results = $this->getClient()->post('/chat/completions', [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => $this->messagesToArray($messages),
        ]);

        if (! $results->ok()) {
            $error = $this->getError($results);
            Log::error('Groq API Error '.$error);
            throw new \Exception('Groq API Error '.$error);
        }

        $data = null;

        foreach ($results->json()['choices'] as $content) {
            $data = data_get($content, 'message.content', null);
        }

        return CompletionResponse::from([
            'content' => $data,
        ]);
    }

    public function completion(string $prompt): CompletionResponse
    {
        $model = $this->getConfig('groq')['models']['completion_model'];
        $maxTokens = $this->getConfig('groq')['max_tokens'];

        Log::info('LlmDriver::Groq::completion');

        $results = $this->getClient()->post('/chat/completions', [
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
            $error = $this->getError($results);
            Log::error('Groq API Error '.$error);
            throw new \Exception('Groq API Error '.$error);
        }

        $data = null;

        foreach ($results->json()['choices'] as $content) {
            $data = data_get($content, 'message.content', null);
        }

        return CompletionResponse::from([
            'content' => $data,
        ]);
    }

    protected function getError(Response $response)
    {
        return $response->json()['error']['message'];
    }

    /**
     * @return CompletionResponse[]
     *
     * @throws \Exception
     */
    public function completionPool(array $prompts, int $temperature = 0): array
    {
        $token = Setting::getSecret('groq', 'api_key');

        if (is_null($token)) {
            throw new \Exception('Missing Groq ai api key');
        }

        $model = $this->getConfig('groq')['models']['completion_model'];
        $maxTokens = $this->getConfig('groq')['max_tokens'];

        $responses = Http::pool(function (Pool $pool) use (
            $prompts,
            $token,
            $model,
            $maxTokens
        ) {
            foreach ($prompts as $prompt) {
                $pool->withHeaders([
                    'content-type' => 'application/json',
                    'Authorization' => 'Bearer '.$token,
                ])->withToken($token)
                    ->retry(3, 6000)
                    ->timeout(120)
                    ->baseUrl($this->baseUrl)
                    ->post('/chat/completions', [
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
                $response = $response->json();
                foreach (data_get($response, 'choices', []) as $result) {
                    $result = data_get($result, 'message.content', '');
                    $results[] = CompletionResponse::from([
                        'content' => $result,
                    ]);
                }
            } else {
                Log::error('Groq API Error ', [
                    'index' => $index,
                    'error' => $response->body(),
                ]);
            }
        }

        return $results;
    }

    protected function getClient()
    {
        $api_token = Setting::getSecret('groq', 'api_key');

        if (! $api_token) {
            throw new \Exception('Groq API Token not found');
        }

        return Http::retry(3, 6000)->timeout(120)->withToken($api_token)->withHeaders([
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
        Log::info('LlmDriver::GroqClient::functionPromptChat', $messages);

        $model = $this->getConfig('groq')['models']['completion_model'];

        $maxTokens = $this->getConfig('groq')['max_tokens'];

        $messages = $this->insertFunctionsIntoMessageArray($messages);

        $results = $this->getClient()->post('/chat/completions', [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => $messages,
        ]);

        $functions = [];

        if (! $results->ok()) {
            $error = $this->getError($results);
            Log::error('Groq API Error '.$error);
            throw new \Exception('Groq API Error '.$error);
        }

        foreach ($results->json()['choices'] as $content) {
            $functionArray = data_get($content, 'message.content', []);
            $functionArray = json_decode($functionArray, true);
            foreach ($functionArray as $possibleFunction) {
                $functions[] = $possibleFunction;
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

    public function onQueue(): string
    {
        return 'groq';
    }
}
