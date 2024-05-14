<?php

namespace App\Domains\Documents\Transformers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\UnStructured\StructuredDto;
use App\Helpers\TextChunker;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpPresentation\IOFactory;

class PowerPointTransformer
{
    protected Document $document;

    public function handle(Document $document): array
    {
        $this->document = $document;

        $filePath = $this->document->pathToFile();

        $parser = IOFactory::createReader('PowerPoint2007');
        if (! $parser->canRead($filePath)) {
            throw new \Exception('Can not read the document '.$filePath);
        }

        $transformer = new ProcessPpt();
        $results = $transformer->handle($document->pathToFile());

        $chunks = [];
        while ($results->valid()) {
            /** @var StructuredDto $dto */
            $dto = $results->current();

            $content = $dto->content;
            $size = config('llmdriver.chunking.default_size');
            $chunked_chunks = TextChunker::handle($content, $size);

            foreach ($chunked_chunks as $chunkSection => $chunkContent) {
                $DocumentChunk = DocumentChunk::updateOrCreate(
                    [
                        'document_id' => $this->document->id,
                        'sort_order' => $dto->page,
                        'section_number' => $chunkSection,
                    ],
                    [
                        'guid' => $dto->guid,
                        'content' => $chunkContent,
                        'meta_data' => $dto->toArray(),
                    ]
                );

                $chunks[] = [
                    new VectorlizeDataJob($DocumentChunk),
                ];
            }

            $results->next();
        }

        notify_collection_ui($document->collection, CollectionStatusEnum::PROCESSING, 'Processing Document');

        Log::info('PowerPointTransformer:handle', ['chunks' => count($chunks)]);

        return $chunks;
    }
}
