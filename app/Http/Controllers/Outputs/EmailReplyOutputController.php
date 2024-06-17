<?php

namespace App\Http\Controllers\Outputs;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Prompts\EmailReplyPrompt;
use App\Http\Controllers\OutputController;

class EmailReplyOutputController extends OutputController
{
    protected OutputTypeEnum $outputTypeEnum = OutputTypeEnum::EmailReplyOutput;

    protected string $edit_path = 'Outputs/EmailReplyOutput/Edit';

    protected string $show_path = 'Outputs/EmailReplyOutput/Show';

    protected string $create_path = 'Outputs/EmailReplyOutput/Create';

    protected string $info = 'Add an Email box to check for emails and then reply to them.';

    protected string $type = 'Email Reply Output';

    protected function getValidationRules(): array
    {
        return [
            'title' => 'required|string',
            'summary' => 'required|string',
            'active' => 'boolean|nullable',
            'public' => 'boolean|nullable',
            'recurring' => 'string|nullable',
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
