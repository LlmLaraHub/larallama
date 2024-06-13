<?php

namespace LlmLaraHub\LlmDriver;

use App\Domains\Messages\RoleEnum;
use App\Models\Chat;
use App\Models\Filter;
use App\Models\PromptHistory;
use Facades\App\Domains\Messages\SearchAndSummarizeChatRepo;
use Facades\LlmLaraHub\LlmDriver\Functions\StandardsChecker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Functions\FunctionCallDto;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class Orchestrate
{
    use CreateReferencesTrait;

    protected string $response = '';

    protected bool $requiresFollowup = false;

    /**
     * @param  MessageInDto[]  $messagesArray
     */
    public function handle(
        array $messagesArray,
        Chat $chat,
        ?Filter $filter = null,
        string $tool = ''): ?string
    {
        /**
         * We are looking first for functions / agents / tools
         */
        Log::info('[LaraChain] Orchestration Function Check');

        $functions = LlmDriverFacade::driver($chat->chatable->getDriver())
            ->functionPromptChat($messagesArray);

        Log::info("['LaraChain'] Functions Found?", [
            'count' => count($functions),
            'functions' => $functions,
        ]);

        if ($tool) {
            Log::info('[LaraChain] Orchestration Has Tool', [
                'tool' => $tool,
            ]);

            /**
             * @TODO
             * sooo much to do
             * this has to be just a natural function
             * but the user is now forcing it which is fine too
             */
            if ($tool === 'standards_checker') {
                $functionDto = FunctionCallDto::from([
                    'arguments' => '{}',
                    'function_name' => 'standards_checker',
                    'filter' => $filter,
                ]);

                $response = StandardsChecker::handle($messagesArray, $chat, $functionDto);
                $messagesArray = $this->handleResponse($response, $chat);
                $this->response = $response->content;
                $this->requiresFollowup = $response->requires_follow_up_prompt;
                $this->requiresFollowUp($messagesArray, $chat);
            }

        } elseif ($this->hasFunctions($functions)) {
            Log::info('[LaraChain] Orchestration Has Functions', $functions);

            foreach ($functions as $function) {
                $functionName = data_get($function, 'name', null);

                if (is_null($functionName)) {
                    throw new \Exception('Function name is required');
                }

                notify_ui($chat, 'We are running the agent back shortly');

                Log::info('[LaraChain] - Running function '.$functionName);

                $functionClass = app()->make($functionName);

                $arguments = data_get($function, 'arguments');

                $arguments = is_array($arguments) ? json_encode($arguments) : '';

                $functionDto = FunctionCallDto::from([
                    'arguments' => $arguments,
                    'function_name' => $functionName,
                    'filter' => $filter,
                ]);

                /** @var FunctionResponse $response */
                $response = $functionClass->handle($messagesArray, $chat, $functionDto);

                Log::info('[LaraChain] - Function Response', [
                    'function' => $functionName,
                    'response' => $response,
                ]);

                $message = null;
                if ($response->save_to_message) {

                    $message = $chat->addInput(
                        message: $response->content,
                        role: RoleEnum::Assistant,
                        show_in_thread: true);
                }

                if ($response->prompt) {
                    PromptHistory::create([
                        'prompt' => $response->prompt,
                        'chat_id' => $chat->getChat()->id,
                        'message_id' => $message?->id,
                        /** @phpstan-ignore-next-line */
                        'collection_id' => $chat->getChatable()?->id,
                    ]);
                }

                if (! empty($response->documentChunks)) {
                    $this->saveDocumentReference(
                        $message,
                        $response->documentChunks
                    );
                }

                $messagesArray = Arr::wrap(MessageInDto::from([
                    'role' => 'assistant',
                    'content' => $response->content,
                ]));

                $this->response = $response->content;
                $this->requiresFollowup = $response->requires_follow_up_prompt;
            }

        } else {
            Log::info('[LaraChain] Orchestration No Functions Default Search And Summarize');
            /**
             * @NOTE
             * this assumes way too much
             */
            $message = collect($messagesArray)->first(
                function ($message) {
                    return $message->role === 'user';
                }
            )->content;

            return SearchAndSummarizeChatRepo::search($chat, $message, $filter);
        }

        $this->requiresFollowUp($messagesArray, $chat);

        notify_ui_complete($chat);

        return $this->response;
    }

    protected function hasFunctions(array $functions): bool
    {
        return is_array($functions) && count($functions) > 0;
    }

    /**
     * @return MessageInDto[]
     */
    protected function handleResponse(FunctionResponse $response, Chat $chat): array
    {
        $message = null;

        if ($response->save_to_message) {
            $message = $chat->addInput(
                message: $response->content,
                role: RoleEnum::Assistant,
                show_in_thread: true);
        }

        if ($response->prompt) {
            PromptHistory::create([
                'prompt' => $response->prompt,
                'chat_id' => $chat->getChat()->id,
                'message_id' => $message?->id,
                /** @phpstan-ignore-next-line */
                'collection_id' => $chat->getChatable()?->id,
            ]);
        }

        if (! empty($response->documentChunks)) {
            $this->saveDocumentReference(
                $message,
                $response->documentChunks
            );
        }

        $messagesArray = Arr::wrap(MessageInDto::from([
            'role' => 'assistant',
            'content' => $response->content,
        ]));

        return $messagesArray;
    }

    protected function requiresFollowUp(array $messagesArray, Chat $chat): void
    {
        if ($this->requiresFollowup) {
            Log::info('[LaraChain] Orchestration Requires Followup');

            $results = LlmDriverFacade::driver($chat->chatable->getDriver())
                ->chat($messagesArray);

            $chat->addInput(
                message: $results->content,
                role: RoleEnum::Assistant,
                show_in_thread: true);

            notify_ui($chat, 'Functions and Agents have completed their tasks, results will appear shortly');

            $this->response = $results->content;
        }
    }
}
