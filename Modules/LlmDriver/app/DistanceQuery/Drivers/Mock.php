<?php

namespace LlmLaraHub\LlmDriver\DistanceQuery\Drivers;

use App\Domains\Chat\MetaDataDto;
use App\Models\Collection as CollectionModel;
use App\Models\DocumentChunk;
use Illuminate\Support\Collection;
use Pgvector\Laravel\Vector;

class Mock extends Base
{
    public function cosineDistance(
        string $embeddingSize,
        int $collectionId,
        Vector $embedding,
        ?MetaDataDto $meta_data = null
    ): Collection {
        $documents = CollectionModel::find($collectionId)->documents->pluck('id');

        return DocumentChunk::query()
            ->whereIn('document_id', $documents)
            ->get();
    }
}
