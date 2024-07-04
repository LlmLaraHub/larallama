<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\Filter;
use App\Models\Message;
use Facades\LlmLaraHub\LlmDriver\Orchestrate;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OrchestrateJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $messagesArray,
        public Chat $chat,
        public Message $message,
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
            notify_ui_complete($this->chat);

            return;
        }

        Log::info('[LaraChain] Orchestrate Job from batch');
        Orchestrate::handle($this->chat, $this->message);
        notify_ui_complete($this->chat);
    }
}
