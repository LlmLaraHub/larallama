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
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Search;

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
        $content = $this->document->content;

        $prompt = <<<PROMPT
The user is asking to search the web but I want you to review the query and clean it up so I can pass 
it to an api to get results: Just return their content but with your rework so I can pass it right to the 
search api.

### START USER QUERY
$content
### END USER QUERY


PROMPT;        

        $response = LlmDriverFacade::driver($this->document->getDriver())
        ->completion($prompt);

        $search = $response->content;

        $results = WebSearchFacade::search($search, [
            'count' => 5
        ]);

        $jobs = [];

        foreach ($results->getWeb() as $web) {
            $jobs[] = new GetWebContentJob($this->document, $web);
        }
        
        Bus::batch($jobs)
            ->name("Getting Web Content - {$this->document->id}")
            ->onQueue(LlmDriverFacade::driver($this->document->getDriver())->onQueue())
            ->allowFailures()
            ->dispatch();
        

    }
}
