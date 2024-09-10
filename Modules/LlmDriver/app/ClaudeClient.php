<?php

namespace LlmLaraHub\LlmDriver;

use App\Domains\Messages\RoleEnum;
use App\Models\Setting;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LlmLaraHub\LlmDriver\Functions\FunctionDto;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\ClaudeCompletionResponse;
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

        Log::info('LlmDriver::Claude::chat');

        $messages = $this->remapMessages($messages);

        $payload = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => $messages,
        ];

        $payload = $this->modifyPayload($payload);

        $results = $this->getClient()->post('/messages', $payload);

        if (! $results->ok()) {
            $error = $results->json()['error']['type'];
            $message = $results->json()['error']['message'];
            Log::error('Claude API Error Chat', [
                'type' => $error,
                'message' => $message,
            ]);

            throw new \Exception('Claude API Error Chat');
        }

        return ClaudeCompletionResponse::from($results->json());
    }

    public function completion(string $prompt): CompletionResponse
    {
        Log::info('LlmDriver::Claude::completion using chat');

        $prompt = MessageInDto::from([
            'content' => $prompt,
            'role' => 'user',
        ]);

        return $this->chat([
            $prompt,
        ]);
    }

    public function getContentAndToolTypeFromResults(Response $results): array
    {
        $data = 'No results found';
        $results = $results->json();
        $tool_used = null;
        $stop_reason = data_get($results, 'stop_reason', 'end_turn');

        if ($stop_reason === 'tool_use') {
            /**
             * @TOOD
             * The tool should be used here to get the
             * output since it might be different
             * for each tool
             */
            foreach ($results['content'] as $content) {
                $tool_used = data_get($content, 'name');
                $data = json_encode(data_get($content, 'input.results', []), JSON_THROW_ON_ERROR);
            }
        } else {
            foreach ($results['content'] as $content) {
                $data = $content['text'];
            }
        }

        return [$data, $tool_used, $stop_reason];
    }

    public function addJsonFormat(array $payload): array
    {
        //not available for Claude
        return $payload;
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
                $payload = [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ];

                $payload = $this->modifyPayload($payload);

                $pool->retry(3, 6000)->withHeaders([
                    'x-api-key' => $api_token,
                    'anthropic-beta' => 'tools-2024-04-04',
                    'anthropic-version' => $this->version,
                    'content-type' => 'application/json',
                ])->baseUrl($this->baseUrl)
                    ->timeout(240)
                    ->post('/messages', $payload);

            }

        });

        $results = [];

        foreach ($responses as $index => $response) {
            if ($response->successful()) {
                [$data, $tool_used, $stop_reason] = $this->getContentAndToolTypeFromResults($response);

                $results[] = CompletionResponse::from([
                    'content' => $data,
                    'tool_used' => $tool_used,
                    'stop_reason' => $stop_reason,
                    'input_tokens' => data_get($results, 'usage.input_tokens', null),
                    'output_tokens' => data_get($results, 'usage.output_tokens', null),
                ]);
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
        $api_url = Setting::getSecret('claude', 'api_url');

        if (! $api_token) {
            throw new \Exception('Claude API Token not found');
        }

        return Http::retry(3, 6000)->withHeaders([
            'x-api-key' => $api_token,
            'anthropic-beta' => 'tools-2024-04-04',
            'anthropic-version' => $this->version,
            'content-type' => 'application/json',
        ])->retry(3, 6000)
            ->baseUrl($api_url);
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

        $messages = $this->remapMessages($messages, true);

        /**
         * @NOTE
         * The api will not let me end this array in an assistant message
         * it has to end in a user message
         */
        Log::info('LlmDriver::ClaudeClient::functionPromptChat', $messages);

        $model = $this->getConfig('claude')['models']['completion_model'];
        $maxTokens = $this->getConfig('claude')['max_tokens'];

        $results = $this->getClient()->post('/messages', [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => $messages,
            'tools' => $this->getFunctions(),
        ]);

        $functions = [];

        if (! $results->ok()) {
            $error = $results->json()['error']['type'];
            $message = $results->json()['error']['message'];

            Log::error('Claude API Error  getting functions ', [
                'type' => $error,
                'message' => $message,
            ]);

            throw new \Exception('Claude API Error getting functions');
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

    public function getFunctions(): array
    {
        $functions = parent::getFunctions();

        return $this->remapFunctions($functions);
    }

    /**
     * @param  FunctionDto[]  $functions
     */
    public function remapFunctions(array $functions): array
    {
        return collect($functions)->map(function ($function) {
            $properties = [];
            $required = [];

            $type = data_get($function, 'parameters.type', 'object');

            foreach (data_get($function, 'parameters.properties', []) as $property) {
                $name = data_get($property, 'name');

                if (data_get($property, 'required', false)) {
                    $required[] = $name;
                }

                $subType = data_get($property, 'type', 'string');
                $properties[$name] = [
                    'description' => data_get($property, 'description', null),
                    'type' => $subType,
                ];

                if ($subType === 'array') {
                    $subItems = $property['properties'][0]->properties; //stop at this for now
                    $subItemsMapped = [];
                    foreach ($subItems as $subItemKey => $subItemValue) {
                        $subItemsMapped[$subItemValue->name] = [
                            'type' => $subItemValue->type,
                            'description' => $subItemValue->description,
                        ];
                    }

                    $properties[$name]['items'] = [
                        'type' => 'object',
                        'properties' => $subItemsMapped,
                    ];
                }
            }

            $itemsOrProperties = $properties;

            return [
                'name' => data_get($function, 'name'),
                'description' => data_get($function, 'description'),
                'input_schema' => [
                    'type' => 'object',
                    'properties' => $itemsOrProperties,
                    'required' => $required,
                ],
            ];
        })->values()->toArray();
    }

    /**
     * @see https://docs.anthropic.com/claude/reference/messages_post
     * The order of the messages has to be start is oldest
     * then descending is the current
     * with each one alternating between user and assistant
     *
     * @param  MessageInDto[]  $messages
     */
    public function remapMessages(array $messages, bool $userLast = false): array
    {

        /**
         * Claude needs to not start with a system message
         */
        $messages = collect($messages)
            ->filter(function ($item) {
                if ($item->role === 'system') {
                    $this->system = $item->content;

                    return false;
                }

                return true;
            })
            ->transform(function (MessageInDto $item) {
                /**
                 * @NOTE
                 * Claude does not like to end a certain way
                 */
                $item->content = str(
                    cleanString($item->content)
                )->replaceEnd("\n", '')->trim()->toString();

                if (empty($item->content)) {
                    $item->content = 'See content in thread.';
                }

                return $item->toArray();
            });

        /**
         * Claude needs me to not use the role tool
         * but instead set that to role user
         * and make the content string an array
         * and other odd stuff.
         */
        $updatesToMessages = [];

        $messages->map(function ($item, $key) use (&$updatesToMessages) {
            if ($item['role'] === RoleEnum::Tool->value) {
                $toolId = data_get($item, 'tool_id', 'toolu_'.Str::random(32));
                $tool = data_get($item, 'tool', 'unknown_tool');
                $args = data_get($item, 'args', '{}');

                $content = $item['content'];

                $updatesToMessages[] = [
                    'role' => 'assistant',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "<thinking>$content</thinking>",
                        ],
                        [
                            'type' => 'tool_use',
                            'id' => $toolId,
                            'name' => $tool,
                            'input' => $args,
                        ],
                    ],
                ];

                $updatesToMessages[] = [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => $toolId,
                            'content' => $content,
                        ],
                    ],
                ];
            } else {
                $updatesToMessages[] = [
                    'role' => $item['role'],
                    'content' => $item['content'],
                ];
            }

            return $item;
        });

        /**
         * Finally have to make the user assistant sandwich
         * that Claude seems to require for the api
         */
        $lastRole = null;
        $newMessagesArray = [];
        foreach ($updatesToMessages as $index => $message) {
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

        if ($userLast) {
            $last = Arr::last($newMessagesArray);

            if ($last['role'] === 'assistant') {
                $newMessagesArray[] = [
                    'role' => 'user',
                    'content' => 'Using the surrounding context to continue this response thread',
                ];
            }
        }

        return $newMessagesArray;
    }

    public function onQueue(): string
    {
        return 'claude';
    }
}
