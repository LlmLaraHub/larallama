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
     * @NOTES
     * Some of the reasoning:
     * Cosine Similarity: Cosine similarity is often considered one of the most effective metrics for measuring similarity between documents, especially when dealing with high-dimensional data like text documents. It's robust to differences in document length and is effective at capturing semantic similarity.
     * Inner Product: Inner product similarity is another metric that can be effective, particularly for certain types of data. It measures the alignment between vectors, which can be useful in contexts where the direction of the vectors is important.
     * L2 (Euclidean) Distance: L2 distance is a straightforward metric that measures the straight-line distance between vectors. While it's commonly used and easy to understand, it may not always be the most effective for capturing complex relationships between documents, especially in high-dimensional spaces.
     *
     * @TODO
     * I save distance should I save cosine and inner_product
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

        $results = collect($neighborsCosine)
            ->merge($neighborsInnerProduct)
            ->merge($documentChunkResults)
            ->unique('id')
            ->take(10);

        $siblingsIncluded = collect();

        foreach ($results as $result) {
            if ($result->section_number === 0) {
                $siblingsIncluded->push($result);
            } else {
                if ($sibling = $this->getSiblingOrNot($result, $result->section_number - 1)) {
                    $siblingsIncluded->push($sibling);
                }

                $siblingsIncluded->push($result);
            }

            if ($sibling = $this->getSiblingOrNot($result, $result->section_number + 1)) {
                $siblingsIncluded->push($sibling);
            }
        }

        return $siblingsIncluded;
    }

    protected function getSiblingOrNot(DocumentChunk $result, int $sectionNumber): false|DocumentChunk
    {
        $sibling = DocumentChunk::query()
            ->where('document_id', $result->document_id)
            ->where('sort_order', $result->sort_order)
            ->where('section_number', $sectionNumber)
            ->first();

        if ($sibling?->id) {
            return $sibling;
        }

        return false;
    }
}
