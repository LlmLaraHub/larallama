<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\Filter;
use Facades\LlmLaraHub\LlmDriver\Orchestrate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class OrchestrateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;



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

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->chat->id)];
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);
    }

}
