<?php

namespace LlmLaraHub\LlmDriver;

use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;

abstract class BaseClient
{
    protected string $driver = 'mock';

    public function embedData(string $data): EmbeddingsResponseDto
    {
        if (! app()->environment('testing')) {
            sleep(2);
        }
        Log::info('LlmDriver::MockClient::embedData');

        $data = get_fixture('embedding_response.json');

        return EmbeddingsResponseDto::from([
            'embedding' => data_get($data, 'data.0.embedding'),
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
        if (! app()->environment('testing')) {
            sleep(2);
        }

        Log::info('LlmDriver::MockClient::functionPromptChat', $messages);

        $data = get_fixture('openai_response_with_functions_summarize_collection.json');

        $functions = [];

        foreach (data_get($data, 'choices', []) as $choice) {
            foreach (data_get($choice, 'message.toolCalls', []) as $tool) {
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
     * @param  MessageInDto[]  $messages
     */
    public function chat(array $messages): CompletionResponse
    {
        if (! app()->environment('testing')) {
            sleep(2);
        }

        Log::info('LlmDriver::MockClient::completion');

        $data = fake()->sentences(3, true);

        return new CompletionResponse($data);
    }

    public function completion(string $prompt): CompletionResponse
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

    protected function getConfig(string $driver): array
    {
        return config("llmdriver.drivers.$driver");
    }

    public function isAsync(): bool
    {
        return true;
    }

    public function hasFunctions(): bool
    {
        return count($this->getFunctions()) > 0;
    }

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

    /**
     * @NOTE
     * Some systems like Claude have to do this
     * So adding it here as a standar options
     *
     * @param  MessageInDto[]  $messages
     */
    protected function remapMessages(array $messages): array
    {
        return $messages;
    }

    public function onQueue(): string
    {
        return 'default';
    }
}
