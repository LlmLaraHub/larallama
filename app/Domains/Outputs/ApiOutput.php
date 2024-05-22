<?php

namespace App\Domains\Outputs;

use App\Models\Output;

class ApiOutput extends BaseOutput
{
    public function handle(Output $output): void
    {
        // @NOTE this really does not do anything here
    }
}
