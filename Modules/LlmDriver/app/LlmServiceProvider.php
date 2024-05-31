<?php

namespace LlmLaraHub\LlmDriver;

use Illuminate\Support\ServiceProvider;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryClient;
use LlmLaraHub\LlmDriver\Functions\SearchAndSummarize;
use LlmLaraHub\LlmDriver\Functions\SummarizeCollection;

class LlmServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind('llm_driver', function () {
            return new LlmDriverClient();
        });

        $this->app->bind('distance_query_driver', function () {
            return new DistanceQueryClient();
        });

        $this->app->bind('summarize_collection', function () {
            return new SummarizeCollection();
        });

        $this->app->bind('search_and_summarize', function () {
            return new SearchAndSummarize();
        });

    }
}
