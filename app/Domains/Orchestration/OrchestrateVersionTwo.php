<?php

namespace App\Domains\Orchestration;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Messages\RoleEnum;
use App\Jobs\ToolJob;
use App\Jobs\ToolsCompleteJob;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class OrchestrateVersionTwo
{


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

        //Claude
        //@see https://docs.anthropic.com/en/docs/build-with-claude/tool-use#example-simple-tool-definition
        //stop_reason = response.stop_reason
        //if stop_reason == "tool_calls":
        //  get all the content items that are type "tool_use"
        //  foreach($items as $item)
        //    id = item.id
        //    function_name = item.name
        //    function_args = json.loads(item.input) //might be json already
        //    call the tool
        //    add the response to the message array for record keeping
        //    role = "user
        //    content = array of objects //wow this is an object :( I might need messages.tool = true/false
        //      type = "tool_result"
        //      tool_use_id = id
        //      content = "15 degrees"
        //Sending all of this back into chat will get a normal assistant response

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
        //
        //response_message = response.choices[0].message
        //tool_calls = response_message.tool_calls
        //if tool_calls
        //  append this tool all to messages (response_message)
        //  then call each tool
        //  foreach($tool_calls as $tool_call
        //    function_name = tool_call.function.name
        //    function_to_call = app()->make(function_name)
        //    function_args = json.loads(tool_call.function.arguments)
        //    function_response = function_to_call.handle(function_args)
        //    messages.append(
        //        tool_call_id = tool_call.id
        //        role = "tool": //wow they have a role no one elas has
        //        name = function_name (tool_call.function.name)
        //        content = function_response.
        //once down throw it back to chat

        $messages = $chat->getChatResponse();

        $response = LlmDriverFacade::driver($message->getDriver())
            ->chat($messages);

        if(!empty($response->tool_calls)) {
            $jobs = [];
            Log::info('Orchestration V2 Tools Found', [
                'tool_calls' => collect($response->tool_calls)
                    ->pluck('name')->toArray(),
            ]);

            foreach($response->tool_calls as $tool_call) {
                $message = $chat->addInput(
                    message: "Calling tools",
                    role: RoleEnum::Assistant,
                    show_in_thread: false,
                    meta_data: MetaDataDto::from([
                        'tool' => $tool_call->name,
                        'args' => $tool_call->arguments,
                    ]),
                );

                $tool = app()->make($tool_call->name);

                $jobs[] = new ToolJob($tool, $message);

            }

            Bus::batch([
                $jobs
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
        } else {
            //hmm
            Log::info('[LaraChain] - No Tools found');
        }
        //Ollama
        //@see https://github.com/ollama/ollama/blob/main/docs/api.md#chat-request-with-tools
        //response_message = response.choices[0].message
        //tool_calls = response_message.tool_calls
        //if tool_calls
        //  append this tool all to messages (response_message)
        //  then call each tool
        //  foreach($tool_calls as $tool_call
        //    function_name = tool_call.function.name
        //    function_to_call = app()->make(function_name)
        //    function_args = json.loads(tool_call.function.arguments)
        //    function_response = function_to_call.handle(function_args)
        //    messages.append(
        //        tool_call_id = tool_call.id
        //        role = "tool": //wow they have a role no one elas has
        //        name = function_name (tool_call.function.name)
        //        content = function_response.
        //once down throw it back to chat as user or assistant responses?

    }
}
