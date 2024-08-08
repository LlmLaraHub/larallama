<?php

namespace App\Domains\Recurring;

use App\Helpers\EnumHelperTrait;

enum RecurringTypeEnum: string
{
    use EnumHelperTrait;

    case Not = 'not';

    case Daily = 'daily';
    case Hourly = 'hourly';
    case Weekly = 'weekly';
    case HalfHour = 'half_hour';

}
