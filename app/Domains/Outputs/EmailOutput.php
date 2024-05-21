<?php

namespace App\Domains\Outputs;

use App\Domains\Outputs\BaseOutput;
use App\Jobs\SendOutputEmailJob;
use App\Models\Output;

class EmailOutput extends BaseOutput
{

    public function handle(Output $output): void
    {
        SendOutputEmailJob::dispatch($output);
    }
}
