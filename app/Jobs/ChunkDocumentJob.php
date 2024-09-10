<?php

namespace App\Jobs;

use App\Helpers\TextChunker;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

/**
 * @NOTE
 * Only really good for chunking content that can fit in the Original Content
 * of a Document model.
 */
class ChunkDocumentJob implements ShouldQueue
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

        Log::info('[LaraChain] - Chunking Document', [
            'document' => $this->document->id,
        ]);

        $document = $this->document;

        $chunks = [];

        $page_number = 0;

        $pageContent = $this->document->original_content;

        $pageContent = cleanPDFText($pageContent);

        $size = config('llmdriver.chunking.default_size');

        $chunked_chunks = TextChunker::handle($pageContent, $size);

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
                    'content' => to_utf8($chunkContent),
                ]
            );

            $chunks[] = [
                new VectorlizeDataJob($DocumentChunk),
            ];
        }

        $name = sprintf('Chunking Document Type %s id %d ', $document->type->name, $document->id);

        Bus::batch($chunks)
            ->name($name)
            ->allowFailures()
            ->finally(function (Batch $batch) use ($document) {

                Bus::batch([
                    [
                        new SummarizeDocumentJob($document),
                        new TagDocumentJob($document),
                        new DocumentProcessingCompleteJob($document),
                    ],
                ])
                    ->name(sprintf('Final Document Steps Document %s id %d', $document->type->name, $document->id))
                    ->allowFailures()
                    ->dispatch();
            })
            ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
            ->dispatch();
    }
}
