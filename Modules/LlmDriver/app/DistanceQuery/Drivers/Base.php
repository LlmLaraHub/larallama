<?php

namespace LlmLaraHub\LlmDriver\DistanceQuery\Drivers;

use App\Models\DocumentChunk;
use App\Models\Filter;
use Illuminate\Support\Collection;
use Pgvector\Laravel\Vector;

abstract class Base
{
    abstract public function cosineDistance(
        string $embeddingSize,
        int $collectionId,
        Vector $embedding,
        ?Filter $filter = null
    ): Collection;

    protected function getSiblings(Collection $results): Collection
    {
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
