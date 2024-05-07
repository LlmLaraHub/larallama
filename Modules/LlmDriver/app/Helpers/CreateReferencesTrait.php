<?php

namespace LlmLaraHub\LlmDriver\Helpers;

use App\Models\Message;
use Illuminate\Support\Collection;

trait CreateReferencesTrait
{
    protected function saveDocumentReference(
        Message $model,
        Collection $documentChunks
    ): void {
        //put_fixture("document_chunks.json", $documentChunks->toArray());
        //add each one to a batch job or do the work here.
        foreach ($documentChunks as $documentChunk) {
            $model->message_document_references()->create([
                'document_chunk_id' => $documentChunk->id,
                'distance' => $documentChunk->neighbor_distance,
            ]);
        }
    }
}
