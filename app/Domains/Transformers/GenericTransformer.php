<?php

namespace App\Domains\Transformers;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\Transformers\BaseTransformer;
use App\Helpers\TextChunker;
use App\Jobs\SummarizeDocumentJob;
use App\Models\Document;
use App\Models\Source;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

class GenericTransformer extends BaseTransformer
{

    public TypeEnum $type = TypeEnum::GenericTransformer;

    public ?Document $document = null;


    public function transform(
            Source $source,
          string $content,
          TypesEnum $typesEnum,
          array $meta_data = [],
          ?Document $document = null) : void {
        /**
         * @NOTE
         * No need to queue this yet since
         * it is not doing any LLM work
         */
        Log::info('[LaraChain] Starting EmailTransformer ', [
            'source' => $source->id,
        ]);

        $chunks = [];

        $document = Document::updateOrCreate(
            [
                'source_id' => $source->id,
                'type' => $typesEnum,
                'subject' => $content,
                'collection_id' => $source->collection_id,
            ],
            [
                'status' => StatusEnum::Pending,
                'file_path' => null,
                'status_summary' => StatusEnum::Pending,
                'meta_data' => $meta_data,
            ]
        );

        $chunks[] = $this->documentChunk(
            $document,
            $this->mailDto->from,
            0,
            0
        );

        $chunks[] = $this->documentChunk(
            $document,
            $this->mailDto->to,
            1,
            0
        );

        $chunks[] = $this->documentChunk(
            $document,
            $this->mailDto->subject,
            2,
            0
        );

        $chunks[] = $this->documentChunk(
            $document,
            $this->mailDto->header,
            3,
            0
        );

        $size = config('llmdriver.chunking.default_size');
        $chunked_chunks = TextChunker::handle($this->mailDto->body,
            $size);

        foreach ($chunked_chunks as $chunkSection => $chunkContent) {
            $chunks[] = $this->documentChunk(
                $document,
                $chunkContent,
                4,
                $chunkSection
            );
        }

        $chunks = $this->batchJobs($chunks);

        Bus::batch($chunks)
            ->name("Chunking Email Source Document - {$document->id}")
            ->finally(function (Batch $batch) use ($document) {
                Bus::batch([
                    [
                        new SummarizeDocumentJob($document),
                        new TagDocumentJob($document),
                    ],
                ])
                    ->name("Summarizing and Tagging Email Source Document - {$document->id}")
                    ->allowFailures()
                    ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                    ->dispatch();
            })
            ->allowFailures()
            ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
            ->dispatch();

        $source->updateQuietly([
            'last_run' => now(),
        ]);

    }
}
