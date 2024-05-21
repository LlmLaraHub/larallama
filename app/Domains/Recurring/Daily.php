<?php

namespace App\Domains\Recurring;

use App\Domains\Sources\RecurringTypeEnum;
use App\Jobs\RunSourceJob;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;

class Daily
{
    protected RecurringTypeEnum $recurringTypeEnum = RecurringTypeEnum::Daily;

    public function check()
    {
        $jobs = [];

        $sources = Source::query()
            ->where('recurring', $this->recurringTypeEnum)
            ->where(function ($query) {
                $query->whereNull('last_run')
                    ->orWhere('last_run', '<', $this->getLastRun());
            })
            ->get();

        foreach ($sources as $source) {
            $source->updateQuietly(['last_run' => now()]);
            $jobs[] = new RunSourceJob($source->fresh());
        }

        if (! empty($jobs)) {
            Bus::batch($jobs)
                ->name($this->recurringTypeEnum->name.' Recurring Run')
                ->allowFailures()
                ->dispatch();
        }
    }

    protected function getLastRun(): Carbon
    {
        return now()->subDay();
    }
}
