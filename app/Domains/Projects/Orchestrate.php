<?php

namespace App\Domains\Projects;

use App\Domains\Messages\RoleEnum;
use App\Models\Chat;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Functions\ToolTypes;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class Orchestrate
{
    public function handle(Chat $chat, string $prompt, string $systemPrompt = ''): void
    {
        $chat->addInput(
            message: $prompt,
            systemPrompt: $systemPrompt,
        );

        $messages = $chat->getMessageThread();

        $response = LlmDriverFacade::driver($chat->getDriver())
            ->setSystemPrompt($systemPrompt)
            ->setToolType(ToolTypes::Chat)
            ->chat($messages);

        if (! empty($response->tool_calls)) {
            Log::info('Orchestration Tools Found', [
                'tool_calls' => collect($response->tool_calls)
                    ->pluck('name')->toArray(),
            ]);

            $count = 1;
            foreach ($response->tool_calls as $tool_call) {
                Log::info('[LaraChain] - Tool Call '.$count, [
                    'tool_call' => $tool_call->name,
                    'tool_count' => count($response->tool_calls),
                ]);

                $message = $chat->addInputWithTools(
                    message: sprintf('Tool %s', $tool_call->name),
                    tool_id: $tool_call->id,
                    tool_name: $tool_call->name,
                    tool_args: $tool_call->arguments,
                );

                $tool = app()->make($tool_call->name);
                $tool->handle($message, $tool_call->arguments);

                $count++;
            }

            Log::info('Tools Complete doing final chat');

            $messages = $chat->getMessageThread();

            /**
             * @NOTE
             * I have to continue to pass in tools once used above
             * Since Claude needs them.
             */
            $response = LlmDriverFacade::driver(
                $chat->getDriver()
            )
                ->setToolType(ToolTypes::Chat)
                ->setSystemPrompt($systemPrompt)
                ->chat($messages);

            $chat->addInput(
                message: $response->content,
                role: RoleEnum::Assistant,
            );

        } else {
            Log::info('[LaraChain] - No Tools found just gonna chat');
            $chat->addInput(
                message: $response->content ?? 'Calling Tools', //ollama, openai blank but claude needs this :(
                role: RoleEnum::Assistant
            );
        }
    }
}
