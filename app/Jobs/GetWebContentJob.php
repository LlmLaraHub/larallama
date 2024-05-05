<?php

namespace App\Jobs;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Source;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
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

        Log::info("[Larachain] GetWebContentJob - {$this->source->title} - URL: {$this->webResponseDto->url}");
        $html = GetPage::make($this->source->collection)->handle($this->webResponseDto->url);

        $results = GetPage::make($this->source->collection)->parseHtml($html);

        $page_number = 1;
        $guid = md5($this->webResponseDto->url);

        /**
         * Document can reference a source
         */
        $document = Document::updateOrCreate(
            [
                'source_id' => $this->source->id,
                'type' => TypesEnum::HTML
            ],
            [
                'status' => StatusEnum::Pending,
                'status_summary' => StatusEnum::Pending,
                'file_path' => $this->webResponseDto->url,
                'collection_id' => $this->source->collection_id,
                'meta_data' => $this->webResponseDto->toArray(),
            ]
        );


        /**
         * @TODO
         * I need to use the token_counter and the break up the string till 
         * all of it fits into that limit
         * In the meantime just doing below
         */

        $maxTokenSize = LlmDriverFacade::driver($this->source->getDriver())
            ->getMaxTokenSize($this->source->getDriver());

        $page_number = 1;
    
        $chunks = chunk_string($results, $maxTokenSize);

        foreach ($chunks as $chunk) {
            $DocumentChunk = DocumentChunk::updateOrCreate(
                [
                    'guid' => $guid . '-' . $page_number,
                    'document_id' => $document->id,
                ],
                [
                    'content' => $chunk,
                    'sort_order' => $page_number,
                ]
            );

            Log::info('[Larachain] adding to new batch');

            $this->batch()->add([
                new VectorlizeDataJob($DocumentChunk),
                new SummarizeDataJob($DocumentChunk),
            ]);

            $page_number++;
        }
        return;
    }
}
