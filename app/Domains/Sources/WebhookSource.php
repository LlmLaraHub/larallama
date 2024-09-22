<?php

namespace App\Domains\Sources;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Jobs\ChunkDocumentJob;
use App\Models\Document;
use App\Models\Message;
use App\Models\Source;
use Facades\App\Domains\Orchestration\OrchestrateVersionTwo;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

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

        /** @var Message $assistantMessage */
        $assistantMessage = OrchestrateVersionTwo::sourceOrchestrate(
            $source->refresh()->chat,
            $prompt
        );

        if ($this->ifNotActionRequired($assistantMessage->getContent())) {
            Log::info('[LaraChain] - Webhook Skipping', [
                'prompt' => $prompt,
            ]);
        } else {
            Log::info('[LaraChain] - WebhookSource Transformation Results', [
                'assistant_message' => $assistantMessage->id,
            ]);

            $promptResultsOriginal = $assistantMessage->getContent();

            $this->addUserMessage($source, $promptResultsOriginal);

            $promptResults = $this->arrifyPromptResults($promptResultsOriginal);

            foreach ($promptResults as $promptResultIndex => $promptResult) {
                $promptResult = json_encode($promptResult);

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

                Bus::batch([new ChunkDocumentJob($document)])
                    ->name('Processing '.$title)
                    ->allowFailures()
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
