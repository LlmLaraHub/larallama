<?php

namespace App\Jobs;

use App\Domains\Documents\StatusEnum;
use App\Domains\Prompts\SummarizeDocumentPrompt;
use App\Models\Document;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Functions\ToolTypes;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;

class SummarizeDocumentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $results = '';

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document, public string $prompt = '')
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $content = $this->document->original_content;

        if (empty($this->prompt)) {
            $prompt = $this->document->collection->summary_prompt;
            if (! empty($prompt)) {
                $prompt = Templatizer::appendContext(true)
                    ->handle($prompt, $content);
            } else {
                $prompt = SummarizeDocumentPrompt::prompt($content);
            }

        } else {
            $prompt = Templatizer::appendContext(true)
                ->handle($this->prompt, $content);
        }
        Log::info('[LaraChain] Summarizing Document', [
            'token_count_v2' => token_counter_v2($content),
            'token_count_v1' => token_counter($content),
            'prompt' => $prompt,
        ]);

        /** @var CompletionResponse $results */
        $results = LlmDriverFacade::driver(
            $this->document->getDriver()
        )->setToolType(ToolTypes::NoFunction)
            ->chat([
                MessageInDto::from([
                    'content' => $prompt,
                    'role' => 'user',
                ]),
            ]);

        Log::info('[LaraChain] Summarizing Document Results', [
            'results' => $results->content,
        ]);

        $this->results = $results->content;

        $this->document->update([
            'summary' => $results->content,
            'status_summary' => StatusEnum::SummaryComplete,
        ]);

    }
}
