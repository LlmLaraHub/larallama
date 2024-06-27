<?php

namespace App\Domains\Documents\Transformers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Helpers\TextChunker;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory;

class DocXTransformer
{
    protected Document $document;

    public function handle(Document $document): array
    {
        $this->document = $document;

        $filePath = $this->document->pathToFile();

        $parser = IOFactory::createReader('Word2007');

        if(!File::exists($filePath)) {
            throw new \Exception('Can not fine the document '.$filePath);
        }

        if (! $parser->canRead($filePath)) {
            throw new \Exception('Can not read the document '.$filePath);
        }

        $document = $parser->load($filePath);

        $sections = $document->getSections();

        $content = [];
        $chunks = [];

        foreach ($sections as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                /**
                 * @TODO
                 * what type of section
                 * text is easy
                 * what about images
                 * what about tables
                 * what about lists
                 */
                $content[] = str($element->getText())->trim()->toString();

            }
        }

        $content_flattened = implode(' ', $content);
        $size = config('llmdriver.chunking.default_size');
        $chunked_chunks = TextChunker::handle($content_flattened, $size);
        $page = 1;

        foreach ($chunked_chunks as $chunkSection => $chunkContent) {
            $DocumentChunk = DocumentChunk::updateOrCreate(
                [
                    'document_id' => $this->document->id,
                    'sort_order' => $page,
                    'section_number' => $chunkSection,
                ],
                [
                    'guid' => md5($chunkContent),
                    'content' => $chunkContent,
                    'meta_data' => [$chunkContent],
                ]
            );

            $chunks[] = [
                new VectorlizeDataJob($DocumentChunk),
            ];
        }


        notify_collection_ui($this->document->collection,
            CollectionStatusEnum::PROCESSING, 'Processing Document');

        Log::info('DocXTransformer:handle', ['chunks' => count($chunks)]);

        return $chunks;
    }
}
