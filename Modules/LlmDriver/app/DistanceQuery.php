<?php

namespace LlmLaraHub\LlmDriver;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Distance;
use Pgvector\Laravel\Vector;

class DistanceQuery
{
    protected int $distanceThreshold = 0;

    /**
     * @TODO
     * Track the document page for referehce
     *
     * @see https://github.com/orgs/LlmLaraHub/projects/1?pane=issue&itemId=60394288
     */
    public function distance(
        string $embeddingSize,
        int $collectionId,
        Vector $embedding
    ): Collection {
        $documentIds = Document::query()
            ->select('id')
            ->where('documents.collection_id', $collectionId)
            ->pluck('id');

        $commonQuery = DocumentChunk::query()
            ->whereIn('document_id', $documentIds);

        // Find nearest neighbors using L2 distance
        $documentChunkResults = $commonQuery
            ->nearestNeighbors($embeddingSize, $embedding, Distance::L2)
            ->take(5)
            ->get();

        // Get IDs of the nearest neighbors found 5
        $nearestNeighborIds = $documentChunkResults->pluck('id')->toArray();
        Log::info('[LaraChain] Nearest Neighbor IDs', [
            'count' => count($nearestNeighborIds),
            'ids' => $nearestNeighborIds,
        ]);
        // Find nearest neighbors using InnerProduct distance
        $neighborsInnerProduct = $commonQuery
            ->whereNotIn('document_chunks.id', $nearestNeighborIds)
            ->nearestNeighbors($embeddingSize, $embedding, Distance::InnerProduct)
            ->get();

        // Find nearest neighbors using Cosine distance found 0
        $neighborsInnerProductIds = $neighborsInnerProduct->pluck('id')->toArray();

        Log::info('[LaraChain] Nearest Neighbor Inner Product IDs', [
            'count' => count($neighborsInnerProductIds),
            'ids' => $neighborsInnerProductIds,
        ]);

        $neighborsCosine = $commonQuery
            ->whereNotIn('id', $nearestNeighborIds)
            ->when(! empty($neighborsInnerProductIds), function ($query) use ($neighborsInnerProductIds) {
                return $query->whereNotIn('id', $neighborsInnerProductIds);
            })
            ->nearestNeighbors($embeddingSize, $embedding, Distance::Cosine)
            ->get();

        Log::info('[LaraChain] Nearest Neighbor Cosine IDs', [
            'count' => $neighborsCosine->count(),
            'ids' => $neighborsCosine->pluck('id')->toArray(),
        ]);

        $results = collect($documentChunkResults)->merge($neighborsCosine)->unique('id')->take(5);

        return $results;
    }
}
