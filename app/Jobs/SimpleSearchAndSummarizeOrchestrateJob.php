<?php

namespace App\Jobs;

use App\Domains\Messages\RoleEnum;
use App\Models\Collection;
use App\Models\Message;
use App\Models\PromptHistory;
use Facades\LlmLaraHub\LlmDriver\NonFunctionSearchOrSummarize;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;
use LlmLaraHub\LlmDriver\Responses\NonFunctionResponseDto;

/**
 * @NOTE
 * This is used by LLMs that might not have functions
 * but still want to do a search and summarize
 */
class SimpleSearchAndSummarizeOrchestrateJob implements ShouldQueue
{
    use Batchable;
    use CreateReferencesTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Message $message)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('[LaraChain] Skipping over functions doing SimpleSearchAndSummarizeOrchestrateJob');

        notify_ui(
            $this->message->getChatable(),
            'Searching data now to summarize content'
        );

        $collection = $this->message->getChatable();

        if (get_class($collection) === Collection::class) {
            /** @var NonFunctionResponseDto $results */
            $results = NonFunctionSearchOrSummarize::handle($this->message);

            $message = $this->message->getChat()->addInput(
                message: $results->response,
                role: RoleEnum::Assistant,
                show_in_thread: true,
                meta_data: $this->message->meta_data
            );

            if ($results->prompt) {
                PromptHistory::create([
                    'prompt' => $results->prompt,
                    'chat_id' => $this->message->getChat()->id,
                    'message_id' => $message->id,
                    /** @phpstan-ignore-next-line */
                    'collection_id' => $this->message->getChatable()?->id,
                ]);
            }

            if ($results->documentChunks->isNotEmpty()) {
                $this->saveDocumentReference(
                    $message,
                    $results->documentChunks
                );
            }

            notify_ui($this->message->getChat(), 'Complete');
        } else {
            Log::info('Can only handle Collection model right now');
        }

    }
}
