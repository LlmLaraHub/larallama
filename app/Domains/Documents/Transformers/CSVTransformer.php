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
    public $keysFound = [];

    protected Document $document;

    protected TypesEnum $mimeType = TypesEnum::CSV;

    protected string $readerType = \Maatwebsite\Excel\Excel::CSV;

    public function handle(Document $document): array
    {
        $this->document = $document;

        $filePath = $this->document->pathToFile();

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

                /**
                 * @NOTE
                 * Row number is tricky
                 * going to introduce a Key to the meta_data
                 * in this case i will hard code key to see it work
                 * then it will establish a key to update
                 * BUT by saving the keys we can find any documents not updated
                 * and delete those
                 */
                if (collect($row)->has('key')) {
                    $rowNumber = collect($row)->get('key');
                }

                $this->keysFound[] = $rowNumber;

                $file_name = $this->getFileName($rowNumber, $document->file_path);

                Storage::disk('collections')
                    ->put((string) $document->collection->id.'/'.$file_name, $content);

                $documentRow = Document::updateOrCreate([
                    'collection_id' => $document->collection_id,
                    'file_path' => $file_name,
                ], [
                    'status' => StatusEnum::Pending,
                    'summary' => $content,
                    'meta_data' => $row,
                    'type' => $this->mimeType,
                    'original_content' => $content,
                    'subject' => "Key or Row $rowNumber import from ".$document->file_path,
                ]);

                $size = config('llmdriver.chunking.default_size');

                $chunked_chunks = TextChunker::handle($content, $size);

                if ($documentRow->wasRecentlyCreated || $documentRow->wasChanged([
                    'original_content',
                ])) {
                    foreach ($chunked_chunks as $chunkSection => $chunkContent) {

                        $guid = md5($chunkContent);

                        $DocumentChunk = DocumentChunk::updateOrCreate(
                            [
                                'document_id' => $documentRow->id,
                                'sort_order' => $rowNumber,
                            ],
                            [
                                'guid' => $guid,
                                'section_number' => $chunkSection,
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

        $this->cleanUpDeletedRows();

        $document->delete();

        return $chunks;
    }

    protected function cleanUpDeletedRows(): void
    {
        Document::where('collection_id', $this->document->collection_id)
            ->whereNotIn('file_path', $this->getKeysWithFileName())
            ->delete();
    }

    protected function getKeysWithFileName(): array
    {
        return collect($this->keysFound)->map(function ($rowNumber) {
            return $this->getFileName($rowNumber, $this->document->file_path);
        })->toArray();
    }

    protected function getFileName(int $rowNumber, string $filePath): string
    {
        return 'row_'.$rowNumber.'_'.str($filePath)->beforeLast('.')->toString().'.txt';
    }
}
