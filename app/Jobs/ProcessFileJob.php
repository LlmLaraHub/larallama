<?php

namespace App\Jobs;

use App\Domains\Collections\CollectionStatusEnum;
use App\Events\CollectionStatusEvent;
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
        //This will trigger a batch job chain that will do the following for all the document pages
        //  it will save this on a Collections Watchers so we can see it in the UI.
        //1) Chunk up the data
        //2) vectorize the data
        //3) Summarize the data
        //4) Tag the data

        $batch = Bus::batch([
            new ParsePdfFileJob($this->document),
            //new VectorizeDataJob($this->document),
            //new SummarizeDataJob($this->document),
            //new TagDataJob($this->document),
            //then mark it all as done and notify the ui
        ])
            ->name('OptOutRequests')
            ->finally(function (Batch $batch) {
                /**
             * @TODO
             * make a job that does that and also
             * closes up the batch on the run watcher
             */
                CollectionStatusEvent::dispatch(
                    $this->document->collection,
                    CollectionStatusEnum::PROCESSED);
            })
            ->allowFailures()
            ->dispatch();
    }
}
