<?php

namespace App\Domains\Recurring;

use App\Domains\Sources\RecurringTypeEnum;
use App\Jobs\RunSourceJob;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;

class HalfHour extends Daily
{
    protected RecurringTypeEnum $recurringTypeEnum = RecurringTypeEnum::HalfHour;



    protected function getLastRun() : Carbon {
        return now()->subMinutes(30);
    }
}
