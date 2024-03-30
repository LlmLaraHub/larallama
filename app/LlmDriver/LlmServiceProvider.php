<?php

namespace App\LlmDriver;

use App\LlmDriver\LlmDriverClient;
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

    }
}
