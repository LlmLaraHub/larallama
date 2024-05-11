<?php

namespace App\Jobs;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use App\Domains\Documents\StatusEnum;
use App\Models\DocumentChunk;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Helpers\JobMiddlewareTrait;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;

class SummarizeDataJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use JobMiddlewareTrait;

    /**
     * Create a new job instance.
     */
    public function __construct(public DocumentChunk $documentChunk)
    {
        //
    }

    public function middleware(): array
    {
        $middleware = $this->driverMiddleware($this->documentChunk);

        return $middleware;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (optional($this->batch())->cancelled()) {
                // Determine if the batch has been cancelled...
                $this->documentChunk->update([
                    'status_summary' => StatusEnum::Cancelled,
                ]);

                return;
            }

            $content = $this->documentChunk->content;
            $prompt = <<<EOD
    The following content is part of a larger document. I would like you to summarize it so 
    I can show a summary view of all the other pages and this ones related to the same document.
    Just return the summary, 1-2 lines if possible and no extra surrounding text.
    The content to summarize follows:
    
    $content
    EOD;

            /** @var CompletionResponse $results */
            $results = LlmDriverFacade::driver(
                $this->documentChunk->getDriver()
            )->completion($prompt);

            $verifyPrompt = <<<'PROMPT'
            This the content from a chunk of data in a document.
            Can you verify the summary is correct?
            PROMPT;

            $dto = VerifyPromptInputDto::from(
                [
                    'chattable' => $this->documentChunk,
                    'originalPrompt' => $prompt,
                    'context' => $content,
                    'llmResponse' => $results->content,
                    'verifyPrompt' => $verifyPrompt,
                ]
            );

            /** @var VerifyPromptOutputDto $response */
            $response = VerifyResponseAgent::verify($dto);

            $this->documentChunk->update([
                'summary' => $response->response,
                'status_summary' => StatusEnum::Complete,
            ]);
        } catch (\Exception $e) {
            Log::error('SummarizeDataJob Error', [
                'message' => $e->getMessage(),
            ]);

            return;
        }
    }
}
