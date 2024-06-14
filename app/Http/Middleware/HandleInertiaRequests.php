<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Laravel\Pennant\Feature;

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
            'steps' => Setting::createNewSetting()->steps,
            'llms' => Setting::getLlms(),
            'active_llms' => Setting::getAllActiveLlms(),
            'active_llms_with_embeddings' => Setting::getAllActiveLlmsWithEmbeddings(),
            'drivers' => Setting::getDrivers(),
            'app_name' => config('app.name'),
            'features' => Feature::all(),
        ]);
    }
}
