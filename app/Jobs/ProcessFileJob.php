<?php

namespace App\Jobs;

use App\Domains\Documents\TypesEnum;
use App\Events\DocumentParsedEvent;
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
                    DocumentParsedEvent::dispatch($document);
                })
                ->allowFailures()
                ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                ->dispatch();

        } elseif ($document->type === TypesEnum::PDF) {
            Log::info('Processing PDF Document');
            $batch = Bus::batch([
                new ParsePdfFileJob($this->document),
            ])
                ->name('Process PDF Document - '.$document->id)
                ->finally(function (Batch $batch) use ($document) {
                    DocumentParsedEvent::dispatch($document);
                })
                ->allowFailures()
                ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                ->dispatch();
        }

    }
}
