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
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class ToolsCompleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Batchable;

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

        Log::info('[LaraChain] - Tools Complete Job running');

        $messages = $this->chat->getChatResponse();

        put_fixture('messages_before_final_job.json', $messages);

        $response = LlmDriverFacade::driver($this->chat->chatable->getDriver())->chat($messages);

        put_fixture('tool_complete_response.json', $response);

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
