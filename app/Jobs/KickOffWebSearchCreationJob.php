<?php

namespace App\Jobs;

use App\Domains\Sources\WebSearch\WebSearchFacade;
use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class KickOffWebSearchCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //use the prompt to query the web
        $content = $this->document->summary;

        $prompt = <<<PROMPT
The user is asking to search the web but I want you to review the query and clean it up so I can pass 
it to an api to get results: Just return their content but with your rework so I can pass it right to the 
search api. ONLY return the updated query I will pass this directly to the API via code:

### START USER QUERY
$content
### END USER QUERY


PROMPT;

        Log::info("[Larachain] KickOffWebSearchCreationJob - {$this->document->id} - Prompt: $prompt");

        try {
            $response = LlmDriverFacade::driver($this->document->getDriver())
                ->completion($prompt);

            $search = $response->content;

            Log::info('[Larachain] Starting web search ', [
                'content reworked' => $search,
            ]);

            $results = WebSearchFacade::search($search, [
                'count' => 5,
            ]);

            $jobs = [];

            Log::info('[Larachain] Getting Content from websearch');

            foreach ($results->getWeb() as $web) {
                $jobs[] = new GetWebContentJob($this->document, $web);
            }

            Bus::batch($jobs)
                ->name("Getting Web Content - {$this->document->id}")
                ->onQueue(LlmDriverFacade::driver($this->document->getDriver())->onQueue())
                ->allowFailures()
                ->dispatch();

        } catch (\Exception $e) {
            Log::error("[Larachain] KickOffWebSearchCreationJob - {$this->document->id} - Error: {$e->getMessage()}");

            return;
        }

    }
}
