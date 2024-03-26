<?php

namespace App\Jobs;

use App\Domains\Documents\StatusEnum;
use App\LlmDriver\LlmDriverFacade;
use App\Models\DocumentChunk;
use Illuminate\Bus\Batchable;
use App\LlmDriver\Responses\CompletionResponse;
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

        /** @var CompletionResponse $results */
        $results = LlmDriverFacade::completion($content);

        $this->documentChunk->update([
            'summary' => $results->content,
            'status_summary' => StatusEnum::Complete,
        ]);
    }
}
