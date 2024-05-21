<?php

namespace App\Domains\Outputs;

use App\Models\Output;

abstract class BaseOutput
{

    abstract public function handle(Output $output) : void;
}
