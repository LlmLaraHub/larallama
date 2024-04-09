<?php

namespace App\LlmDriver;

use App\LlmDriver\Requests\MessageInDto;
use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;

class OllamaClient extends BaseClient
{
    protected string $driver = 'ollama';

    public function embedData(string $prompt): EmbeddingsResponseDto
    {
        Log::info('LlmDriver::Ollama::completion');

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
            $functions = $this->getFunctions();

            $functionsEncoded = collect($functions)->transform(
                function ($item) {
                    return sprintf("### START FUNCTION \n name: %s, description: %s, parameters: %s \n### ", $item['name'], $item['description'], json_encode($item['parameters']));
                })->implode("\n");

            $messages = collect($messages)->each(function ($message, $loop) use ($functionsEncoded, $messages) {

                if ($loop === count($messages) - 1) {
                    $prompt = <<<EOD
                        Does the following question prompt from the user: 
                        ### START PROMPT
                        {$message->content} 
                        ### END PROMPT
    
                        Need one of the following functions to answer it? 
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
                        The default function the system uses will take care of anything else so if the user just wants a word or phrase search just return an empy array the default.
                        Do not stray from this below list since these are the only functions the system can run other than the default one mentioned above. The below list of 
                        functions to choose from will start with ### START FUNCTION and end with ### END FUNCTION. Pleas ONLY choose from that list and return JSON OR return [] if 
                        none are a fit which is ok too: 
                        {$functionsEncoded}
                        EOD;

                    $message->content = $prompt;
                }
            }
            )->map(function ($message) {
                return $message->toArray();
            })->toArray();

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

        $response = $this->getClient()->post('/chat', [
            'model' => $this->getConfig('ollama')['models']['completion_model'],
            'messages' => collect($messages)->map(function ($message) {
                return $message->toArray();
            })->toArray(),
            'stream' => false,
        ]);

        $results = $response->json()['message']['content'];

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

        $results = $response->json()['response'];

        return new CompletionResponse($results);
    }

    protected function getClient()
    {
        $api_token = $this->getConfig('ollama')['api_key'];
        $baseUrl = $this->getConfig('ollama')['api_url'];
        if (! $api_token || ! $baseUrl) {
            throw new \Exception('Ollama API Base URL or Token not found');
        }

        return Http::withHeaders([
            'content-type' => 'application/json',
        ])->baseUrl($baseUrl);
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
}
