<?php

namespace App\Providers;

use App\Models\User;
use Feature;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        Gate::define('viewPulse', function (User $user) {
            return $user->isAdmin();
        });

        Feature::define('ollama-functions', function (User $user) {
            return config('llmdriver.drivers.ollama.feature_flags.functions'); //just not ready yet
        });


    }
}
