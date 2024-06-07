<?php

namespace App\Http\Controllers;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Prompts\EmailPrompt;
use App\Jobs\SendOutputEmailJob;
use App\Models\Output;
use Illuminate\Support\Facades\Log;

class EmailOutputController extends OutputController
{
    protected OutputTypeEnum $outputTypeEnum = OutputTypeEnum::EmailOutput;

    protected string $edit_path = 'Outputs/EmailOutput/Edit';

    protected string $show_path = 'Outputs/EmailOutput/Show';

    protected string $create_path = 'Outputs/EmailOutput/Create';

    public function send(Output $output)
    {
        Log::info('Sending message', $output->toArray());

        try {
            $to = $output->fromMetaData('to');

            if (empty($to)) {
                request()->session()->flash('Oops no one in the To area');

                return back();
            }

            SendOutputEmailJob::dispatch(output: $output, testRun: true);

            request()->session()->flash('Sending email now');

            return back();
        } catch (\Exception $e) {
            Log::error('Exception with error');
            Log::error($e->getMessage());

            request()->session()->flash('Issue sending email');

            return back();
        }

    }

    public function getPrompts(): array
    {
        return [
            'transform_github_json' => EmailPrompt::prompt('[CONTEXT]'),
        ];
    }
}
