<?php

namespace LlmLaraHub\LlmDriver\Helpers;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

trait CreateReferencesTrait
{
    protected function saveDocumentReference(
        Message $model,
        Collection $documentChunks
    ): void {
        //add each one to a batch job or do the work here.
        foreach ($documentChunks as $documentChunk) {
            $model->message_document_references()->create([
                'document_chunk_id' => $documentChunk->id,
                'distance' => $documentChunk->distance,
            ]);
        }
    }
}
