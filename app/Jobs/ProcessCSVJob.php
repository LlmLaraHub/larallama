<?php

namespace App\Jobs;

use App\Models\Document;
use Facades\App\Domains\Documents\Transformers\CSVTransformer;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

class ProcessCSVJob implements ShouldQueue
{
    use Batchable;
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
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...

            return;
        }

        $chunks = CSVTransformer::handle($this->document);

        foreach ($chunks as $chunk) {
            $document = $chunk->document;
            Bus::batch([
                new VectorlizeDataJob($chunk),
            ])
                ->name(sprintf('Process %s Document Chunks - %d', $document->type->value, $document->id))
                ->finally(function (Batch $batch) use ($document) {
                    Bus::batch([
                        new SummarizeDocumentJob($document),
                        new TagDocumentJob($document),
                        new DocumentProcessingCompleteJob($document),
                    ])->name(sprintf('Part 2 of Process for %s Document - %d',
                        $document->type->value, $document->id))
                        ->allowFailures()
                        ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                        ->dispatch();
                })
                ->allowFailures()
                ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                ->dispatch();
        }
    }
}
