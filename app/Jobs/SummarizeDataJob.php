<?php

namespace App\Jobs;

use App\Domains\Documents\StatusEnum;
use App\LlmDriver\LlmDriverFacade;
use App\LlmDriver\Responses\CompletionResponse;
use App\Models\DocumentChunk;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SummarizeDataJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public DocumentChunk $documentChunk)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
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

{$content}
EOD;

        /** @var CompletionResponse $results */
        $results = LlmDriverFacade::driver(
            $this->documentChunk->getDriver()
        )->completion($prompt);

        $this->documentChunk->update([
            'summary' => $results->content,
            'status_summary' => StatusEnum::Complete,
        ]);
    }
}
