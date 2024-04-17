<?php

namespace LlmLaraHub\LlmDriver;

use App\Domains\Messages\RoleEnum;
use App\Events\ChatUiUpdateEvent;
use App\Models\Chat;
use Facades\App\Domains\Messages\SearchOrSummarizeChatRepo;
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

        if ($this->hasFunctions($functions)) {
            Log::info('[LaraChain] Orchestration Has Fucntions', $functions);
            /**
             * @TODO
             * We will deal with multi functions shortly
             * @TODO
             * When should messages be made
             * which class should make them
             * In this case I will assume the user of this class
             * save the Users input as a Message already
             */
            foreach ($functions as $function) {
                $functionName = data_get($function, 'name', null);

                if (is_null($functionName)) {
                    throw new \Exception('Function name is required');
                }

                ChatUiUpdateEvent::dispatch(
                    $chat->chatable,
                    $chat,
                    sprintf('We are running the agent %s back shortly',
                        str($functionName)->headline()->toString()
                    )
                );

                $functionClass = app()->make($functionName);

                $arguments = data_get($function, 'arguments');

                $arguments = is_array($arguments) ? json_encode($arguments) : '';

                $functionDto = FunctionCallDto::from([
                    'arguments' => $arguments,
                    'function_name' => $functionName,
                ]);

                /** @var FunctionResponse $response */
                $response = $functionClass->handle($messagesArray, $chat, $functionDto);

                $chat->addInput(
                    message: $response->content,
                    role: RoleEnum::Assistant,
                    show_in_thread: false);

                $messagesArray = Arr::wrap(MessageInDto::from([
                    'role' => 'assistant',
                    'content' => $response->content,
                ]));

                ChatUiUpdateEvent::dispatch(
                    $chat->chatable,
                    $chat,
                    'The Agent has completed the task going to the final step now');

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

                // $chat->addInput(
                //     message: "Use the previous assistant response to help for context to the users previous prompt",
                //     role: RoleEnum::User,
                //     show_in_thread: true);

                put_fixture('orchestration_message_array_followup_pre.json', $messagesArray);

                $results = LlmDriverFacade::driver($chat->chatable->getDriver())
                    ->chat($messagesArray);

                $chat->addInput(
                    message: $results->content,
                    role: RoleEnum::Assistant,
                    show_in_thread: true);

                /**
                 * Could just show this in the ui
                 */
                ChatUiUpdateEvent::dispatch(
                    $chat->chatable,
                    $chat,
                    'Functions and Agents have completed their tasks, results will appear shortly');

                $this->response = $results->content;
            }

            return $this->response;
        } else {
            /**
             * @NOTE
             * this assumes way too much
             */
            $message = collect($messagesArray)->first(
                function ($message) {
                    return $message->role === 'user';
                }
            )->content;

            return SearchOrSummarizeChatRepo::search($chat, $message);
        }
    }

    protected function hasFunctions(array $functions): bool
    {
        return is_array($functions) && count($functions) > 0;
    }
}
