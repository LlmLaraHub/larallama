<?php

namespace App\Jobs;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Documents\StatusEnum;
use App\Domains\Prompts\TitlePrompt;
use App\Models\Document;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;

class GiveTitleToDocumentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $results = '';

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

        Log::info('[LaraChain] Title To Document Job', [
            'document' => $this->document->id,
        ]);

        if (is_null($this->document->summary)) {
            Log::info('[LaraChain] Document has no summary', [
                'document' => $this->document->id,
            ]);

            return;
        }

        $prompt = TitlePrompt::prompt($this->document->summary);

        /** @var CompletionResponse $results */
        $results = LlmDriverFacade::driver(
            $this->document->getDriver()
        )->completion($prompt);

        $this->results = $results->content;

        $this->document->update([
            'subject' => str($this->results)->remove([
                '*',
                '"',
            ])->title()->toString(),
            'status_summary' => StatusEnum::Complete,
        ]);

        notify_collection_ui($this->document->collection, CollectionStatusEnum::PROCESSED, 'Document Title Added');

    }
}
