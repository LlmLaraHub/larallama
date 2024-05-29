<?php

namespace App\Domains\Sources;

use App\Domains\Prompts\PromptMerge;
use App\Domains\UnStructured\StructuredTypeEnum;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Output;
use App\Models\Source;
use App\Models\Transformer;
use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

abstract class BaseSource
{
    public string $batchTitle = 'Chunking Source';

    public static string $description = 'Sources are ways we get data into the system. They are the core of the system.';

    public ?Document $document = null;

    public ?string $content = '';

    public ?string $summarizeDocumentPrompt = '';

    public array $meta_data = [];

    public array $document_chunks = [];

    public Source $source;

    public SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::GenericSource;

    public string $documentSubject = '';

    public ?Transformer $lastRan = null;

    public array|Collection $transformers = [];

    public function addDocumentChunk(DocumentChunk $documentChunk): self
    {
        $this->document_chunks[] = $documentChunk;

        return $this;
    }

    public function setDocument(Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function setSource(Source $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function setLastRun(Transformer $transformer): self
    {
        $this->lastRan = $transformer;

        return $this;
    }

    protected function batchWithDocument(Document $document): array
    {
        return [
            [
                new SummarizeDocumentJob($document, $this->getSummarizeDocumentPrompt()),
                new TagDocumentJob($document),
            ],
        ];
    }

    protected function batchWithVector(DocumentChunk $documentChunk): array
    {
        return [
            new VectorlizeDataJob($documentChunk),
        ];
    }

    protected function batchJobs(array $jobs): array
    {
        $chunks = [];

        foreach ($jobs as $documentChunk) {
            $chunks[] = $this->batchWithVector($documentChunk);
        }

        return $chunks;
    }

    protected function getSummarizeDocumentPrompt(): string
    {
        return $this->summarizeDocumentPrompt;
    }

    protected function batchTransformedSource(
        BaseSource $baseSource,
        Source $source)
    {
        $chunks = $this->batchJobs($baseSource->document_chunks);

        $document = $baseSource->document;

        $batchWithDocuments = $this->batchWithDocument($document);

        $title = $this->batchTitle;

        Bus::batch($chunks)
            ->name($this->batchTitle)
            ->finally(function (Batch $batch) use ($title, $document, $batchWithDocuments) {
                Bus::batch($batchWithDocuments)
                    ->name($title)
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

    public function getContext(Output $output): string
    {
        $documents = $output->collection
            ->documents()
            ->when($output->last_run != null, function ($query) use ($output) {
                $query->whereDate('created_at', '>=', $output->last_run);
            }, function ($query) {
                $query->limit(5);
            })
            ->latest()
            ->get();

        if ($documents->count() === 0) {
            Log::info('LaraChain] - No Emails since the last run');

            return '';
        }

        $content = [];

        foreach ($documents as $document) {
            if (! empty($document->children)) {
                foreach ($document->children as $child) {
                    $content[] = $this->getContentFromChild($child);
                }
            } else {
                //@TODO
                // we get it from the chunks that are to and from
                //and the summary
            }
            $content[] = 'Sent At: '.$document->created_at;
            $content[] = 'Subject: '.$document->subject;

            $content[] = "### START BODY\n";
            $content[] = $this->getEmailSummary($document);
            $content[] = "### END BODY\n";

        }

        $content = implode("\n", $content);
        $tokens = ['[CONTEXT]'];
        $content = [$content];

        $prompt = PromptMerge::merge($tokens, $content, $output->summary);

        Log::info('[LaraChain] - Sending this prompt to LLM', [
            'prompt' => $prompt,
        ]);

        return $prompt;
    }

    protected function getContentFromChild(Document $document): string
    {
        $type = ($document->child_type === StructuredTypeEnum::EmailTo) ? 'To' : 'From';
        $summary = $document->summary;

        $message = <<<MESSAGE
This email was $type the following Contact
$summary
MESSAGE;

        return $message;
    }

    protected function getEmailSummary(Document $document): string
    {
        /** @phpstan-ignore-next-line */
        $content = $document
            ->document_chunks()
            ->where('type', StructuredTypeEnum::EmailBody)
            ->orderBy('section_number')
            ->get()
            ->pluck('content')
            ->implode("\n");

        return $content;
    }
}
