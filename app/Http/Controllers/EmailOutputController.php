<?php

namespace App\Http\Controllers;

use App\Domains\Outputs\OutputTypeEnum;

class EmailOutputController extends OutputController
{
    protected OutputTypeEnum $outputTypeEnum = OutputTypeEnum::EmailOutput;

    protected string $edit_path = 'Outputs/EmailOutput/Edit';

    protected string $show_path = 'Outputs/EmailOutput/Show';

    protected string $create_path = 'Outputs/EmailOutput/Create';
}
