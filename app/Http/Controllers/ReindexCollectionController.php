<?php

namespace App\Http\Controllers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Documents\StatusEnum;
use App\Events\CollectionStatusEvent;
use App\Jobs\DocumentProcessingCompleteJob;
use App\Jobs\SummarizeDataJob;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Collection;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

class ReindexCollectionController extends Controller
{
    public function reindex(Collection $collection)
    {
        foreach ($collection->documents as $document) {
            $chunks = [];

            foreach ($document->document_chunks as $chunk) {
                $chunk->status_embeddings = StatusEnum::Pending;
                $chunks[] = [
                    new VectorlizeDataJob($chunk),
                    new SummarizeDataJob($chunk),
                ];

                CollectionStatusEvent::dispatch($collection, CollectionStatusEnum::PROCESSING);

            }

            Bus::batch($chunks)
                ->name("Reindexing - {$document->id}")
                ->finally(function (Batch $batch) use ($document) {
                    SummarizeDocumentJob::dispatch($document);
                    TagDocumentJob::dispatch($document);
                    DocumentProcessingCompleteJob::dispatch($document);
                })
                ->allowFailures()
                ->dispatch();
        }

    }
}
