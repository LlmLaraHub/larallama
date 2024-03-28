<?php

namespace App\Domains\Documents\Transformers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Events\CollectionStatusEvent;
use App\Jobs\SummarizeDataJob;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Smalot\PdfParser\Parser;

class PdfTransformer
{
    protected Document $document;

    public function handle(Document $document): Document
    {
        $this->document = $document;

        $filePath = $this->document->pathToFile();

        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $pages = $pdf->getPages();
        $chunks = [];
        foreach ($pages as $page_number => $page) {
            $page_number = $page_number + 1;
            $pageContent = $page->getText();
            $guid = md5($pageContent);
            $DocumentChunk = DocumentChunk::updateOrCreate(
                [
                    'guid' => $guid,
                    'document_id' => $this->document->id,
                ],
                [
                    'content' => $pageContent,
                    'sort_order' => $page_number,
                ]
            );
            /**
             * Soon taggings
             * And Summary
             */
            $chunks[] = [
                new VectorlizeDataJob($DocumentChunk),
                new SummarizeDataJob($DocumentChunk),
                //Tagging
            ];

            CollectionStatusEvent::dispatch($document->collection, CollectionStatusEnum::PROCESSING);
        }

        $batch = Bus::batch($chunks)
            ->name("Chunking Document - {$this->document->id}")
            ->finally(function (Batch $batch) use ($document) {
                SummarizeDocumentJob::dispatch($document);
            })
            ->allowFailures()
            ->dispatch();

        return $this->document;
    }
}
