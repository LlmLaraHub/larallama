<?php

namespace App\Jobs;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Documents\StatusEnum;
use App\Events\CollectionStatusEvent;
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
    public function __construct(public Document $document)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       

        $count = $this->document->document_chunks()->count();
      

        $this->document->update([
            'status' => StatusEnum::Complete,
            'document_chunk_count' => $count,
        ]);

        CollectionStatusEvent::dispatch(
            $this->document->collection,
            CollectionStatusEnum::PROCESSED);

    }
}
