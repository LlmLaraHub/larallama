<?php

namespace App\Http\Controllers\Sources;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\Sources\SourceTypeEnum;
use App\Helpers\TextChunker;
use App\Http\Controllers\BaseSourceController;
use App\Jobs\DocumentProcessingCompleteJob;
use App\Jobs\GiveTitleToDocumentJob;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Source;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

class JsonSourceController extends BaseSourceController
{
    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::JsonSource;

    protected string $edit_path = 'Sources/JsonSource/Edit';

    protected string $show_path = 'Sources/JsonSource/Show';

    protected string $create_path = 'Sources/JsonSource/Create';

    protected string $info = 'Allows you to import a JSON object';

    protected string $type = 'Json Source';

    protected function makeSource(array $validated, Collection $collection): void
    {
        $source = Source::create([
            'title' => $validated['title'],
            'details' => $validated['details'],
            'recurring' => $validated['recurring'],
            'active' => $validated['active'],
            'collection_id' => $collection->id,
            'slug' => str(Str::random(16))->toString(),
            'type' => $this->sourceTypeEnum,
            'meta_data' => json_decode($validated['meta_data'], true, 512),
            'secrets' => [],
        ]
        );

        $this->addDocuments($source);
    }

    protected function updateSource(Source $source, array $validated): void
    {
        $originalMetaData = json_encode($source->meta_data, 128);
        $source->update([
            'title' => $validated['title'],
            'details' => $validated['details'],
            'recurring' => $validated['recurring'],
            'active' => $validated['active'],
            'slug' => str(Str::random(16))->toString(),
            'type' => $this->sourceTypeEnum,
            'meta_data' => json_decode($validated['meta_data'], true, 512),
            'secrets' => [],
        ]
        );

        if (md5($originalMetaData) !== md5($validated['meta_data'])) {
            $this->addDocuments($source);
        }
    }

    protected function addDocuments(Source $source)
    {
        foreach ($source->meta_data as $item) {
            $id = md5($item);
            $document = Document::updateOrCreate([
                'file_path' => $id,
                'collection_id' => $source->collection_id,
            ], [
                'type' => TypesEnum::JSON,
                'status_summary' => StatusEnum::Pending,
            ]);
            $jobs = [];
            $page_number = 1;
            $chunked_chunks = TextChunker::handle($item);
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
                ->name("Chunking Document from JSON Source = $source->id")
                ->finally(function (Batch $batch) use ($document) {
                    SummarizeDocumentJob::dispatch($document);
                    TagDocumentJob::dispatch($document);
                    GiveTitleToDocumentJob::dispatch($document);
                    DocumentProcessingCompleteJob::dispatch($document);
                })
                ->allowFailures()
                ->dispatch();

        }
    }

    protected function getValidationRules(): array
    {
        return [
            'title' => 'required|string',
            'details' => 'required|string',
            'active' => ['boolean', 'required'],
            'recurring' => ['string', 'required'],
            'meta_data' => ['required', 'string'],
            'secrets' => ['nullable', 'array'],
        ];
    }
}
