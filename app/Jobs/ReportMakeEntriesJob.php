<?php

namespace App\Jobs;

use App\Models\Report;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Facades\LlmLaraHub\LlmDriver\Functions\ReportingToolMakeEntries;

class ReportMakeEntriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Report $report)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...

            return;
        }

        ReportingToolMakeEntries::handle($this->report);

    }
}
