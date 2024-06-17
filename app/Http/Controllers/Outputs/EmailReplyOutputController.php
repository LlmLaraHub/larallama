<?php

namespace App\Http\Controllers\Outputs;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Prompts\ChatBotPrompt;
use App\Domains\Prompts\EmailReplyPrompt;
use App\Domains\Prompts\PromptMerge;
use App\Domains\Prompts\SupportChatBotPrompt;
use App\Http\Controllers\OutputController;
use App\Models\Collection;
use App\Models\Output;
use App\Models\Source;
use Facades\LlmLaraHub\LlmDriver\NonFunctionSearchOrSummarize;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Responses\NonFunctionResponseDto;

class EmailReplyOutputController extends OutputController
{
    protected OutputTypeEnum $outputTypeEnum = OutputTypeEnum::ApiOutput;

    protected string $edit_path = 'Outputs/EmailReplyOutput/Edit';

    protected string $show_path = 'Outputs/EmailReplyOutput/Show';

    protected string $create_path = 'Outputs/EmailReplyOutput/Create';


    protected function getValidationRules(): array
    {
        return [
            'title' => 'required|string',
            'details' => 'required|string',
            'active' => ['boolean', 'required'],
            'recurring' => ['string', 'required'],
            'meta_data.signature' => ['required', 'string'],
        ];
    }

    public function getPrompts(): array
    {
        return [
            'email_reply_prompt' => EmailReplyPrompt::prompt('[CONTEXT]', '[USER_INPUT]'),
        ];
    }


}
