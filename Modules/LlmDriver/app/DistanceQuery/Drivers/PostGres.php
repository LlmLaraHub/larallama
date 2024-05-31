<?php

namespace LlmLaraHub\LlmDriver\DistanceQuery\Drivers;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Filter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Distance;
use Pgvector\Laravel\Vector;

class PostGres extends Base
{
    public function cosineDistance(
        string $embeddingSize,
        int $collectionId,
        Vector $embedding,
        ?Filter $filter = null
    ): Collection {

        Log::info('[LaraChain] - PostGres Cosine Query', [
            'filter' => $filter?->toArray(),
            'embedding_size' => $embeddingSize,
        ]);

        $documentIds = Document::query()
            ->select('id')
            ->when($filter, function ($query, $filter) {
                $query->whereIn('id', $filter->documents()->pluck('id'));
            })
            ->where('documents.collection_id', $collectionId)
            ->orderBy('id')
            ->pluck('id');

        $commonQuery = DocumentChunk::query()
            ->orderBy('sort_order')
            ->orderBy('section_number')
            ->whereIn('document_id', $documentIds);

        $neighborsCosine = $commonQuery
            ->nearestNeighbors($embeddingSize, $embedding, Distance::Cosine)
            ->get();

        Log::info('[LaraChain] Nearest Neighbor Cosine IDs', [
            'count' => $neighborsCosine->count(),
            'ids' => $neighborsCosine->pluck('id')->toArray(),
        ]);

        $results = collect($neighborsCosine)
            ->unique('id')
            ->take(8);

        $siblingsIncluded = $this->getSiblings($results);

        return $siblingsIncluded;
    }
}
