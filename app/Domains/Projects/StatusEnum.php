<?php

namespace App\Domains\Projects;

use App\Helpers\EnumHelperTrait;

enum StatusEnum: string
{
    use EnumHelperTrait;

    case Draft = 'draft';
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';

}
