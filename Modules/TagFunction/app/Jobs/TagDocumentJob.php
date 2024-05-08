<?php

namespace LlmLaraHub\TagFunction\Jobs;

use App\Models\Document;
use Facades\LlmLaraHub\TagFunction\TagManager;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TagDocumentJob implements ShouldQueue
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
        if ($this->batch()?->cancelled()) {
            // Determine if the batch has been cancelled...

            return;
        }

        TagManager::handle($this->document);
    }
}
