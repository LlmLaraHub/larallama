<?php

namespace LlmLaraHub\LlmDriver;

use App\Domains\Messages\RoleEnum;
use App\Jobs\OrchestrateBatchJob;
use App\Models\Chat;
use App\Models\Filter;
use App\Models\Message;
use App\Models\PromptHistory;
use Facades\App\Domains\Messages\SearchAndSummarizeChatRepo;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Functions\FunctionCallDto;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

/**
 * @NOTE
 * I hate notes but doing a lot of ref
 * and will clean these up as I go
 */
class Orchestrate
{
    use CreateReferencesTrait;
    use ToolsHelper;

    protected string $response = '';

    protected bool $requiresFollowup = false;

    public function handle(
        Chat $chat,
        Message $message): ?string
    {
        $messagesArray = $message->getLatestMessages();

        $filter = $message->meta_data?->filter;

        if ($filter) {
            $filter = Filter::find($filter);
        }

        $tool = $message->meta_data?->tool;

        if ($tool) {
            Log::info('[LaraChain] Orchestration Has Tool', [
                'tool' => $tool,
            ]);

            $functionDto = FunctionCallDto::from([
                'arguments' => '{}',
                'function_name' => $tool,
                'filter' => $filter,
            ]);

            $message = $this->addToolsToMessage($message, $functionDto);

            $toolClass = app()->make($tool);

            if ($toolClass->runAsBatch()) {
                Log::info('[LaraChain] - Running as long running job', [
                    'tool' => $tool,
                    'chat' => $chat->id,
                ]);
                notify_ui($chat, 'Running as long running job');

                Bus::batch([
                    new OrchestrateBatchJob($toolClass, $message),
                ])->name("Orchestrate Batch Job - {$chat->id} {$tool}")
                    ->allowFailures()
                    ->dispatch();

                return 'Running as batch';
            } else {
                $response = $toolClass->handle($message);
                $this->handleResponse($response, $chat, $message);
                $this->response = $response->content;
                $this->requiresFollowup = $response->requires_follow_up_prompt;
                $this->requiresFollowUp($message->getLatestMessages(), $chat);
                notify_ui_complete($chat);
            }

        } else {
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

                    Log::info('[LaraChain] - Running function '.$functionName);

                    $functionClass = app()->make($functionName);

                    $arguments = data_get($function, 'arguments');

                    $arguments = is_array($arguments) ? json_encode($arguments) : '';

                    /**
                     * @TODO
                     * All functions need to then just get Message
                     * by the time this refactor is done!
                     */
                    $functionDto = FunctionCallDto::from([
                        'arguments' => $arguments,
                        'function_name' => $functionName,
                        'filter' => $filter,
                    ]);

                    $message = $this->addToolsToMessage($message, $functionDto);

                    /** @var FunctionResponse $response */
                    $response = $functionClass->handle($message);

                    Log::info('[LaraChain] - Function Response', [
                        'function' => $functionName,
                    ]);

                    if ($response->save_to_message) {

                        $assistantMessage = $chat->addInput(
                            message: $response->content,
                            role: RoleEnum::Assistant,
                            show_in_thread: true,
                            meta_data: $message->meta_data,
                            tools: $message->tools);

                        if ($response->prompt) {
                            $this->savePromptHistory($message, $response->prompt);
                        }

                        if (! empty($response->documentChunks) && $assistantMessage?->id) {
                            $this->saveDocumentReference(
                                $assistantMessage,
                                $response->documentChunks
                            );
                        }
                    }

                    $this->response = $response->content;
                    $this->requiresFollowup = $response->requires_follow_up_prompt;
                }

                /**
                 * ONE MORE REFRESH
                 */
                $messagesArray = $message->getLatestMessages();

                $this->requiresFollowUp($messagesArray, $chat);

            } else {
                Log::info('[LaraChain] Orchestration No Functions Default Search And Summarize');

                return SearchAndSummarizeChatRepo::search($chat, $message);
            }
        }

        return $this->response;
    }

    protected function hasFunctions(array $functions): bool
    {
        return is_array($functions) && count($functions) > 0;
    }

    protected function handleResponse(
        FunctionResponse $response,
        Chat $chat,
        Message $message): void
    {

        if ($response->save_to_message) {
            $message = $chat->addInput(
                message: $response->content,
                role: RoleEnum::Assistant,
                show_in_thread: true,
                meta_data: $message->meta_data,
                tools: $message->tools);

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
        }
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
