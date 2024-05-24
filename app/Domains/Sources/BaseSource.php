<?php

namespace App\Domains\Sources;

use App\Domains\Transformers\BaseTransformer;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Source;
use App\Models\Transformer;
use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

abstract class BaseSource
{
    public string $batchTitle = 'Chunking Source';

    public ?Document $document = null;

    public ?string $content = '';

    public array $meta_data = [];

    public Source $source;

    public SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::GenericSource;

    public string $documentSubject = '';

    public ?Transformer $lastRan = null;

    public array|Collection $transformers = [];

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
                new SummarizeDocumentJob($document),
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

    protected function batchTransformedSource(
        BaseTransformer $transformer,
        Source $source)
    {
        $chunks = $this->batchJobs($transformer->chunks);

        $document = $transformer->document;

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
}
