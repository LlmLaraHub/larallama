<?php

namespace App\Domains\Recurring;

use Carbon\Carbon;

class Hourly extends Daily
{
    protected RecurringTypeEnum $recurringTypeEnum = RecurringTypeEnum::Hourly;

    protected function getLastRun(): Carbon
    {
        return now()->subHour();
    }
}
