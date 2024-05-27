<?php

namespace App\Domains\Transformers;

use App\Domains\Sources\BaseSource;
use App\Domains\UnStructured\StructuredTypeEnum;
use App\Helpers\TextChunker;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Log;

abstract class BaseTransformer
{
    public TypeEnum $type = TypeEnum::GenericTransformer;

    public BaseSource $baseSource;

    public array $compatible_sources = [];

    public array $chunks = [];

    public ?Document $document = null;

    abstract public function transform(BaseSource $baseSource): BaseSource;

    protected function chunkContent(string $content): array
    {
        $size = config('llmdriver.chunking.default_size');

        return TextChunker::handle($content, $size);
    }

    public function supported(BaseSource $baseSource): bool
    {
        if (! in_array($baseSource->sourceTypeEnum, $this->compatible_sources)) {
            Log::info(sprintf(
                '[LaraChain] - This Transformer %s does not work with this source type %s',
                $this->type->name,
                $baseSource->sourceTypeEnum->name
            ));

            return false;
        }

        return true;
    }

    protected function documentChunk(
        Document $document,
        string $content,
        int $sort_order,
        int $section_number,
        StructuredTypeEnum $typeEnum = StructuredTypeEnum::Raw
    ): DocumentChunk {
        return DocumentChunk::updateOrCreate(
            [
                'document_id' => $document->id,
                'sort_order' => $sort_order,
                'section_number' => $section_number,
                'guid' => md5($content),
                'type' => $typeEnum,
            ],
            [
                'content' => $content,
            ]
        );
    }
}
