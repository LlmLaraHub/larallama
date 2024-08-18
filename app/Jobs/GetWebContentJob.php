<?php

namespace App\Jobs;

use App\Domains\Documents\TypesEnum;
use App\Domains\Sources\DocumentDto;
use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Helpers\ChatHelperTrait;
use App\Models\Document;
use App\Models\Source;
use Facades\App\Domains\Orchestration\OrchestrateVersionTwo;
use Facades\App\Domains\Sources\CreateDocumentFromSource;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\ToolsHelper;

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

        $prompt = Templatizer::appendContext(true)
            ->handle($this->source->getPrompt(), $htmlResults->content);

        /**
         * Do we care about the results
         * No tools
         */
        $results = LlmDriverFacade::driver(
            $this->source->getDriver()
        )->completion($prompt);

        if ($this->ifNotActionRequired($results->content)) {
            Log::info('[LaraChain] - Web Source Skipping', [
                'prompt' => $prompt,
            ]);

            return;
        } else {

            OrchestrateVersionTwo::sourceOrchestrate(
                $this->source->getchat(),
                $prompt);

            /**
             * @NOTE
             * this should be driven by tools as well
             * maybe we do not want a Document
             * Maybe this is making an event
             *
             * @TODO
             * Make this a tool and or pull it out for now in a
             * shared Class for Sources since it is the same for all of them
             */
            CreateDocumentFromSource::handle(
                source: $this->source,
                content: $htmlResults->content,
                documentDto: DocumentDto::from([
                    'type' => TypesEnum::HTML,
                    'link' => $this->webResponseDto->url,
                    'title' => sprintf('WebPageSource - %s', $this->webResponseDto->url),
                    'subject' => sprintf('WebPageSource - %s', $this->webResponseDto->url),
                    'file_path' => $this->webResponseDto->url,
                    'document_md5' => md5($htmlResults->content),
                    'meta_data' => $this->webResponseDto->toArray(),
                ])
            );

        }
    }
}
