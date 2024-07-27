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
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

class ProcessFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $jobs = [];

    public array $finally = [];

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
        /**
         * @TODO
         * I need a document->reset() type method
         * to better deal with this
         * Then it uses the source type to just do that one document
         */
        $document = $this->document;

        $options = [
            TypesEnum::CSV->value => [
                'jobs' => [
                    ProcessCSVJob::class,
                ],
                'finally' => [], //going to make new docs from each row
            ],
            TypesEnum::Xlsx->value => [
                'jobs' => [
                    ProcessCSVJob::class,
                ],
                'finally' => [], //going to make new docs from each row
            ],
            TypesEnum::Pptx->value => [
                'jobs' => [
                    ParsePowerPointJob::class,
                ],
                'finally' => [
                    SummarizeDocumentJob::class,
                    TagDocumentJob::class,
                    DocumentProcessingCompleteJob::class,
                ],
            ],
            TypesEnum::Email->value => [
                'jobs' => [
                    EmailTransformerJob::class,
                ],
                'finally' => [
                    SummarizeDocumentJob::class,
                    TagDocumentJob::class,
                    DocumentProcessingCompleteJob::class,
                ],
            ],
            TypesEnum::Docx->value => [
                'jobs' => [
                    ParseDocxJob::class,
                ],
                'finally' => [
                    SummarizeDocumentJob::class,
                    TagDocumentJob::class,
                    DocumentProcessingCompleteJob::class,
                ],
            ],
            TypesEnum::Txt->value => [
                'jobs' => [
                    ProcessTextFilesJob::class,
                ],
                'finally' => [],
            ],
            TypesEnum::HTML->value => [
                'jobs' => [
                    WebPageDocumentJob::class,
                ],
                'finally' => [],
            ],
            TypesEnum::PDF->value => [
                'jobs' => [
                    ParsePdfFileJob::class,
                ],
                'finally' => [],
            ],
        ];

        $option = $options[$document->type->value];

        Bus::batch(collect($option['jobs'])->map(function ($job) use ($document) {
            return new $job($document);
        })->toArray())
            ->name(sprintf('Process %s Document - %d', $document->type->value, $document->id))
            ->finally(function (Batch $batch) use ($document, $option) {
                Bus::batch(collect($option['finally'])->map(function ($job) use ($document) {
                    return new $job($document);
                })->toArray())
                    ->name(sprintf('Part 2 of Process for %s Document - %d', $document->type->value, $document->id))
                    ->allowFailures()
                    ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                    ->dispatch();
            })
            ->allowFailures()
            ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
            ->dispatch();

    }
}
