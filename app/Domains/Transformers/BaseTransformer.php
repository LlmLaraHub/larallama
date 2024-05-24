<?php

namespace App\Domains\Transformers;

use App\Helpers\TextChunker;
use App\Models\Document;
use App\Models\DocumentChunk;

abstract class BaseTransformer
{
    public TypeEnum $type = TypeEnum::GenericTransformer;

    public array $chunks = [];

    public ?Document $document = null;

    protected function chunkContent(string $content): array
    {
        $size = config('llmdriver.chunking.default_size');

        return TextChunker::handle($content, $size);
    }

    protected function documentChunk(
        Document $document,
        string $content,
        int $sort_order,
        int $section_number
    ): DocumentChunk {
        return DocumentChunk::updateOrCreate(
            [
                'document_id' => $document->id,
                'sort_order' => $sort_order,
                'section_number' => $section_number,
                'guid' => md5($content),
            ],
            [
                'content' => $content,
            ]
        );
    }
}
