<?php

namespace App\Jobs;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\Messages\RoleEnum;
use App\Domains\Prompts\PromptMerge;
use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Helpers\ChatHelperTrait;
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
use LlmLaraHub\LlmDriver\ToolsHelper;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

class GetWebContentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ChatHelperTrait, ToolsHelper;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Source $source,
        public WebResponseDto $webResponseDto
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

        $this->source = $this->checkForChat($this->source);

        $key = md5($this->webResponseDto->url.$this->source->id);

        if ($this->skip($this->source, $key)) {
            return;
        }

        $this->createSourceTask($this->source, $key);

        Log::info("[LaraChain] GetWebContentJob - {$this->source->title} - URL: {$this->webResponseDto->url}");

        /**
         * @NOTE
         * Sometimes the HTML is too big
         */
        $htmlResults = GetPage::make($this->source->collection)
            ->handle($this->webResponseDto->url, true);

        $prompt = PromptMerge::merge(
            ['[CONTEXT]'],
            [$htmlResults],
            $this->source->getPrompt()
        );

        $results = LlmDriverFacade::driver(
            $this->source->getDriver()
        )->completion($prompt);

        if ($this->ifNotActionRequired($results->content)) {
            Log::info('[LaraChain] - Web Source Skipping', [
                'prompt' => $prompt,
            ]);
        } else {
            $promptResultsOriginal = $results->content;

            $this->addUserMessage($this->source, $promptResultsOriginal);

            $promptResults = $this->arrifyPromptResults($promptResultsOriginal);

            /**
             * @NOTE all the user to build array results
             * Like Events from a webpage
             */
            foreach ($promptResults as $promptResultIndex => $promptResult) {

                $promptResult = json_encode($promptResult);

                $title = sprintf('WebPageSource - item #%d source: %s',
                    $promptResultIndex + 1,
                    $this->webResponseDto->url);

                /**
                 * Document can reference a source
                 */
                $document = Document::updateOrCreate(
                    [
                        'source_id' => $this->source->id,
                        'type' => TypesEnum::HTML,
                        'subject' => to_utf8($title),
                        'link' => $this->webResponseDto->url,
                        'collection_id' => $this->source->collection_id,
                    ],
                    [
                        'status' => StatusEnum::Pending,
                        'file_path' => $this->webResponseDto->url,
                        'status_summary' => StatusEnum::Pending,
                        'meta_data' => $this->webResponseDto->toArray(),
                        'original_content' => $promptResult,
                    ]
                );

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
                        ]
                    );

                    Log::info('[LaraChain] adding to new batch');

                    $chunks[] = new VectorlizeDataJob($DocumentChunk);

                    $page_number++;
                }

                Bus::batch($chunks)
                    ->name("Chunking Document from Web - {$this->webResponseDto->url}")
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

            /**
             * @NOTE
             * I could move this into the loop if it is not
             * enough here
             */
            $assistantMessage = $this->source->getChat()->addInput(
                message: json_encode($promptResults),
                role: RoleEnum::Assistant,
                show_in_thread: true,
                meta_data: MetaDataDto::from([
                    'driver' => $this->source->getDriver(),
                    'source' => $this->source->title,
                ]),
            );

            $this->savePromptHistory(
                message: $assistantMessage,
                prompt: $prompt);

        }
    }
}
