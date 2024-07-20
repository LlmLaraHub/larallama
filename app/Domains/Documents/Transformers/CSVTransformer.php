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
                 * @TODO
                 * We have the text but what does the user want to do with the text
                 * 1) Here we should have a source with a chat_id or make the chat id
                 * 2) this becomes a message (that is a lot of them?)
                 * 3) then the LLM gets the sources prompt and sees what the user wants to do with the data.
                 * 4) Example "Take these dates and save them to the document start and end data then save the content to the document as an event"
                 *    Then tag the document by the Region seen in the data (or hard coded in the prompt)
                 * 5) The Prompt using OrchestrateV2 should take the Chat and Message and start building out the results
                 *    this will update or create a document
                 *    this will find start_date and end_date new fields in a document
                 *    this will tag the document Region: Foobar
                 *    NOTE: We already have date_range so bummer it is created_at
                 */
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
