<?php

namespace App\Providers;

use App\Domains\EmailParser\EmailClientWrapper;
use App\Listeners\InvitingTeamMemberAddToSystemListener;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Events\InvitingTeamMember;
use Laravel\Pennant\Feature;
use Opcodes\LogViewer\Facades\LogViewer;
use vipnytt\SitemapParser;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('email_client_facade', function ($app) {
            return new EmailClientWrapper();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LogViewer::auth(function ($request) {
            if (! auth()->check()) {
                return false;
            }

            return $request->user()?->is_admin || $request->user()?->id === User::first()->id;
        });

        Gate::define('viewPulse', function (User $user) {
            return $user->isAdmin();
        });

        Feature::define('date_range', function (User $user) {
            return config('llmdriver.features.date_range'); //just not ready yet
        });

        Feature::define('ollama-functions', function (User $user) {
            return false;
        });

        Feature::define('reference_collection', function (User $user) {
            return config('llmdriver.features.reference_collection'); //just not ready yet
        });

        Feature::define('all_tools', function (User $user) {
            if (config('llmdriver.features.all_tools')) {
                return true;
            }

            return false;
        });

        Feature::define('editor', function (User $user) {
            if (config('llmdriver.features.editor')) {
                return true;
            }

            return false;
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

        $this->app->bind('sitemap_parser', function ($app) {
            return new SitemapParser();
        });

    }
}
