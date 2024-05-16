<?php

namespace LlmLaraHub\LlmDriver;

use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAiClient extends BaseClient
{
    protected string $driver = 'openai';

    /**
     * @param  MessageInDto[]  $messages
     */
    public function chat(array $messages): CompletionResponse
    {

        $response = OpenAI::chat()->create([
            'model' => $this->getConfig('openai')['models']['chat_model'],
            'messages' => $this->messagesToArray($messages),
        ]);

        $results = null;

        foreach ($response->choices as $result) {
            $results = $result->message->content;
        }

        return new CompletionResponse($results);
    }

    public function embedData(string $data): EmbeddingsResponseDto
    {

        $response = OpenAI::embeddings()->create([
            'model' => $this->getConfig('openai')['models']['embedding_model'],
            'input' => $data,
        ]);

        $results = [];

        foreach ($response->embeddings as $embedding) {
            $results = $embedding->embedding; // [0.018990106880664825, -0.0073809814639389515, ...]
        }

        return EmbeddingsResponseDto::from([
            'embedding' => $results,
            'token_count' => $response->usage->totalTokens,
        ]);
    }

    public function completion(string $prompt, int $temperature = 0): CompletionResponse
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => $this->getConfig('openai')['models']['completion_model'],
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);
        } catch (\Exception $e) {
            Log::info("[LaraChain] - OpenAi Rate limit maybe I should just use Http");

            if(str($e->getMessage())->contains("Rate limit reached")) {
                sleep(60);
                $this->completion($prompt, 0);
            }

            throw new \Exception("OpenAi error", [
                'error' =>$e->getMessage()
            ]);
        }

        $results = null;

        foreach ($response->choices as $result) {
            $results = $result->message->content;
        }

        return new CompletionResponse($results);
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

        Log::info('LlmDriver::OpenAiClient::functionPromptChat', $messages);

        $functions = $this->getFunctions();

        $response = OpenAI::chat()->create([
            'model' => $this->getConfig('openai')['models']['chat_model'],
            'messages' => collect($messages)->map(function ($message) {
                return $message->toArray();
            })->toArray(),
            'tool_choice' => 'auto',
            'tools' => $functions,
        ]);

        $functions = [];
        foreach ($response->choices as $result) {
            foreach (data_get($result, 'message.toolCalls', []) as $tool) {
                if (data_get($tool, 'type') === 'function') {
                    $name = data_get($tool, 'function.name', null);
                    if (! in_array($name, $only)) {
                        $functions[] = [
                            'name' => $name,
                            'arguments' => json_decode(data_get($tool, 'function.arguments', []), true),
                        ];
                    }
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
                'type' => 'function',
                'function' => [
                    'name' => data_get($function, 'name'),
                    'description' => data_get($function, 'description'),
                    'parameters' => [
                        'type' => 'object',
                        'properties' => $properties,
                    ],
                    'required' => $required,
                ],
            ];
        })->toArray();
    }
}
