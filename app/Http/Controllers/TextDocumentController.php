<?php

namespace App\Http\Controllers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Helpers\TextChunker;
use App\Jobs\DocumentProcessingCompleteJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

class TextDocumentController extends Controller
{
    public function store(Collection $collection, Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'name' => 'required|string',
        ]);

        $document = Document::create([
            'file_path' => $validated['name'],
            'collection_id' => $collection->id,
            'type' => TypesEnum::Txt,
            'subject' => str($validated['content'])->limit(256)->toString(),
            'summary' => $validated['content'],
            'status_summary' => StatusEnum::Pending,
        ]);

        $jobs = [];
        $page_number = 1;
        $chunked_chunks = TextChunker::handle($validated['content']);
        foreach ($chunked_chunks as $chunkSection => $chunkContent) {

            try {
                $guid = md5($chunkContent);
                $DocumentChunk = DocumentChunk::updateOrCreate(
                    [
                        'document_id' => $document->id,
                        'sort_order' => $page_number,
                        'section_number' => $chunkSection,
                    ],
                    [
                        'guid' => $guid,
                        'content' => $chunkContent,
                        'sort_order' => $page_number,
                    ]
                );

                $jobs[] = [
                    new VectorlizeDataJob($DocumentChunk),
                ];

                notify_collection_ui($document->collection, CollectionStatusEnum::PROCESSING, 'Document Created working on pages');
            } catch (\Exception $e) {
                Log::error('Error parsing PDF', ['error' => $e->getMessage()]);
            }

        }

        Bus::batch($jobs)
            ->name("Chunking Document - $document->file_path")
            ->finally(function (Batch $batch) use ($document) {
                TagDocumentJob::dispatch($document);
                DocumentProcessingCompleteJob::dispatch($document);
            })
            ->allowFailures()
            ->dispatch();

        $request->session()->flash('flash.banner', 'Document created successfully!');

        return back();
    }
}
