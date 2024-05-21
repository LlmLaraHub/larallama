<?php

namespace App\Domains\Recurring;

use App\Domains\Sources\RecurringTypeEnum;
use App\Jobs\RunSourceJob;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;

class Hourly extends Daily
{
    protected RecurringTypeEnum $recurringTypeEnum = RecurringTypeEnum::Hourly;



    protected function getLastRun() : Carbon {
        return now()->subHour();
    }
}
