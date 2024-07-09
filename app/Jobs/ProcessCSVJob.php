<?php

namespace App\Jobs;

use App\Domains\Documents\TypesEnum;
use App\Models\Document;
use Facades\App\Domains\Documents\Transformers\CSVTransformer;
use Facades\App\Domains\Documents\Transformers\XlsxTransformer;
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

        if ($this->document->type === TypesEnum::Xlsx) {
            $chunks = XlsxTransformer::handle($this->document);
        } else {
            $chunks = CSVTransformer::handle($this->document);
        }

        $total = count($chunks) - 1;
        $count = 1;

        foreach ($chunks as $item => $chunk) {

            $document = Document::find($item);

            $chunkVectorized = collect($chunk)
                ->map(function ($chunk) {
                    return new VectorlizeDataJob($chunk);
                })->toArray();

            Bus::batch($chunkVectorized)
                ->finally(function (Batch $batch) use ($document) {
                    Bus::batch([
                        [
                            new SummarizeDocumentJob($document),
                            new TagDocumentJob($document),
                            new DocumentProcessingCompleteJob($document),
                        ],
                    ])->name(sprintf('Part 2 of Process for Document - %d',
                        $document->id))
                        ->allowFailures()
                        ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                        ->dispatch();
                })
                ->name(sprintf('Part 1 of Process Document # %d %d of %d',
                    $document->id, $count, $total))
                ->allowFailures()
                ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                ->dispatch();
            $count++;
        }
    }
}
