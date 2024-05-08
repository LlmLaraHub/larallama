<?php

namespace App\Jobs;

use App\Models\Chat;
use Facades\LlmLaraHub\LlmDriver\SimpleSearchAndSummarizeOrchestrate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SimpleSearchAndSummarizeOrchestrateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $input, public Chat $chat)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        SimpleSearchAndSummarizeOrchestrate::handle($this->input, $this->chat);
    }
}
