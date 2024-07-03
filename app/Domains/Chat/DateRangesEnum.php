<?php

namespace App\Domains\Chat;

use App\Helpers\EnumHelperTrait;

enum DateRangesEnum : string
{

    use EnumHelperTrait;

    case Today = 'today';
    case Yesterday = 'yesterday';
    case ThisWeek = 'this_week';
    case LastWeek = 'last_week';
    case ThisMonth = 'this_month';
    case LastMonth = 'last_month';


}
