<?php

namespace App\Jobs;

use App\Domains\Documents\TypesEnum;
use App\Models\Document;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

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

        if ($document->type === TypesEnum::Pptx) {
            Log::info('Processing PPTX Document');
            $batch = Bus::batch([
                new ParsePowerPointJob($this->document),
            ])
                ->name('Process PPTX Document - '.$document->id)
                ->finally(function (Batch $batch) use ($document) {
                    Bus::batch([
                        [
                            new SummarizeDocumentJob($document),
                            new TagDocumentJob($document),
                        ],
                    ])
                        ->name("Summarizing and Tagging Document - {$document->id}")
                        ->allowFailures()
                        ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                        ->dispatch();
                })
                ->allowFailures()
                ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                ->dispatch();

        } elseif ($document->type === TypesEnum::Txt) {

            Log::info('Processing Text Document');
            Bus::batch([
                new ProcessTextFilesJob($this->document),
            ])
                ->name('Processing Text Document - '.$document->id)
                ->finally(function (Batch $batch) use ($document) {
                    DocumentProcessingCompleteJob::dispatch($document);
                })
                ->allowFailures()
                ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                ->dispatch();

        } elseif ($document->type === TypesEnum::PDF) {
            Log::info('Processing PDF Document');
            Bus::batch([
                new ParsePdfFileJob($this->document),
            ])
                ->name('Process PDF Document - '.$document->id)
                ->finally(function (Batch $batch) {
                    //this is triggered in the PdfTransformer class
                })
                ->allowFailures()
                ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                ->dispatch();
        }

    }
}
