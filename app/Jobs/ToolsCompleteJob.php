<?php

namespace App\Jobs;

use App\Domains\Messages\RoleEnum;
use App\Models\Chat;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Functions\ToolTypes;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class ToolsCompleteJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Chat $chat)
    {
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

        Log::info('[LaraChain] - Tools Complete Job running', [
            'chat' => $this->chat->id,
            'driver' => $this->chat->getDriver(),
        ]);

        $messages = $this->chat->getChatResponse();

        $response = LlmDriverFacade::driver($this->chat->getDriver())
            ->setToolType(ToolTypes::NoFunction)
            ->chat($messages);

        $this->chat->addInput(
            message: $response->content,
            role: RoleEnum::Assistant,
            show_in_thread: true,
            meta_data: null,
            tools: null
        );

        notify_ui_complete($this->chat);
    }
}
