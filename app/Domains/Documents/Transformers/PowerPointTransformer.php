<?php

namespace App\Domains\Documents\Transformers;

use App\Domains\UnStructured\StructuredDto;
use App\Jobs\SummarizeDataJob;
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
            $DocumentChunk = DocumentChunk::updateOrCreate(
                [
                    'guid' => $dto->guid,
                    'document_id' => $this->document->id,
                ],
                [
                    'content' => $dto->content,
                    'sort_order' => $dto->page,
                    'meta_data' => $dto->toArray(),
                ]
            );

            $chunks[] = [
                new VectorlizeDataJob($DocumentChunk),
                new SummarizeDataJob($DocumentChunk),
            ];

            $results->next();
        }

        Log::info('PowerPointTransformer:handle', ['chunks' => count($chunks)]);

        return $chunks;
    }
}
