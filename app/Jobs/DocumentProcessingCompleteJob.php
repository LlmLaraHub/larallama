<?php

namespace App\Jobs;

use App\Domains\Documents\StatusEnum;
use App\Models\Document;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DocumentProcessingCompleteJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document, public Batch $batch)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->batchId = $this->batch->id;

        $count = $this->document->document_chunks()->count();
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            $this->document->update([
                'status' => StatusEnum::Cancelled,
                'document_chunk_count' => $count,
            ]);

            return;
        }

        $this->document->update([
            'status' => StatusEnum::Complete,
            'document_chunk_count' => $count,
        ]);

    }
}
