<?php

namespace LlmLaraHub\LlmDriver\DistanceQuery\Drivers;

use App\Domains\Chat\DateRangesEnum;
use App\Domains\Chat\MetaDataDto;
use App\Models\Document;
use App\Models\DocumentChunk;
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
        ?MetaDataDto $meta_data = null
    ): Collection {

        $filter = $meta_data?->getFilter();
        $date_range = $meta_data?->date_range;

        Log::info('[LaraChain] - PostgresSQL Cosine Query', [
            'filter' => $filter?->toArray(),
            'embedding_size' => $embeddingSize,
        ]);

        $documentIds = Document::query()
            ->select('id')
            ->when($filter, function ($query, $filter) {
                $query->whereIn('id', $filter->documents()->pluck('id'));
            })
            ->when($date_range, function ($query, $date_range) {
                Log::info('Date Range', [
                    'date_range' => $date_range,
                ]);
                $results = DateRangesEnum::getStartAndEndDates($date_range);

                $query->whereBetween(
                    'created_at', [
                        $results['start'],
                        $results['end'],
                    ]);
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
