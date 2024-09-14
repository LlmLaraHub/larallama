<?php

namespace App\Http\Controllers\Outputs;

use App\Domains\Outputs\OutputTypeEnum;
use App\Http\Controllers\OutputController;

class CalendarOutputController extends OutputController
{
    protected OutputTypeEnum $outputTypeEnum = OutputTypeEnum::CalendarOutput;

    protected string $edit_path = 'Outputs/Calendar/Edit';

    protected string $show_path = 'Outputs/Calendar/Show';

    protected string $create_path = 'Outputs/Calendar/Create';

    protected string $info = 'This will use the events in the collection to create a calendar page';

    protected string $type = 'Calendar Page';
}
