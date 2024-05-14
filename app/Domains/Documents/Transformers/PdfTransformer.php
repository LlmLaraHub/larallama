<?php

namespace App\Domains\Documents\Transformers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Helpers\TextChunker;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;
use Smalot\PdfParser\Parser;

class PdfTransformer
{
    protected Document $document;

    protected string $content = '';

    public function handle(Document $document): Document
    {
        $this->document = $document;

        $filePath = $this->document->pathToFile();

        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $pages = $pdf->getPages();
        $chunks = [];
        foreach ($pages as $page_number => $page) {
            try {
                $page_number = $page_number + 1;
                $pageContent = $page->getText();

                $size = config('llmdriver.chunking.default_size');
                $chunked_chunks = TextChunker::handle($pageContent, $size);
                foreach ($chunked_chunks as $chunkSection => $chunkContent) {
                    $guid = md5($chunkContent);
                    $DocumentChunk = DocumentChunk::updateOrCreate(
                        [
                            'document_id' => $this->document->id,
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
                notify_collection_ui($document->collection, CollectionStatusEnum::PROCESSING, 'Processing Document');

            } catch (\Exception $e) {
                Log::error('Error parsing PDF', ['error' => $e->getMessage()]);
            }
        }

        Bus::batch($chunks)
            ->name("Chunking Document - {$this->document->id} {$this->document->file_path}")
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

        return $this->document;
    }
}
