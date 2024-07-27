<?php

namespace App\Domains\Sources;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Helpers\TextChunker;
use App\Jobs\DocumentProcessingCompleteJob;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Source;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Bus\Batch;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
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
        Log::info('[LaraChain] - WebhookSource', [
            'payload' => $this->payload,
        ]);

        $this->source = $this->checkForChat($source);
        $payloadMd5 = md5(json_encode($this->payload, 128));
        $key = md5($payloadMd5.$this->source->id);

        if ($this->skip($this->source, $key)) {
            return;
        }

        $this->createSourceTask($this->source, $key);
        $encoded = json_encode($this->payload, 128);

        $prompt = Templatizer::appendContext(true)
            ->handle($this->source->getPrompt(), $encoded);

        $results = LlmDriverFacade::driver(
            $source->getDriver()
        )->completion($prompt);

        if ($this->ifNotActionRequired($results->content)) {
            Log::info('[LaraChain] - Webhook Skipping', [
                'prompt' => $prompt,
            ]);
        } else {
            Log::info('[LaraChain] - WebhookSource Transformation Results', [
                'results' => $results,
            ]);

            $promptResultsOriginal = $results->content;

            $this->addUserMessage($source, $promptResultsOriginal);

            $promptResults = $this->arrifyPromptResults($promptResultsOriginal);

            foreach ($promptResults as $promptResultIndex => $promptResult) {
                $promptResult = json_encode($promptResult);

                /**
                 * Could even do ONE more look at the data
                 * with the Source Prompt and LLM
                 */
                $title = sprintf('WebhookSource - item #%d source: %s',
                    $promptResultIndex + 1, md5($promptResult));

                $document = Document::updateOrCreate([
                    'type' => TypesEnum::WebHook,
                    'source_id' => $source->id,
                    'subject' => $title,
                    'collection_id' => $source->collection_id,
                ], [
                    'status' => StatusEnum::Pending,
                    'meta_data' => $this->payload,
                    'status_summary' => StatusEnum::Pending,
                    'summary' => $promptResult,
                    'original_content' => $promptResult,
                ]);

                $page_number = 1;

                $chunked_chunks = TextChunker::handle($promptResult);

                $chunks = [];

                foreach ($chunked_chunks as $chunkSection => $chunkContent) {
                    $guid = md5($chunkContent);

                    $DocumentChunk = DocumentChunk::updateOrCreate(
                        [
                            'document_id' => $document->id,
                            'guid' => $guid,
                        ],
                        [
                            'sort_order' => $page_number,
                            'section_number' => $chunkSection,
                            'content' => to_utf8($chunkContent),
                            'original_content' => to_utf8($chunkContent),
                        ]
                    );

                    Log::info('[LaraLlama] WebhookSource adding to new batch');

                    $chunks[] = new VectorlizeDataJob($DocumentChunk);

                    $page_number++;
                }

                Bus::batch($chunks)
                    ->name("Chunking Document from WebhookSource - {$this->source->id}")
                    ->allowFailures()
                    ->finally(function (Batch $batch) use ($document) {
                        Bus::batch([
                            [
                                new SummarizeDocumentJob($document),
                                new TagDocumentJob($document),
                                new DocumentProcessingCompleteJob($document),
                            ],
                        ])
                            ->name(sprintf('Final Document Steps Document %s id %d', $document->type->name, $document->id))
                            ->allowFailures()
                            ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                            ->dispatch();
                    })
                    ->onQueue(LlmDriverFacade::driver($this->source->getDriver())->onQueue())
                    ->dispatch();
            }

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
