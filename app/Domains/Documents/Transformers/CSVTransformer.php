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

    protected string $readerType = \Maatwebsite\Excel\Excel::CSV;

    public function handle(Document $document): array
    {
        $this->document = $document;

        $filePath = $this->document->pathToFile();

        //$filePath = null, string $disk = null, string $readerType = null
        $collection = (new DocumentsImport())
            ->toCollection($filePath, null, $this->readerType);

        $rows = $collection->first();

        $chunks = [];

        /**
         * Going to turn into a document then chunks
         */
        foreach ($rows as $rowNumber => $row) {
            try {
                $content = collect($row)
                    ->filter(function ($item) {
                        return $item !== '';
                    })
                    ->transform(function ($item, $key) {
                        return remove_ascii($key.': '.$item);
                    })->implode("\n");

                $file_name = 'row_'.$rowNumber.'_'.str($document->file_path)->beforeLast('.')->toString().'.txt';

                Storage::disk('collections')
                    ->put((string) $document->collection->id.'/'.$file_name, $content);

                $documentRow = Document::updateOrCreate([
                    'collection_id' => $document->collection_id,
                    'file_path' => $file_name,
                    'type' => $this->mimeType,
                ], [
                    'status' => StatusEnum::Pending,
                    'summary' => $content,
                    'meta_data' => $row,
                    'original_content' => $content,
                    'subject' => "Row $rowNumber import from ".$document->file_path,
                ]);

                $size = config('llmdriver.chunking.default_size');

                $chunked_chunks = TextChunker::handle($content, $size);

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
                                'original_content' => $content,
                            ]
                        );

                        $chunks[$documentRow->id][] = $DocumentChunk;
                    }
                } else {
                    $documentRow->updateQuietly([
                        'status' => StatusEnum::Complete,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error processing Row', [
                    'error' => $e->getMessage(),
                    'row_number' => $rowNumber,
                ]);
            }

        }

        notify_collection_ui($document->collection, CollectionStatusEnum::PROCESSING, 'Processing Documents');

        Log::info($this->mimeType->name.':Transformer:handle', ['chunks' => count($chunks)]);

        $document->delete();

        return $chunks;
    }
}
