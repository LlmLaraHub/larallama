<?php

namespace App\Domains\Recurring;

use Carbon\Carbon;

class HalfHour extends Daily
{
    protected RecurringTypeEnum $recurringTypeEnum = RecurringTypeEnum::HalfHour;

    protected function getLastRun(): Carbon
    {
        return now()->subMinutes(30);
    }
}
