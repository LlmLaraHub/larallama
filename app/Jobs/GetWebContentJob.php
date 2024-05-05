<?php

namespace App\Jobs;

use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Models\Document;
use App\Models\DocumentChunk;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GetWebContentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Document $document,
        public WebResponseDto $webResponseDto
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...

            return;
        }

        Log::info("[Larachain] GetWebContentJob - {$this->document->id} - URL: {$this->webResponseDto->url}");
        $html = GetPage::make($this->document->collection)->handle($this->webResponseDto->url);

        $results = GetPage::make($this->document->collection)->parseHtml($html);

        $page_number = 1;
        $guid = md5($this->webResponseDto->url);

        Log::info("[Larachain] GetWebContentJob - {$this->document->id} - GUID: $guid");
        $DocumentChunk = DocumentChunk::updateOrCreate(
            [
                'guid' => $guid,
                'document_id' => $this->document->id,
            ],
            [
                'content' => $results,
                'sort_order' => $page_number
            ]
        );

        Log::info("[Larachain] adding to new batch");
        $this->batch()->add([
            new VectorlizeDataJob($DocumentChunk),
            new SummarizeDataJob($DocumentChunk),
        ]);

    }
}
