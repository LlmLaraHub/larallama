<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class ProcessFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $document = $this->document;

        $batch = Bus::batch([
            new ParsePdfFileJob($this->document),
        ])
            ->name('Process PDF Document - '.$document->id)
            ->finally(function (Batch $batch) {
            })
            ->allowFailures()
            ->dispatch();
    }
}
