<?php

namespace App\Jobs;

use App\Helpers\TextChunker;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

trait WebHelperTrait
{
    protected function processDocument(Document $document): void
    {
        $jobs = [];

        $page_number = 1;

        $html = $document->original_content;

        $chunked_chunks = TextChunker::handle($html);

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
                    'content' => to_utf8($chunkContent), //still having issues.
                ]
            );

            Log::info('[LaraChain] adding to new batch');

            $jobs[] = [
                new VectorlizeDataJob($DocumentChunk),
            ];

            $page_number++;
        }

        if (! empty($jobs)) {
            Bus::batch($jobs)
                ->name('Web Pages to Documents - '.$document->subject)
                ->finally(function (Batch $batch) use ($document) {
                    Bus::batch([
                        [
                            new SummarizeDocumentJob($document),
                            new TagDocumentJob($document),
                            new DocumentProcessingCompleteJob($document),
                        ],
                    ])->name(sprintf('Part 2 of Process for Web Page Document %d',
                        $document->id))
                        ->allowFailures()
                        ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                        ->dispatch();
                })
                ->allowFailures()
                ->onQueue(LlmDriverFacade::driver($document->collection->getDriver())->onQueue())
                ->dispatch();
        }

    }
}
