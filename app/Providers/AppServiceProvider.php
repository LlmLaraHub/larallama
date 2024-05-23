<?php

namespace App\Providers;

use App\Listeners\InvitingTeamMemberAddToSystemListener;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Events\InvitingTeamMember;
use Laravel\Pennant\Feature;

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

        Feature::define('verification_prompt_tags', function (User $user) {
            return false;
        });

        Feature::define('verification_prompt', function (User $user) {
            return false;
        });

        Event::listen(
            InvitingTeamMember::class,
            InvitingTeamMemberAddToSystemListener::class,
        );

    }
}
