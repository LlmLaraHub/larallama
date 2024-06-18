<?php

namespace App\Http\Controllers\Outputs;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Prompts\EmailReplyPrompt;
use App\Http\Controllers\OutputController;
use App\Models\Collection;
use App\Models\Output;
use Facades\App\Domains\Outputs\EmailReplyOutput;

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

        return array_merge(parent::getValidationRules(), [
            'meta_data.signature' => ['required', 'string'],
        ]);
    }

    public function getPrompts(): array
    {
        return [
            'email_reply_prompt' => EmailReplyPrompt::prompt('[CONTEXT]', '[USER_INPUT]'),
        ];
    }

    public function updateOutput(Output $output, array $validated): void
    {
        $secrets = [
            'username' => data_get($validated, 'secrets.username', null),
            'password' => data_get($validated, 'secrets.password', null),
            'host' => data_get($validated, 'secrets.host', null),
            'port' => data_get($validated, 'secrets.port', '465'),
            'protocol' => data_get($validated, 'secrets.protocol', 'imap'),
            'encryption' => data_get($validated, 'secrets.encryption', 'ssl'),
            'delete' => data_get($validated, 'secrets.delete', false),
            'email_box' => data_get($validated, 'secrets.email_box', 'Inbox'),
        ];

        $validated['secrets'] = $secrets;
        $output->update($validated);
    }

    protected function makeOutput(array $validated, Collection $collection): void
    {

        $secrets = [
            'username' => data_get($validated, 'secrets.username', null),
            'password' => data_get($validated, 'secrets.password', null),
            'host' => data_get($validated, 'secrets.host', null),
            'port' => data_get($validated, 'secrets.port', '993'),
            'protocol' => data_get($validated, 'secrets.protocol', 'imap'),
            'encryption' => data_get($validated, 'secrets.encryption', 'ssl'),
            'delete' => data_get($validated, 'secrets.delete', false),
            'email_box' => data_get($validated, 'secrets.email_box', null),
        ];

        $validated['secrets'] = $secrets;

        $validated['collection_id'] = $collection->id;
        $validated['type'] = $this->outputTypeEnum;

        Output::create($validated);
    }

    public function check(Output $output)
    {
        EmailReplyOutput::handle($output);

        request()->session()->flash('flash.banner', 'Checking box sending replies');

        return back();
    }
}
