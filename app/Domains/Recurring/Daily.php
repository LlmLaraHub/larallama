<?php

namespace App\Domains\Recurring;

use App\Domains\Sources\RecurringTypeEnum;
use App\Jobs\RunSourceJob;
use App\Models\Source;
use Illuminate\Support\Facades\Bus;

class Daily
{
    public function check()
    {
        $jobs = [];

        $sources = Source::query()
            ->where('recurring', RecurringTypeEnum::Daily)
            ->where(function ($query) {
                $query->whereNull('last_run')
                    ->orWhere('last_run', '<', now()->subDay());
            })
            ->get();

        foreach ($sources as $source) {
            $source->updateQuietly(['last_run' => now()]);
            $jobs[] = new RunSourceJob($source->fresh());
        }

        if (! empty($jobs)) {
            Bus::batch($jobs)
                ->name('Daily Recurring Run')
                ->allowFailures()
                ->dispatch();
        }
    }
}
