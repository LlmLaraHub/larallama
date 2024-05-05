<?php

namespace App\Domains\Sources\WebSearch;

use Illuminate\Support\ServiceProvider;

class WebSearchProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('web_search_driver', function () {
            $driver = config('llmdriver.sources.search_driver');
            $client = new WebSearchDriverClient();

            return $client;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
