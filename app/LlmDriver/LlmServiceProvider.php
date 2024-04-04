<?php

namespace App\LlmDriver;

use App\LlmDriver\Functions\SearchAndSummarize;
use App\LlmDriver\Functions\SummarizeCollection;
use Illuminate\Support\ServiceProvider;

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

        $this->app->bind('summarize_collection', function () {
            return new SummarizeCollection();
        });

        $this->app->bind('search_and_summarize', function() {
            return new SearchAndSummarize();
        });

    }
}
