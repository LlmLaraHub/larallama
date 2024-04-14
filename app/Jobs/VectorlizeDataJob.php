<?php

namespace App\Jobs;

use App\Domains\Documents\StatusEnum;
use App\Models\DocumentChunk;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LlmLaraHub\LlmDriver\Helpers\JobMiddlewareTrait;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;

class VectorlizeDataJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use JobMiddlewareTrait;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 25;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

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

        if (optional($this->batch())->cancelled()) {
            // Determine if the batch has been cancelled...
            $this->documentChunk->update([
                'status_embeddings' => StatusEnum::Cancelled,
            ]);

            return;
        }

        $content = $this->documentChunk->content;

        /** @var EmbeddingsResponseDto $results */
        $results = LlmDriverFacade::driver($this->documentChunk->getEmbeddingDriver())
            ->embedData($content);

        $embedding_column = $this->documentChunk->getEmbeddingColumn();

        $this->documentChunk->update([
            $embedding_column => $results->embedding,
            'status_embeddings' => StatusEnum::Complete,
        ]);
    }
}
