<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\Filter;
use Facades\LlmLaraHub\LlmDriver\Orchestrate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrchestrateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $messagesArray,
        public Chat $chat,
        public ?Filter $filter = null,
        public string $tool = ''
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Orchestrate::handle($this->messagesArray, $this->chat, $this->filter, $this->tool);
    }
}
