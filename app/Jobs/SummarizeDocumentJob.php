<?php

namespace App\Jobs;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Documents\StatusEnum;
use App\Events\CollectionStatusEvent;
use App\Models\Document;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LlmLaraHub\LlmDriver\Helpers\TrimText;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use App\Domains\Agents\VerifyPromptOutputDto;

class SummarizeDocumentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $content = [];

        foreach ($this->document->document_chunks as $chunk) {
            $content[] = (new TrimText())->handle($chunk->content);
        }

        $content = implode(' ', $content);
        $intro = "The following content is part of a larger document. I would like you to summarize it so 
        I can show a summary view of all the other pages and this ones related to the same document.
        Just return the summary, 1-2 lines if possible and no extra surrounding text.
        The content to summarize follows:";

        $prompt = <<<EOD
$intro

{$content}
EOD;

        /** @var CompletionResponse $results */
        $results = LlmDriverFacade::driver(
            $this->document->getDriver()
        )->completion($prompt);


        $verifyPrompt = <<<'PROMPT'
        This the content from all the documents in this collection.
        Then that was passed into the LLM to summarize the results.
        PROMPT;

        $dto = VerifyPromptInputDto::from(
            [
                'chattable' => $this->document->collection,
                'originalPrompt' => $intro,
                'context' => $content,
                'llmResponse' => $results->content,
                'verifyPrompt' => $verifyPrompt,
            ]
        );

        /** @var VerifyPromptOutputDto $response */
        $response = VerifyResponseAgent::verify($dto);

        $this->document->update([
            'summary' => $response->response,
            'status_summary' => StatusEnum::Complete,
        ]);


        CollectionStatusEvent::dispatch(
            $this->document->collection,
            CollectionStatusEnum::PROCESSED
        );

        DocumentProcessingCompleteJob::dispatch($this->document);
    }
}
