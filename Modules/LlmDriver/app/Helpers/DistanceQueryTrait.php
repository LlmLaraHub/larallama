<?php

namespace LlmLaraHub\LlmDriver\Helpers;

use App\Models\DocumentChunk;
use Illuminate\Database\Eloquent\Collection;
use Pgvector\Laravel\Vector;

trait DistanceQueryTrait
{
    /**
     * @TODO
     * Track the document page for referehce
     *
     * @see https://github.com/orgs/LlmLaraHub/projects/1?pane=issue&itemId=60394288
     */
    protected function distance(
        string $embeddingSize,
        int $collectionId,
        Vector $embedding
    ): Collection {

        $documentChunkResults = DocumentChunk::query()
            ->join('documents', 'documents.id', '=', 'document_chunks.document_id')
            ->selectRaw(
                "document_chunks.{$embeddingSize} <-> ? as distance, document_chunks.content as content, document_chunks.{$embeddingSize} as embedding, document_chunks.id as id, document_chunks.summary as summary, document_chunks.document_id as document_id",
                [$embedding]
            )
            /** @phpstan-ignore-next-line */
            ->where('documents.collection_id', $collectionId)
            ->limit(10)
            ->orderByRaw('distance')
            ->get();

        return $documentChunkResults;
    }
}
