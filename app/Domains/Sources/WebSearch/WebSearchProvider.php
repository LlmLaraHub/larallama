<?php

namespace App\Domains\Sources\WebSearch;

use App\Domains\Sources\WebSearch\WebSearchDriverClient;
use Illuminate\Support\ServiceProvider;

class WebSearchProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('web_search_driver', function () {
            return new WebSearchDriverClient();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        

    }
}
