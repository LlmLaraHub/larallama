<?php

namespace App\Jobs;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Helpers\TextChunker;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Source;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

class WebPageSourceJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Source $source,
        public string $url
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...

            return;
        }

        $jobs = [];

        $html = GetPage::make($this->source->collection)->handle($this->url);

        $html = GetPage::parseHtml($html);

        $html = to_utf8($html);

        $title = sprintf('WebPageSource - source: %s', $this->url);

        $parseTitle = str($html)->limit(50)->toString();

        if (! empty($parseTitle)) {
            $title = $parseTitle;
        }

        $document = Document::updateOrCreate(
            [
                'source_id' => $this->source->id,
                'type' => TypesEnum::HTML,
                'subject' => to_utf8($title),
                'link' => $this->url,
                'collection_id' => $this->source->collection_id,
            ],
            [
                'status' => StatusEnum::Pending,
                'file_path' => $this->url,
                'summary' => str($html)->limit(254)->toString(),
                'status_summary' => StatusEnum::Pending,
                'original_content' => $html,
                'meta_data' => $this->source->meta_data,
            ]
        );

        $page_number = 1;

        $chunked_chunks = TextChunker::handle($html);

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
                    'content' => to_utf8($chunkContent), //still having issues.
                ]
            );

            Log::info('[LaraChain] adding to new batch');

            $jobs[] = [
                new VectorlizeDataJob($DocumentChunk),
                new TagDocumentJob($document),
                new SummarizeDocumentJob($document),
            ];

            $page_number++;
        }

        Bus::batch($jobs)
            ->name('Web Pages to Documents - '.$this->source->subject)
            ->finally(function (Batch $batch) use ($document) {
                DocumentProcessingCompleteJob::dispatch($document);
            })
            ->allowFailures()
            ->onQueue(LlmDriverFacade::driver($this->source->getDriver())->onQueue())
            ->dispatch();

    }
}
