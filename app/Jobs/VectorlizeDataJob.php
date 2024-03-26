<?php

namespace App\Jobs;

use App\LlmDriver\LlmDriverClient;
use App\LlmDriver\LlmDriverFacade;
use App\Models\DocumentChunk;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VectorlizeDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $content = $this->documentChunk->content;
        $results = LlmDriverFacade::embedDara($content);

        $this->documentChunk->update([
            'embedding' => $results,
        ]);
    }
}
