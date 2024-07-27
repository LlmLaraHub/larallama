<?php

namespace App\Http\Controllers;

use App\Domains\Messages\RoleEnum;
use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Prompts\ChatBotPrompt;
use App\Domains\Prompts\SupportChatBotPrompt;
use App\Models\Chat;
use App\Models\Output;
use Facades\App\Domains\Tokenizer\Templatizer;
use Facades\LlmLaraHub\LlmDriver\NonFunctionSearchOrSummarize;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Responses\NonFunctionResponseDto;

class ApiOutputController extends OutputController
{
    protected OutputTypeEnum $outputTypeEnum = OutputTypeEnum::ApiOutput;

    protected string $edit_path = 'Outputs/ApiOutput/Edit';

    protected string $show_path = 'Outputs/ApiOutput/Show';

    protected string $create_path = 'Outputs/ApiOutput/Create';

    public function api(Output $output)
    {
        $validate = request()->validate([
            'messages' => ['array', 'required'],
        ]);

        Log::info('[LaraChain] - Message Coming in', [
            'message' => $validate['messages']]
        );

        $input = data_get($validate, 'messages.0.content');

        $prompt = $output->summary;

        $prompt = Templatizer::appendContext(true)
            ->handle($prompt, $input);

        $chat = Chat::firstOrCreateUsingOutput($output);

        $message = $chat->addInput(
            message: $input,
            role: RoleEnum::User,
            show_in_thread: true,
        );

        /** @var NonFunctionResponseDto $results */
        $results = NonFunctionSearchOrSummarize::setPrompt($prompt)
            ->handle($message);

        return response()->json([
            'choices' => [
                [
                    'message' => [
                        'content' => $results->response,
                    ],
                ],
            ],
        ]);
    }

    public function getPrompts(): array
    {
        return [
            'chat_bot_prompt' => ChatBotPrompt::prompt('[CONTEXT]', '[USER_INPUT]'),
            'support_chat_bot_prompt' => SupportChatBotPrompt::prompt('[CONTEXT]', '[USER_INPUT]'),
        ];
    }
}
