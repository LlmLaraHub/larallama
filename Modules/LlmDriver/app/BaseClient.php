<?php

namespace LlmLaraHub\LlmDriver;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;

abstract class BaseClient
{
    protected string $driver = 'mock';

    protected int $poolSize = 3;

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

    protected function messagesToArray(array $messages): array
    {
        return collect($messages)->map(function ($message) {
            return $message->toArray();
        })->toArray();
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

    /**
     * @return CompletionResponse[]
     *
     * @throws \Exception
     */
    public function completionPool(array $prompts, int $temperature = 0): array
    {
        Log::info('LlmDriver::MockClient::completionPool');

        return [
            $this->completion($prompts[0]),
        ];
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
     * @param  MessageInDto[]  $messages
     */
    protected function insertFunctionsIntoMessageArray(array $messages): array
    {
        $functions = $this->getFunctions();

        $functionsEncoded = collect($functions)->transform(
            function ($item) {
                $name = data_get($item, 'name');
                $description = data_get($item, 'description');
                $input_schema = data_get($item, 'input_schema', []);
                $input_schema = json_encode($input_schema);

                return sprintf("### START FUNCTION \n name: %s, description: %s, parameters: %s \n ###  END FUNCTION", $name, $description, $input_schema);
            }
        )->implode('\n');

        $systemPrompt = <<<EOD
        You are a helpful assistant in a Retrieval augmented generation system (RAG - an architectural approach that can improve the efficacy of large language model (LLM) applications by leveraging custom data) system with tools and functions to help perform tasks.
        When you find the right function make sure to return just the JSON that represents the requirements of that function.
        If no function is found just return {} empty json

        If so can you return the function name and arguments to call it with. the return format would just be json
        and it would be empty if no function is needed. But if a function is needed it would be like this:
        [
            {
                "name": "example_function_name",
                "arguments": {
                    "prompt": "The users prompt here"
                }
            }
        ]
        Here is a list of the function names, description and parameters for the function. IT IS OK TO RETURN EMPTY ARRAY if none are needed.
        No extra text like "I think it is this function"
        The default function the system uses will take care of anything else so if the user just wants a word or phrase search just return an empy array the default.
        Do not stray from this below list since these are the only functions the system can run other than the default one mentioned above. The below list of
        functions to choose from will start with ### START FUNCTION and end with ### END FUNCTION. Pleas ONLY choose from that list and return JSON OR return [] if
        none are a fit which is ok too:
        {$functionsEncoded}
        EOD;

        $messages = $this->messagesToArray($messages);

        if (! collect($messages)->first(
            function ($message) {
                return $message['role'] === 'system';
            }
        )) {
            $messages = Arr::prepend($messages, [
                'content' => $systemPrompt,
                'role' => 'system',
            ]);
        } else {
            foreach ($messages as $index => $message) {
                if ($message['role'] === 'system') {
                    $messages[$index]['content'] = $systemPrompt;
                }
            }
        }

        return $messages;
    }

    /**
     * @NOTE
     * Some systems like Claude have to do this
     * So adding it here as a standar options
     *
     * @param  MessageInDto[]  $messagess
     */
    protected function remapMessages(array $messages): array
    {
        return $messages;
    }

    public function onQueue(): string
    {
        return 'api_request';
    }

    public function getMaxTokenSize(string $driver): int
    {
        $driver = config("llmdriver.drivers.$driver");

        return data_get($driver, 'max_tokens', 8192);
    }

    public function poolSize(): int
    {
        return $this->poolSize;
    }
}
