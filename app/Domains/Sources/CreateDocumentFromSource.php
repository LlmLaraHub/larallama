<?php

namespace App\Domains\Sources;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Documents\StatusEnum;
use App\Domains\Messages\RoleEnum;
use App\Helpers\ChatHelperTrait;
use App\Helpers\TextChunker;
use App\Jobs\DocumentProcessingCompleteJob;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Source;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\ToolsHelper;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

class CreateDocumentFromSource
{
    use ChatHelperTrait, ToolsHelper;

    protected Source $source;

    public function handle(Source $source, string $content, DocumentDto $documentDto): void
    {
        $this->source = $this->checkForChat($source);

        $promptUsingCollection = Templatizer::appendContext(true)
            ->handle($this->source->collection->getPrompt(), $content);

        $results = LlmDriverFacade::driver(
            $this->source->getDriver()
        )->completion($promptUsingCollection);

        $promptResults = $results->content;

        $this->addUserMessage($this->source, $promptResults);

        /**
         * Document can reference a source
         */
        $document = Document::updateOrCreate(
            [
                'source_id' => $this->source->id,
                'type' => $documentDto->type,
                'subject' => $documentDto->subject,
                'document_md5' => $documentDto->document_md5,
                'link' => $documentDto->link,
                'collection_id' => $this->source->collection_id,
            ],
            [
                'status' => StatusEnum::Pending,
                'file_path' => $documentDto->file_path,
                'status_summary' => StatusEnum::Pending,
                'meta_data' => $documentDto->meta_data,
                'original_content' => $content,
            ]
        );

        $page_number = 1;

        $chunked_chunks = TextChunker::handle($promptResults);

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
            ->name("Chunking Document from Web - {$documentDto->title}")
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
            prompt: $promptUsingCollection);
    }
}
