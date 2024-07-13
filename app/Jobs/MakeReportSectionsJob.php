<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Report;
use Facades\LlmLaraHub\LlmDriver\Functions\ReportingToolMakeSections;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeReportSectionsJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $prompts,
        public Report $report,
        public Document $document
    ) {
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

        ReportingToolMakeSections::handle($this->prompts, $this->report, $this->document);

    }
}
