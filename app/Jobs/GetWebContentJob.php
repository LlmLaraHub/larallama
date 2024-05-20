<?php

namespace App\Jobs;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Helpers\TextChunker;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Source;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class GetWebContentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Source $source,
        public WebResponseDto $webResponseDto
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

        /**
         * Document can reference a source
         */
        $document = Document::updateOrCreate(
            [
                'source_id' => $this->source->id,
                'type' => TypesEnum::HTML,
                'subject' => $this->webResponseDto->title,
                'link' => $this->webResponseDto->url,
                'collection_id' => $this->source->collection_id,
            ],
            [
                'status' => StatusEnum::Pending,
                'file_path' => $this->webResponseDto->url,
                'status_summary' => StatusEnum::Pending,
                'meta_data' => $this->webResponseDto->toArray(),
            ]
        );

        Log::info("[LaraChain] GetWebContentJob - {$this->source->title} - URL: {$this->webResponseDto->url}");
        $html = GetPage::make($this->source->collection)->handle($this->webResponseDto->url);

        /**
         * @NOTE
         * making them PDF for now
         * I ran into "noise" issues
         * of just a lot of script tags and stuff
         * there is some code in the getPage for html
         * that might be worth it later
         */
        if (! Feature::active('html_to_text')) {
            $document->update([
                'type' => TypesEnum::PDF,
                'file_path' => md5($this->webResponseDto->url).'.pdf',
            ]);

            Bus::batch([
                new ParsePdfFileJob($document),
            ])
                ->name('Process PDF Document - '.$document->id)
                ->finally(function (Batch $batch) {
                    //this is triggered in the PdfTransformer class
                })
                ->allowFailures()
                ->onQueue(LlmDriverFacade::driver($this->source->getDriver())->onQueue())
                ->dispatch();
        } else {
            $results = GetPage::make($this->source->collection)->parseHtml($html);

            $page_number = 1;
            $guid = md5($this->webResponseDto->url);

            /**
             * @TODO
             * I need to use the token_counter and the break up the string till
             * all of it fits into that limit
             * In the meantime just doing below
             */
            $maxTokenSize = LlmDriverFacade::driver($this->source->getDriver())
                ->getMaxTokenSize($this->source->getDriver());

            $page_number = 1;

            $chunked_chunks = TextChunker::handle($results);

            foreach ($chunked_chunks as $chunkSection => $chunkContent) {

                $guid = md5($chunkContent);

                $DocumentChunk = DocumentChunk::updateOrCreate(
                    [
                        'document_id' => $document->id,
                        'sort_order' => $page_number,
                        'section_number' => $chunkSection,
                    ],
                    [
                        'guid' => $guid,
                        'content' => $chunkContent,
                    ]
                );

                Log::info('[LaraChain] adding to new batch');

                $this->batch()->add([
                    new VectorlizeDataJob($DocumentChunk),
                ]);

                $page_number++;
            }
        }

    }
}
