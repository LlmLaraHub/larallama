<?php

namespace LlmLaraHub\LlmDriver;

use App\Domains\Messages\RoleEnum;
use App\Events\ChatUiUpdateEvent;
use App\Models\Chat;
use App\Models\PromptHistory;
use Facades\App\Domains\Messages\SearchAndSummarizeChatRepo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Functions\FunctionCallDto;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class Orchestrate
{
    protected string $response = '';

    protected bool $requiresFollowup = false;

    /**
     * @param  MessageInDto[]  $messagesArray
     */
    public function handle(array $messagesArray, Chat $chat): ?string
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

        if ($this->hasFunctions($functions)) {
            Log::info('[LaraChain] Orchestration Has Functions', $functions);

            foreach ($functions as $function) {
                $functionName = data_get($function, 'name', null);

                if (is_null($functionName)) {
                    throw new \Exception('Function name is required');
                }

                notify_ui($chat, 'We are running the agent back shortly');

                $functionClass = app()->make($functionName);

                $arguments = data_get($function, 'arguments');

                $arguments = is_array($arguments) ? json_encode($arguments) : '';

                $functionDto = FunctionCallDto::from([
                    'arguments' => $arguments,
                    'function_name' => $functionName,
                ]);

                /** @var FunctionResponse $response */
                $response = $functionClass->handle($messagesArray, $chat, $functionDto);

                if ($response->save_to_message) {

                    $message = $chat->addInput(
                        message: $response->content,
                        role: RoleEnum::Assistant,
                        show_in_thread: true);

                    if($response->prompt) {
                        PromptHistory::create([
                            'prompt' => $response->prompt,
                            'chat_id' => $chat->id,
                            'message_id' => $message->id,
                            'collection_id' => $chat->getChatable()?->id,
                        ]);
                    }
                }

                $messagesArray = Arr::wrap(MessageInDto::from([
                    'role' => 'assistant',
                    'content' => $response->content,
                ]));

                notify_ui($chat, 'The Agent has completed the task going to the final step now');                    

                $this->response = $response->content;
                $this->requiresFollowup = $response->requires_follow_up_prompt;
            }

            /**
             * @NOTE the function might return the results of a table
             * or csv file or image info etc.o
             * This prompt should consider the initial prompt and the output of the function(s)
             */
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

            return $this->response;
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

            return SearchAndSummarizeChatRepo::search($chat, $message);
        }
    }

    protected function hasFunctions(array $functions): bool
    {
        return is_array($functions) && count($functions) > 0;
    }
}
