<?php

namespace App\Domains\Sources;

use App\Domains\Sources\WebSearch\Response\SearchResponseDto;
use App\Domains\Sources\WebSearch\WebSearchFacade;
use App\Jobs\GetWebContentJob;
use App\Models\Source;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class WebSearchSource extends BaseSource
{
    public function handle(Source $source): void
    {
        Log::info('[LaraChain] - Running WebSearchSource');

        try {
            $meta_data = $source->meta_data;
            $search = $source->details;
            $limit = data_get($meta_data, 'limit', 5);
            $driver = data_get($meta_data, 'driver', 'mock');

            $prompt = <<<PROMPT
            The user is asking to search the web but I want you to review the query and clean it keeping it as 
            a string.

            Here are some examples of how I want you to return the data:
            ### RETURN FORMAT
            in: Search the web for PHP news and Laravle news
            out: php news OR laravel news -filetype:pdf -intitle:pdf

            in: current data on the llm industry
            out: llm industry news OR llm industry updates -filetype:pdf -intitle:pdf

            in: latest news on the laravel framework
            out: laravel framework news OR laravel framework updates -filetype:pdf -intitle:pdf
            ### END RETURN FORMAT

            
            ### START USER QUERY
            $search
            ### END USER QUERY

         
PROMPT;

            Log::info('[LaraChain] Asking LLM to optimize search query');

            $response = LlmDriverFacade::driver($source->getDriver())
                ->completion($prompt);

            $search = $response->content;

            Log::info('[LaraChain] Starting web search ', [
                'content reworked' => $search,
            ]);

            /** @var SearchResponseDto $response */
            $response = WebSearchFacade::driver($driver)->search(
                search: $search,
                options: [
                    'limit' => $limit,
                ]
            );

            $jobs = [];

            Log::info('[Larachain] Getting Content from websearch');

            foreach ($response->getWeb() as $web) {
                $jobs[] = new GetWebContentJob($source, $web);
            }

            Log::info('[Larachain] Dispatching jobs to get content', [
                'jobs' => count($jobs),
            ]);

            Bus::batch($jobs)
                ->name("Getting Web Content for Source - {$source->title}")
                ->onQueue(LlmDriverFacade::driver($source->getDriver())->onQueue())
                ->allowFailures()
                ->dispatch();

            $source->last_run = now();
            $source->save();

        } catch (\Exception $e) {
            Log::error('[LaraChain] - Error running WebSearchSource', [
                'error' => $e->getMessage(),
            ]);
        }

    }
}
