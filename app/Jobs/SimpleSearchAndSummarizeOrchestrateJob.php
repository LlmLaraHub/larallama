<?php

namespace App\Jobs;

use App\Domains\Messages\RoleEnum;
use App\Models\Collection;
use App\Models\Filter;
use App\Models\PromptHistory;
use Facades\LlmLaraHub\LlmDriver\NonFunctionSearchOrSummarize;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;
use LlmLaraHub\LlmDriver\Responses\NonFunctionResponseDto;

class SimpleSearchAndSummarizeOrchestrateJob implements ShouldQueue
{
    use Batchable;
    use CreateReferencesTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $input,
        public HasDrivers $chat,
        public ?Filter $filter = null)
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
            $this->chat->getChatable(),
            'Searching data now to summarize content'
        );

        $collection = $this->chat->getChatable();

        if (get_class($collection) === Collection::class) {
            /** @var NonFunctionResponseDto $results */
            $results = NonFunctionSearchOrSummarize::handle($this->input, $collection, $this->filter);

            $message = $this->chat->getChat()->addInput(
                message: $results->response,
                role: RoleEnum::Assistant,
                show_in_thread: true
            );

            if ($results->prompt) {
                PromptHistory::create([
                    'prompt' => $results->prompt,
                    'chat_id' => $this->chat->getChat()->id,
                    'message_id' => $message->id,
                    /** @phpstan-ignore-next-line */
                    'collection_id' => $this->chat->getChatable()?->id,
                ]);
            }

            if ($results->documentChunks->isNotEmpty()) {
                $this->saveDocumentReference(
                    $message,
                    $results->documentChunks
                );
            }
        } else {
            Log::info('Can only handle Collection model right now');
        }

    }
}
