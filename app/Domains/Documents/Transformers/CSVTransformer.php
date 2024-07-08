<?php

namespace App\Domains\Documents\Transformers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Helpers\TextChunker;
use App\Imports\DocumentsImport;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CSVTransformer
{
    protected Document $document;

    protected TypesEnum $mimeType = TypesEnum::CSV;

    public function handle(Document $document): array
    {
        $this->document = $document;

        $filePath = $this->document->pathToFile();

        //$filePath = null, string $disk = null, string $readerType = null
        $collection = (new DocumentsImport())
            ->toCollection($filePath, null, \Maatwebsite\Excel\Excel::CSV);

        $rows = $collection->first();

        $chunks = [];

        /**
         * Going to turn into a document then chunks
         */
        foreach ($rows as $rowNumber => $row) {
            $file_name = 'row_'.$rowNumber.'_'.$document->file_path;

            $encoded = json_encode($row);

            Storage::disk('collections')
                ->put((string) $document->collection->id.'/'.$file_name, $encoded);

            $documentRow = Document::updateOrCreate([
                'collection_id' => $document->collection_id,
                'file_path' => $file_name,
                'type' => $this->mimeType,
            ], [
                'status' => StatusEnum::Pending,
                'summary' => $encoded,
                'meta_data' => $row,
                'original_content' => $encoded,
                'subject' => "Row $rowNumber import from ".$document->file_path,
            ]);

            $size = config('llmdriver.chunking.default_size');

            $chunked_chunks = TextChunker::handle($encoded, $size);

            if ($documentRow->wasRecentlyCreated) {
                foreach ($chunked_chunks as $chunkSection => $chunkContent) {

                    $guid = md5($chunkContent);

                    $DocumentChunk = DocumentChunk::updateOrCreate(
                        [
                            'document_id' => $documentRow->id,
                            'sort_order' => $rowNumber,
                            'section_number' => $chunkSection,
                        ],
                        [
                            'guid' => $guid,
                            'content' => $chunkContent,
                            'meta_data' => $row,
                            'original_content' => $encoded,
                        ]
                    );

                    $chunks[] = $DocumentChunk;
                }
            } else {
                $documentRow->updateQuietly([
                    'status' => StatusEnum::Complete,
                ]);
            }

        }

        notify_collection_ui($document->collection, CollectionStatusEnum::PROCESSING, 'Processing Documents');

        Log::info('CSVTransformer:handle', ['chunks' => count($chunks)]);

        $document->delete();

        return $chunks;
    }
}
