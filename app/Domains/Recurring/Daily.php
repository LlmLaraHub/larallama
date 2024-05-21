<?php

namespace App\Domains\Recurring;

use App\Jobs\RunOutputJob;
use App\Jobs\RunSourceJob;
use App\Models\Output;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;

class Daily
{
    protected RecurringTypeEnum $recurringTypeEnum = RecurringTypeEnum::Daily;

    public function check()
    {

        $this->runSources();
        $this->runOutputs();
    }

    protected function runSources()
    {
        $sources = Source::query()
            ->active()
            ->where('recurring', $this->recurringTypeEnum)
            ->where(function ($query) {
                $query->whereNull('last_run')
                    ->orWhere('last_run', '<', $this->getLastRun());
            })
            ->get();
        $jobs = [];

        foreach ($sources as $source) {
            $source->updateQuietly(['last_run' => now()]);
            $jobs[] = new RunSourceJob($source->fresh());
        }

        if (! empty($jobs)) {
            Bus::batch($jobs)
                ->name($this->recurringTypeEnum->name.' Recurring Source Run')
                ->allowFailures()
                ->dispatch();
        }
    }

    protected function runOutputs()
    {
        $sources = Output::query()
            ->active()
            ->where('recurring', $this->recurringTypeEnum)
            ->where(function ($query) {
                $query->whereNull('last_run')
                    ->orWhere('last_run', '<', $this->getLastRun());
            })
            ->get();
        $jobs = [];

        foreach ($sources as $source) {
            $source->updateQuietly(['last_run' => now()]);
            $jobs[] = new RunOutputJob($source->fresh());
        }

        if (! empty($jobs)) {
            Bus::batch($jobs)
                ->name($this->recurringTypeEnum->name.' Recurring Output Run')
                ->allowFailures()
                ->dispatch();
        }
    }

    protected function getLastRun(): Carbon
    {
        return now()->subDay();
    }
}
