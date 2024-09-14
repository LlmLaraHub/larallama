<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ReindexCollectionController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\TextDocumentController;
use App\Http\Controllers\WebSourceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/team-invitations/{invitation}',
    [\App\Http\Controllers\TeamInviteAcceptController::class, 'accept'])
    ->middleware('signed')
    ->name('team-invitations.accept');

Route::get('/login/signed/{token}', [\App\Http\Controllers\SignedUrlAuth::class,
    'signInWithToken'])
    ->name('signed_url.login');

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::controller(\App\Http\Controllers\CalendarController::class)->group(function () {
        Route::get('/calendar/{collection}', 'show')->name('calendar.show');
    });

    Route::get('/auth/google', [\App\Http\Controllers\GoogleController::class, 'redirectToGoogle'])
        ->name('auth.google');
    Route::get('/auth/google/callback', [\App\Http\Controllers\GoogleController::class, 'handleGoogleCallback'])
        ->name('auth.google.callback');

    Route::controller(\App\Http\Controllers\DocumentController::class)->group(
        function () {
            Route::get('/collections/{collection}/documents', 'index')
                ->name('collections.documents.index');
            Route::get('/collections/{collection}/documents/status', 'status')
                ->name('collections.documents.status');
        }
    );

    Route::controller(\App\Http\Controllers\EventsController::class)->group(
        function () {
            Route::get('/collections/{collection}/events', 'index')
                ->name('collections.events.index');
        }
    );

    Route::controller(\App\Http\Controllers\PasswordController::class)->group(
        function () {
            Route::put('/users/password/update', 'update')
                ->name('custom.user-password.update');
        }
    );

    Route::controller(\App\Http\Controllers\OutputController::class)->group(
        function () {

            Route::get('/collections/{collection}/outputs', 'index')
                ->name('collections.outputs.index');

            Route::delete('/outputs/{output:id}/delete', 'delete')
                ->name('collections.outputs.delete');

        }
    );

    Route::controller(DownloadController::class)->group(
        function () {
            Route::get('/collections/{collection}/download', 'download')
                ->name('download.document');
        }
    );

    Route::controller(\App\Http\Controllers\FilterController::class)->group(
        function () {
            Route::post('/collections/{collection}/filters/create',
                'create')
                ->name('filters.create');
            Route::delete('/filters/{filter}/delete',
                'delete')
                ->name('filters.delete');
        }
    );

    Route::controller(SourceController::class)->group(
        function () {
            Route::get('/collections/{collection}/sources', 'index')
                ->name('collections.sources.index');

            Route::post('/sources/{source}/run', 'run')
                ->name('collections.sources.run');

            Route::delete('/sources/{source}/delete', 'delete')
                ->name('collections.sources.delete');
        }
    );

    Route::controller(\App\Http\Controllers\Sources\FeedSourceController::class)->group(
        function () {
            Route::get('/collections/{collection}/sources/feed_source/create', 'create')
                ->name('collections.sources.feed_source.create');
            Route::post('/collections/{collection}/sources/feed_source', 'store')
                ->name('collections.sources.feed_source.store');
            Route::get('/collections/{collection}/sources/feed_source/{source}/edit', 'edit')
                ->name('collections.sources.feed_source.edit');
            Route::put('/collections/{collection}/sources/feed_source/{source}/update', 'update')
                ->name('collections.sources.feed_source.update');
            Route::post('/sources/feed_source/test_feed', 'testFeed')->name('sources.feed_source.test_feed');
        }
    );

    Route::post('/daily-report/{project}', \App\Http\Controllers\DailyReportSendController::class)->name('daily-report.send');

    Route::controller(\App\Http\Controllers\AssistantEmailBoxSourceController::class)->group(
        function () {
            Route::get('/collections/{collection}/sources/email_source/create', 'create')
                ->name('collections.sources.email_source.create');
            Route::post('/collections/{collection}/sources/email_source', 'store')
                ->name('collections.sources.email_source.store');
            Route::get('/collections/{collection}/sources/email_source/{source}/edit', 'edit')
                ->name('collections.sources.email_source.edit');
            Route::put('/collections/{collection}/sources/email_source/{source}/update', 'update')
                ->name('collections.sources.email_source.update');
        }
    );

    Route::controller(\App\Http\Controllers\Sources\EmailBoxSourceController::class)->group(
        function () {
            Route::get('/collections/{collection}/sources/email_box_source/create', 'create')
                ->name('collections.sources.email_box_source.create');
            Route::post('/collections/{collection}/sources/email_box_source', 'store')
                ->name('collections.sources.email_box_source.store');
            Route::get('/collections/{collection}/sources/email_box_source/{source}/edit', 'edit')
                ->name('collections.sources.email_box_source.edit');
            Route::put('/collections/{collection}/sources/email_box_source/{source}/update', 'update')
                ->name('collections.sources.email_box_source.update');
        }
    );

    Route::controller(\App\Http\Controllers\StyleGuideController::class)->group(
        function () {
            Route::get('/style_guides', 'show')
                ->name('style_guide.show');
            Route::post('/style_guides/persona', 'createPersona')
                ->name('style_guide.create.persona');
            Route::put('/style_guides/{persona}/persona', 'updatePersona')
                ->name('style_guide.update.persona');
            Route::post('/style_guides/audience', 'createAudience')
                ->name('style_guide.create.audience');
            Route::put('/style_guides/{audience}/audience', 'updateAudience')
                ->name('style_guide.update.audience');
        }
    );

    Route::controller(\App\Http\Controllers\SettingController::class)->group(
        function () {
            Route::get('/settings', 'show')
                ->name('settings.show');
            Route::put('/settings/{setting}/open_ai', 'updateOpenAi')
                ->name('settings.update.open_ai');
            Route::put('/settings/{setting}/claude', 'updateClaude')
                ->name('settings.update.claude');
            Route::put('/settings/{setting}/ollama', 'updateOllama')
                ->name('settings.update.ollama');
            Route::put('/settings/{setting}/groq', 'updateGroq')
                ->name('settings.update.groq');
            Route::put('/settings/{setting}/fire_crawl', 'updateFireCrawl')
                ->name('settings.update.fire_crawl');
        }
    );

    Route::controller(\App\Http\Controllers\TaskController::class)->group(
        function () {
            Route::get('/tasks/{project}', 'index')->name('tasks.index');
            Route::post('/tasks/{task}/complete', 'markAsComplete')->name('tasks.complete');
        }
    );

    Route::controller(\App\Http\Controllers\ProjectController::class)->group(
        function () {
            Route::get('/projects', 'index')
                ->name('projects.index');
            Route::get('/projects/create', 'create')
                ->name('projects.create');
            Route::post('/projects', 'store')
                ->name('projects.store');
            Route::get('/projects/{project}', 'show')
                ->name('projects.show');
            Route::get('/projects/{project}/chat/{chat}', 'showWithChat')
                ->name('projects.showWithChat');
            Route::get('/projects/{project}/edit', 'edit')
                ->name('projects.edit');
            Route::put('/projects/{project}', 'update')
                ->name('projects.update');
            Route::delete('/projects/{project}', 'destroy')
                ->name('projects.destroy');
            Route::post('/projects/{project}', 'kickOff')
                ->name('projects.kickoff');
            Route::post('/projects/{project}/chat/{chat}', 'chat')->name('project.chat');
        }
    );

    Route::controller(WebSourceController::class)->group(
        function () {
            Route::get('/collections/{collection}/sources/web_search_source/create', 'create')
                ->name('collections.sources.web_search_source.create');
            Route::post('/collections/{collection}/sources/web_search_source', 'store')
                ->name('collections.sources.web_search_source.store');
            Route::get('/collections/{collection}/sources/web_search_source/{source}/edit', 'edit')
                ->name('collections.sources.web_search_source.edit');
            Route::put('/collections/{collection}/sources/web_search_source/{source}/update', 'update')
                ->name('collections.sources.web_search_source.update');
        }
    );

    Route::controller(\App\Http\Controllers\WebPageOutputController::class)->group(
        function () {
            Route::get('/collections/{collection}/outputs/web_page/create', 'create')
                ->name('collections.outputs.web_page.create');
            Route::get('/collections/{collection}/outputs/web_page/{output:id}/edit', 'edit')
                ->name('collections.outputs.web_page.edit');
            Route::post('/collections/{collection}/outputs/web_page', 'store')
                ->name('collections.outputs.web_page.store');
            Route::post('/collections/{collection}/outputs/web_page/summary', 'generateSummaryFromCollection')
                ->name('collections.outputs.web_page.summary');
            Route::put('/collections/{collection}/outputs/web_page/{output:id}/update', 'update')
                ->name('collections.outputs.web_page.update');
        }
    );

    Route::controller(\App\Http\Controllers\Outputs\CalendarOutputController::class)->group(
        function () {
            Route::get('/collections/{collection}/outputs/calendar/create', 'create')
                ->name('collections.outputs.calendar_output.create');
            Route::get('/collections/{collection}/outputs/calendar/{output:id}/edit', 'edit')
                ->name('collections.outputs.calendar_output.edit');
            Route::post('/collections/{collection}/outputs/calendar', 'store')
                ->name('collections.outputs.calendar_output.store');
            Route::put('/collections/{collection}/outputs/calendar/{output:id}/update', 'update')
                ->name('collections.outputs.calendar_output.update');
        }
    );

    Route::controller(\App\Http\Controllers\EmailOutputController::class)->group(
        function () {
            Route::get('/collections/{collection}/outputs/email_output/create', 'create')
                ->name('collections.outputs.email_output.create');
            Route::get('/collections/{collection}/outputs/email_output/{output:id}/edit', 'edit')
                ->name('collections.outputs.email_output.edit');
            Route::post('/collections/{collection}/outputs/email_output', 'store')
                ->name('collections.outputs.email_output.store');
            Route::post('/outputs/{output:id}/send', 'send')
                ->name('collections.outputs.email_output.send');
            Route::put('/collections/{collection}/outputs/email_output/{output:id}/update', 'update')
                ->name('collections.outputs.email_output.update');
        }
    );

    Route::controller(\App\Http\Controllers\ApiOutputController::class)->group(
        function () {
            Route::get('/collections/{collection}/outputs/api_output/create', 'create')
                ->name('collections.outputs.api_output.create');
            Route::get('/collections/{collection}/outputs/api_output/{output:id}/edit', 'edit')
                ->name('collections.outputs.api_output.edit');
            Route::post('/collections/{collection}/outputs/api_output', 'store')
                ->name('collections.outputs.api_output.store');
            Route::put('/collections/{collection}/outputs/api_output/{output:id}/update', 'update')
                ->name('collections.outputs.api_output.update');
        }
    );

    Route::get('/dashboard', function () {
        return to_route('collections.index');
        //return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::controller(\App\Http\Controllers\DeleteDocumentsController::class)->group(
        function () {
            Route::delete('/documents/delete', 'delete')
                ->name('documents.delete');
            Route::delete('/collections/{collection}/documents/delete', 'deleteAll')
                ->name('documents.delete_all');
        }
    );

    Route::controller(ReindexCollectionController::class)->group(
        function () {
            Route::post('/collections/{collection}/reindex', 'reindex')
                ->name('collections.reindex');
        }
    );

    Route::controller(TextDocumentController::class)->group(function () {
        Route::post('/collections/{collection}/text-documents', 'store')
            ->name('text-documents.store');
    });

    Route::controller(\App\Http\Controllers\ReportController::class)->group(function () {
        Route::get('api/reports/{report}', 'show')
            ->name('api.reports.show');
        Route::get('/reports/{report}/export', 'export')
            ->name('reports.export');
    });

    Route::controller(\App\Http\Controllers\SectionsController::class)->group(function () {
        Route::get('api/reports/{report}/sections', 'index')
            ->name('api.sections.index');
    });

    Route::controller(\App\Http\Controllers\ManageBatchesController::class)->group(function () {
        Route::get('/batches', 'index')
            ->name('batches.index');
        Route::post('/batches/cancel-all', 'cancelAll')
            ->name('batches.cancel-all');
        Route::post('/batches/{batchId}', 'cancel')
            ->name('batches.cancel');
    });

    Route::controller(CollectionController::class)->group(function () {
        Route::get('/collections', 'index')->name('collections.index');
        Route::post('/collections/{collection}/documents/{document}/reset', 'resetCollectionDocument')
            ->name('collections.documents.reset');
        Route::post('/collections', 'store')->name('collections.store');
        Route::put('/collections/{collection}', 'update')->name('collections.update');
        Route::delete('/collections/{collection}', 'delete')->name('collections.delete');
        Route::get('/collections/{collection}', 'show')->name('collections.show');
        Route::any('/collections/{collection}/upload', 'filesUpload')->name('collections.upload');
    });

    Route::controller(ChatController::class)->group(function () {
        Route::post('/collections/{collection}/chats', 'storeCollectionChat')->name('chats.collection.store');
        Route::get('/collections/{collection}/chats/{chat}', 'showCollectionChat')->name('chats.collection.show');

        Route::get('/collections/{collection}/chats/{chat}/messages/latest', 'latestChatMessage')->name('chats.collection.latest');

        Route::post('/chats/{chat}/messages/create', 'chat')
            ->name('chats.messages.create');
        Route::delete('/chats/{message}/delete', 'deleteMessage')->name('chats.messages.delete');
    });

    Route::controller(\App\Http\Controllers\ReRunController::class)->group(function () {
        Route::post('/messages/{message}/rerun', 'rerun')->name('messages.rerun');
    });

    Route::controller(\App\Http\Controllers\UpdateSummaryController::class)->group(function () {
        Route::post('/documents/{document}/update-summary', 'updateSummary')
            ->name('collections.documents.update-summary');
    });

    Route::controller(\App\Http\Controllers\Sources\WebhookSourceController::class)->group(
        function () {
            Route::get('/collections/{collection}/sources/webhook_source/create', 'create')
                ->name('collections.sources.webhook_source.create');
            Route::post('/collections/{collection}/sources/webhook_source', 'store')
                ->name('collections.sources.webhook_source.store');
            Route::get('/collections/{collection}/sources/webhook_source/{source}/edit', 'edit')
                ->name('collections.sources.webhook_source.edit');
            Route::put('/collections/{collection}/sources/webhook_source/{source}/update', 'update')
                ->name('collections.sources.webhook_source.update');
        }
    );

    Route::controller(\App\Http\Controllers\Sources\JsonSourceController::class)->group(
        function () {
            Route::get('/collections/{collection}/sources/json_source/create', 'create')
                ->name('collections.sources.json_source.create');
            Route::post('/collections/{collection}/sources/json_source', 'store')
                ->name('collections.sources.json_source.store');
            Route::get('/collections/{collection}/sources/json_source/{source}/edit', 'edit')
                ->name('collections.sources.json_source.edit');
            Route::put('/collections/{collection}/sources/json_source/{source}/update', 'update')
                ->name('collections.sources.json_source.update');
        }
    );

    Route::controller(\App\Http\Controllers\Sources\WebPageSourceController::class)->group(
        function () {
            Route::get('/collections/{collection}/sources/web_page_source/create', 'create')
                ->name('collections.sources.web_page_source.create');
            Route::post('/collections/{collection}/sources/web_page_source', 'store')
                ->name('collections.sources.web_page_source.store');
            Route::get('/collections/{collection}/sources/web_page_source/{source}/edit', 'edit')
                ->name('collections.sources.web_page_source.edit');
            Route::put('/collections/{collection}/sources/web_page_source/{source}/update', 'update')
                ->name('collections.sources.web_page_source.update');
        }
    );

    Route::controller(\App\Http\Controllers\Outputs\EmailReplyOutputController::class)->group(
        function () {
            Route::get('/collections/{collection}/outputs/email_reply_output/{output:id}/edit', 'edit')
                ->name('collections.outputs.email_reply_output.edit');
            Route::get('/collections/{collection}/outputs/email_reply_output/create', 'create')
                ->name('collections.outputs.email_reply_output.create');
            Route::post('/collections/{collection}/outputs/email_reply_output', 'store')
                ->name('collections.outputs.email_reply_output.store');
            Route::put('/collections/{collection}/outputs/email_reply_output/{output:id}/update', 'update')
                ->name('collections.outputs.email_reply_output.update');
            Route::post('/outputs/{output:id}/check', 'check')
                ->name('collections.outputs.email_reply_output.check');
        }
    );
});

Route::get('/pages/{output}', [
    \App\Http\Controllers\WebPageOutputController::class, 'show',
])
    ->name('collections.outputs.web_page.show');

Route::post('/pages/{output:id}/chat', [
    \App\Http\Controllers\WebPageOutputController::class, 'chat',
])
    ->name('collections.outputs.web_page.chat');
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::controller(\App\Http\Controllers\Sources\SiteMapSourceController::class)->group(
        function () {
            Route::get('/collections/{collection}/sources/site_map_source/create', 'create')
                ->name('collections.sources.site_map_source.create');
            Route::post('/collections/{collection}/sources/site_map_source', 'store')
                ->name('collections.sources.site_map_source.store');
            Route::get('/collections/{collection}/sources/site_map_source/{source}/edit', 'edit')
                ->name('collections.sources.site_map_source.edit');
            Route::put('/collections/{collection}/sources/site_map_source/{source}/update', 'update')
                ->name('collections.sources.site_map_source.update');
            Route::post('/sources/site_map_source/test_feed', 'testFeed')->name('sources.site_map_source.test_feed');
        }
    );
});
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::controller(\App\Http\Controllers\Sources\GoogleSheetSourceController::class)->group(
        function () {
            Route::get('/collections/{collection}/sources/google_sheet_source/create', 'create')
                ->name('collections.sources.google_sheet_source.create');
            Route::post('/collections/{collection}/sources/google_sheet_source', 'store')
                ->name('collections.sources.google_sheet_source.store');
            Route::get('/collections/{collection}/sources/google_sheet_source/{source}/edit', 'edit')
                ->name('collections.sources.google_sheet_source.edit');
            Route::put('/collections/{collection}/sources/google_sheet_source/{source}/update', 'update')
                ->name('collections.sources.google_sheet_source.update');
            Route::post('/sources/google_sheet_source/test_feed', 'testFeed')->name('sources.google_sheet_source.test_feed');
        }
    );
});
