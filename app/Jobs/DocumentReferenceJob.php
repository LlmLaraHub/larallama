<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;

class DocumentReferenceJob implements ShouldQueue
{
    use CreateReferencesTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Message $message,
        public Collection $documentChunks
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->saveDocumentReference($this->message, $this->documentChunks);
    }
}
