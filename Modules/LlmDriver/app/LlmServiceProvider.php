<?php

namespace LlmLaraHub\LlmDriver;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryClient;
use LlmLaraHub\LlmDriver\Functions\SearchAndSummarize;
use LlmLaraHub\LlmDriver\Functions\StandardsChecker;
use LlmLaraHub\LlmDriver\Functions\SummarizeCollection;
use OpenAI\Client;
use OpenAI\Contracts\ClientContract;

class LlmServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ClientContract::class, static function (): Client {
            Log::info('Using Overridden OpenAI Client');

            $apiKey = Setting::getSecret('openai', 'api_key');
            $organization = Setting::getSecret('openai', 'organization');

            if (! is_string($apiKey) || ($organization !== null && ! is_string($organization))) {
                throw new \Exception('OpenAI API Key is missing');
            }

            $timeout = Setting::getSecret('openai', 'request_timeout', 120);

            return \OpenAI::factory()
                ->withApiKey($apiKey)
                ->withOrganization($organization)
                ->withHttpHeader('OpenAI-Beta', 'assistants=v1')
                ->withHttpClient(new \GuzzleHttp\Client(['timeout' => $timeout]))
                ->make();
        });

        $this->app->alias(ClientContract::class, 'openai');
        $this->app->alias(ClientContract::class, Client::class);
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

        $this->app->bind('standards_checker', function () {
            return new StandardsChecker();
        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            Client::class,
            ClientContract::class,
            'openai',
        ];
    }
}
