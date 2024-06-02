<?php

namespace App\Domains\Sources;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\Prompts\Transformers\GithubTransformer;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Source;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        Log::info('[LaraChain] - WebhookSource');

        $chunks = [];

        $encoded = json_encode($this->payload, 128);

        $prompt = GithubTransformer::prompt($encoded);

        put_fixture('prompt_and_payload.txt', $prompt, false);

        $results = LlmDriverFacade::driver(
            $source->getDriver()
        )->completion($prompt);

        Log::info('[LaraChain] - WebhookSource Transformation Results', [
            'results' => $results,
        ]);

        $content = $results->content;
        $content = str($content)
            ->replace('```json', '')
            ->replaceLast('```', '')
            ->toString();

        try {
            $results = json_decode($content, true);

            foreach ($results as $index => $result) {
                $id = data_get($result, 'commit_id', Str::random(12));
                $result = data_get($result, 'message', $result);
                $document = Document::updateOrCreate([
                    'type' => TypesEnum::WebHook,
                    'source_id' => $source->id,
                    'subject' => 'Commit ID: '.$id,
                ], [
                    'status' => StatusEnum::Pending,
                    'meta_data' => $this->payload,
                    'collection_id' => $source->collection_id,
                    'status_summary' => StatusEnum::Pending,
                    'summary' => $result,
                ]);

                $this->document = $document;

                $DocumentChunk = DocumentChunk::updateOrCreate(
                    [
                        'document_id' => $document->id,
                        'sort_order' => $index + 1,
                        'section_number' => 0,
                    ],
                    [
                        'guid' => md5($result),
                        'content' => $result,
                    ]
                );

                $chunks[] = [
                    new VectorlizeDataJob($DocumentChunk),
                ];

                Bus::batch($chunks)
                    ->name("Chunking Document from Webhook - {$this->document->id} {$this->document->file_path}")
                    ->allowFailures()
                    ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                    ->dispatch();
            }
        } catch (\Exception $e) {
            Log::error('[LaraChain] - Error running WebhookSource', [
                'error' => $e->getMessage(),
                'results' => $results,
            ]);
        }

    }
}
