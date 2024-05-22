<?php

namespace App\Domains\Sources;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use Facades\App\Domains\EmailParser\Client;
use App\Domains\EmailParser\MailDto;
use App\Helpers\TextChunker;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Source;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

class EmailSource extends BaseSource
{
    private ?MailDto $mailDto = null;

    public function getMailDto(): MailDto
    {
        return $this->mailDto;
    }

    public function setMailDto(MailDto $mailDto): self
    {
        $this->mailDto = $mailDto;

        return $this;
    }

    public function handle(Source $source): void
    {
        if(!$this->mailDto) {
            Client::handle();
            return;
        }

        Log::info('[LaraChain] - Running Email Source');

        try {
            Log::info("Do something!");

            if($source->transformers()->count() === 0) {
                /**
                 * @NOTE
                 * No need to queue this yet since
                 * it is not doing any LLM work
                 *
                 */
                Log::info('[LaraChain] Starting EmailTransformer ', [
                    'source' => $source->id
                ]);

                $chunks = [];

                $document = Document::updateOrCreate(
                    [
                        'source_id' => $source->id,
                        'type' => TypesEnum::Email,
                        'subject' => $this->mailDto->subject,
                        'collection_id' => $source->collection_id,
                    ],
                    [
                        'status' => StatusEnum::Pending,
                        'file_path' => null,
                        'status_summary' => StatusEnum::Pending,
                        'meta_data' => $this->mailDto->toArray(),
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
                        $chunkSection,
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
                    'last_run' => now()
                ]);

            } else {
                //lots to do here!
                Log::info("[LaraChain] - Source has Transformers let's figure out which one to run");
            }

        } catch (\Exception $e) {
            Log::error('[LaraChain] - Error running Email Source', [
                'error' => $e->getMessage(),
            ]);
        }

    }

    protected function documentChunk(
        Document $document,
        string $content,
        int $sort_order,
        int $section_number
    ) : DocumentChunk
    {
        return DocumentChunk::updateOrCreate(
            [
                'document_id' => $document->id,
                'sort_order' => $sort_order,
                'section_number' => $section_number,
                'guid' => md5($content),
            ],
            [
                'content' => $content,
            ]
        );
    }

    protected function batchJobs(array $jobs) : array
    {
        $chunks = [];

        foreach ($jobs as $DocumentChunk) {
            $chunks[] = [
                new VectorlizeDataJob($DocumentChunk),
            ];
        }

        return $chunks;
    }

    public function getSourceFromSlug(string $slug): ?Source
    {
        $source = Source::where('type', SourceTypeEnum::EmailSource)
            ->slug($slug)
            ->first();

        if ($source) {
            return $source;
        }

        return null;
    }
}
