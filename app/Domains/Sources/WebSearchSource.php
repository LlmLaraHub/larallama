<?php

namespace App\Domains\Sources;

use App\Domains\Collections\CollectionStatusEnum;
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

            Log::info('[LaraChain] Starting web search ', [
                'content reworked' => $search,
            ]);

            /** @var SearchResponseDto $response */
            $response = WebSearchFacade::driver($driver)->search(
                search: $search,
                options: [
                    'limit' => $limit,
                    'last_run' => $source->last_run,
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

            notify_collection_ui(
                collection: $source->collection,
                status: CollectionStatusEnum::PENDING,
                message: 'Search complete getting results from each page'
            );

        } catch (\Exception $e) {
            Log::error('[LaraChain] - Error running WebSearchSource', [
                'error' => $e->getMessage(),
            ]);
        }

    }
}
