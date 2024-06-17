<?php

namespace App\Http\Controllers\Outputs;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Prompts\EmailReplyPrompt;
use App\Http\Controllers\OutputController;
use App\Models\Collection;
use App\Models\Output;

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
            'secrets' => ['required', 'array'],
        ];
    }

    public function getPrompts(): array
    {
        return [
            'email_reply_prompt' => EmailReplyPrompt::prompt('[CONTEXT]', '[USER_INPUT]'),
        ];
    }

    public function updateOutput(Output $output, array $validated) : void
    {
        $secrets = [
            'username' => data_get($validated, 'secrets.username', null),
            'password' => data_get($validated, 'secrets.password', null),
            'host' => data_get($validated, 'secrets.host', null),
            'port' => data_get($validated, 'secrets.port', '465'),
            'protocol' => data_get($validated, 'secrets.protocol', 'imap'),
            'encryption' => data_get($validated, 'secrets.encryption', 'ssl'),
            'delete' => data_get($validated, 'secrets.delete', false),
            'email_box' => data_get($validated, 'secrets.email_box', null),
        ];

        $output->meta_data = $validated['meta_data'];
        $output->secrets = $secrets;

        $output->updateQuietly();

        $output->update([
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'recurring' => $validated['recurring'],
            'active' => $validated['active'],
        ]);
    }

    protected function makeOutput(array $validated, Collection $collection): void
    {
        Output::create([
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'collection_id' => $collection->id,
            'recurring' => data_get($validated, 'recurring', null),
            'active' => data_get($validated, 'active', false),
            'public' => data_get($validated, 'public', false),
            'type' => $this->outputTypeEnum,
            'meta_data' => data_get($validated, 'meta_data', []),
            'secrets' => [
                'username' => data_get($validated, 'secrets.username', null),
                'password' => data_get($validated, 'secrets.password', null),
                'host' => data_get($validated, 'secrets.host', null),
                'port' => data_get($validated, 'secrets.port', '993'),
                'protocol' => data_get($validated, 'secrets.protocol', 'imap'),
                'encryption' => data_get($validated, 'secrets.encryption', 'ssl'),
                'delete' => data_get($validated, 'secrets.delete', false),
                'email_box' => data_get($validated, 'secrets.email_box', null),
            ],
        ]);
    }
}
