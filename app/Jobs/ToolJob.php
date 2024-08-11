<?php

namespace App\Jobs;

use App\Domains\Messages\RoleEnum;
use App\Models\Message;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LlmLaraHub\LlmDriver\Functions\FunctionContract;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class ToolJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public FunctionContract $function,
        public Message $message
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

        $results = $this->function->setBatch($this->batch())->handle($this->message);

        $this->message->updateQuietly([
            'is_chat_ignored' => true,
            'role' => RoleEnum::Tool,
            'body' => $results->content,
        ]);

        /**
         * @NOTE
         * Should I do anything with the results of above FunctionResponse
         * Should I set the batch and if so how to best use it
         */
    }
}
