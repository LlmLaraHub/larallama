<?php

namespace App\Domains\Sources;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\Prompts\PromptMerge;
use App\Helpers\TextChunker;
use App\Jobs\DocumentProcessingCompleteJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Source;
use Illuminate\Bus\Batch;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

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
        Log::info('[LaraChain] - WebhookSource', [
            'payload' => $this->payload,
        ]);

        $chunks = [];

        $encoded = json_encode($this->payload, 128);

        $prompt = PromptMerge::merge([
            '[CONTEXT]',
        ], [
            $encoded,
        ], $source->details);

        $results = LlmDriverFacade::driver(
            $source->getDriver()
        )->completion($prompt);

        Log::info('[LaraChain] - WebhookSource Transformation Results', [
            'results' => $results,
        ]);

        $content = $results->content;

        /**
         * @TODO
         * There is too big of an assumption here
         * The user might just make this TEXT it is their
         * prompt to do what they want
         */
        $content = str($content)
            ->replace('```json', '')
            ->replaceLast('```', '')
            ->toString();

        try {

            $results = $this->checkIfJsonOrJustText($results, $content);

            $page_number = 0;

            foreach ($results as $index => $result) {
                if (is_array($result)) {
                    $result = json_encode($result);
                }

                $id = $this->getIdFromPayload($result);

                $document = Document::updateOrCreate([
                    'type' => TypesEnum::WebHook,
                    'source_id' => $source->id,
                    'subject' => 'Webhook: '.$id,
                ], [
                    'status' => StatusEnum::Pending,
                    'meta_data' => $this->payload,
                    'collection_id' => $source->collection_id,
                    'status_summary' => StatusEnum::Pending,
                    'summary' => $result,
                ]);

                $this->document = $document;

                $page_number = $page_number + 1;
                $pageContent = $result;
                $size = config('llmdriver.chunking.default_size');
                $chunked_chunks = TextChunker::handle($pageContent, $size);

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
                            'content' => to_utf8($chunkContent),
                        ]
                    );

                    $chunks[] = [
                        new VectorlizeDataJob($DocumentChunk),
                    ];
                }

                Bus::batch($chunks)
                    ->name("Chunking Document from Webhook - {$this->document->id} {$this->document->file_path}")
                    ->allowFailures()
                    ->finally(function (Batch $batch) use ($document) {
                        DocumentProcessingCompleteJob::dispatch($document);
                    })
                    ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                    ->dispatch();

            }
        } catch (\Exception $e) {
            Log::error('[LaraChain] - Error running WebhookSource Job Level', [
                'error' => $e->getMessage(),
                'results' => $results,
            ]);
        }

    }

    protected function checkIfJsonOrJustText($results, $content): array
    {

        try {
            $results = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            $results = Arr::wrap($content);
        }

        /**
         * @NOTE
         * I do this extra check due to a fail on
         * a PHP version
         */
        if (is_null($results) && ! is_null($content)) {
            $results = Arr::wrap($content);
        }

        return $results;
    }

    protected function getIdFromPayload($result): string
    {
        if (data_get(Arr::wrap($this->payload), 'id', false)) {
            /**
             * @NOTE
             * You can pass in as a key in the payload
             * for example
             * {
             *     "id": "commit_id",
             *     "content": "Test Message"
             * }
             */
            $id = data_get($this->payload, 'id');
        } else {
            $id = md5($result);
        }

        return $id;
    }
}
