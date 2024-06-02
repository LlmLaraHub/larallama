<?php

namespace App\Domains\Sources;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Helpers\TextChunker;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Source;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

class WebhookSource extends BaseSource
{
    public SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::WebhookSource;

    protected array $payload = [];

    public function payload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public static string $description = 'For taking webhooks from sites like GitHub';

    /**
     * Here you can add content coming in from an API,
     * Email etc to documents. or you can React to the data coming in and for example
     * reply to it from the collection of data in the system eg
     * API hits source with article added to CMS
     * Source triggers Reaction via Output that sends the results of the LLM
     * looking in the collection of data for related content
     */
    public function handle(Source $source): void
    {
        Log::info('[LaraChain] - WebhookSource Doing something');
        //@TDODO
        //LLM can use the source promot to transfor the data.
        $encoded = json_encode($this->payload, 128);

        $document = Document::create([
            'type' => TypesEnum::WebHook,
            'status' => StatusEnum::Pending,
            'source_id' => $source->id,
            'meta_data' => $this->payload,
            'collection_id' => $source->collection_id,
            'status_summary' => StatusEnum::Pending,
            'subject' => 'Webhook '.Str::random(12),
        ]);

        $this->document = $document;

        $size = config('llmdriver.chunking.default_size');
        $chunked_chunks = TextChunker::handle($encoded, $size);
        $page_number = 1;
        $chunks = [];
        foreach ($chunked_chunks as $chunkSection => $chunkContent) {
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
                ]
            );
            $page_number++;
            $chunks[] = [
                new VectorlizeDataJob($DocumentChunk),
            ];

        }

        Bus::batch($chunks)
            ->name("Chunking Document from Webhook - {$this->document->id} {$this->document->file_path}")
            ->finally(function (Batch $batch) use ($document) {
                Bus::batch([
                    [
                        new SummarizeDocumentJob($document),
                        new TagDocumentJob($document),
                    ],
                ])
                    ->name("Summarizing and Tagging Document from Webhook - {$document->id}")
                    ->allowFailures()
                    ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                    ->dispatch();
            })
            ->allowFailures()
            ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
            ->dispatch();
    }
}
