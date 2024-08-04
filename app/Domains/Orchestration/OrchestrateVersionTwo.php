<?php

namespace App\Domains\Orchestration;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Messages\RoleEnum;
use App\Jobs\ToolJob;
use App\Jobs\ToolsCompleteJob;
use App\Models\Chat;
use App\Models\Message;
use App\Models\PromptHistory;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Functions\ToolTypes;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\ToolsHelper;

/**
 * Ollama
 * @see https://github.com/ollama/ollama/blob/main/docs/api.md#chat-request-with-tools
 *
 * Claude
 * @see https://docs.anthropic.com/en/docs/build-with-claude/tool-use#example-simple-tool-definition
 *
 */
class OrchestrateVersionTwo
{
    use CreateReferencesTrait;
    use ToolsHelper;

    public function handle(
        Chat $chat,
        Message $message)
    {

        //OpenAI
        //@see https://platform.openai.com/docs/guides/function-calling
        //response_message = response.choices[0].message
        //NOTE: tool_calls is plural so we will process it that way
        //if response_message.tool_calls
        //  add the response_message to the message array for record keeping
        //  then call the tool
        //  and append to the message array
        //  foreach($tool_calls as $tool_call
        //    function_name = tool_call.function.name
        //    function_to_call = available_functions[function_name]
        //    function_args = json.loads(tool_call.function.arguments)
        //    //@NOTE this assumes immediate response
        //    function_response = function_to_call(
        //        location=function_args.get("location"),
        //        unit=function_args.get("unit")
        //    tool_call_id = tool_call.id
        //    role = "tool": //wow they have a role no one elas has
        //    name = function_name (tool_call.function.name)
        //    content = function_response.
        //Now we have all these extra messages in here that we can send again to chat.

        //Groq
        //@see https://console.groq.com/docs/tool-use
        //models recommended
        //  llama3-groq-70b-8192-tool-use-preview
        //  llama3-groq-8b-8192-tool-use-preview
        //all models that support tool use
        //    llama-3.1-405b-reasoning
        //    llama-3.1-70b-versatile
        //    llama-3.1-8b-instant
        //    llama3-70b-8192
        //    llama3-8b-8192
        //    mixtral-8x7b-32768
        //    gemma-7b-it
        //    gemma2-9b-it

        /**
         * @NOTE
         * Here the user is just forcing a chat
         * they want to continue with the thread
         */
        if($message->meta_data?->tool === 'chat') {
             Log::info('[LaraChain] - Just Chatting');
             $this->justChat($chat, $message, ToolTypes::Chat);
        } else {
            $messages = $chat->getChatResponse();

            put_fixture('orchestrate_messages_fist_send.json', $messages);

            Log::info('[LaraChain] - Looking for Tools');
            $response = LlmDriverFacade::driver($message->getDriver())
                ->setToolType(ToolTypes::ChatCompletion)
                ->chat($messages);

            if (! empty($response->tool_calls)) {
                Log::info('[LaraChain] - Tools Found');
                $this->chatWithTools($chat, $message, $response);

            } else {
                //hmm
                Log::info('[LaraChain] - No Tools found just gonna chat');
                $this->justChat($chat, $message, ToolTypes::NoFunction);
            }
        }
    }

    protected function chatWithTools(Chat $chat, Message $message, CompletionResponse $response): void
    {
        $jobs = [];
        Log::info('Orchestration V2 Tools Found', [
            'tool_calls' => collect($response->tool_calls)
                ->pluck('name')->toArray(),
        ]);

        /**
         * Might need toolid for claude :(
         */
        foreach ($response->tool_calls as $tool_call) {

            $message = $chat->addInput(
                message: $response->content ?? 'Calling Tools', //ollama, openai blank but claude needs this :(
                role: RoleEnum::Assistant,
                show_in_thread: false,
                meta_data: MetaDataDto::from([
                    'tool' => $tool_call->name,
                    'tool_id' => $tool_call->id,
                    'args' => $tool_call->arguments,
                ]),
            );

            $tool = app()->make($tool_call->name);

            $jobs[] = new ToolJob($tool, $message);
        }

        Bus::batch([
            $jobs,
        ])->name("Running tools for Chat {$message->getChat()->id} {$message->id}")
            ->finally(function (Batch $batch) use ($chat) {
                Bus::batch([
                    new ToolsCompleteJob($chat),
                ])->name("Running complete tools for Cnat {$chat->id}")
                    ->allowFailures()
                    ->finally(function (Batch $batch) use ($chat) {
                        notify_ui_complete($chat);
                    })
                    ->dispatch();
            })
            ->allowFailures()
            ->dispatch();
    }

    protected function justChat(Chat $chat, Message $message, ToolTypes $toolType): void
    {
        Log::info('[LaraChain] - Just Chatting');

        $messages = $chat->getChatResponse();

        $response = LlmDriverFacade::driver($chat->getDriver())
            ->setToolType($toolType)
            ->chat($messages);

        $assistantMessage = $chat->addInput(
            message: $response->content,
            role: RoleEnum::Assistant,
            show_in_thread: true,
            meta_data: $message->meta_data,
            tools: $message->tools,
        );

        PromptHistory::create([
            'prompt' => $message->getPrompt(),
            'chat_id' => $chat->id,
            'message_id' => $assistantMessage->id,
            'collection_id' => $chat->chatable_id,
        ]);

        notify_ui_complete($chat);
    }
}
