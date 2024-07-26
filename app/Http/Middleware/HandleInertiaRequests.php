<?php

namespace App\Http\Middleware;

use App\Domains\Tokenizer\Templatizer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'email_name' => config('llmlarahub.email_name'),
            'steps' => Cache::remember('steps', now()->addDay(), function () {
                return Setting::createNewSetting()->steps;
            }),
            'llms' => Cache::remember('llms', now()->addDay(), function () {
                return Setting::getLlms();
            }),
            'active_llms' => Cache::remember('active_llms', now()->addDay(), function () {
                return Setting::getAllActiveLlms();
            }),
            'active_llms_with_embeddings' => Cache::remember('active_llms_with_embeddings', now()->addDay(), function () {
                return Setting::getAllActiveLlmsWithEmbeddings();
            }),
            'drivers' => Cache::remember('drivers', now()->addDay(), function () {
                return Setting::getDrivers();
            }),
            'app_name' => config('app.name'),
            'domain' => config('llmlarahub.domain'),
            'features' => Cache::remember('features', now()->addDay(), function () {
                return Feature::all();
            }),
            'tools' => LlmDriverFacade::getFunctionsForUi(),
            'tokens' => Templatizer::getTokens(),
        ]);
    }
}
