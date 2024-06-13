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

    Route::controller(CollectionController::class)->group(function () {
        Route::get('/collections', 'index')->name('collections.index');
        Route::post('/collections/{collection}/documents/{document}/reset', 'resetCollectionDocument')
            ->name('collections.documents.reset');
        Route::post('/collections', 'store')->name('collections.store');
        Route::put('/collections/{collection}', 'update')->name('collections.update');
        Route::get('/collections/{collection}', 'show')->name('collections.show');
        Route::any('/collections/{collection}/upload', 'filesUpload')->name('collections.upload');
    });

    Route::controller(ChatController::class)->group(function () {
        Route::post('/collections/{collection}/chats', 'storeCollectionChat')->name('chats.collection.store');
        Route::get('/collections/{collection}/chats/{chat}', 'showCollectionChat')->name('chats.collection.show');
        Route::post('/chats/{chat}/messages/create', 'chat')
            ->name('chats.messages.create');
    });

});

Route::get('/pages/{output}', [
    \App\Http\Controllers\WebPageOutputController::class, 'show',
])
    ->name('collections.outputs.web_page.show');

Route::post('/pages/{output:id}/chat', [
    \App\Http\Controllers\WebPageOutputController::class, 'chat',
])
    ->name('collections.outputs.web_page.chat');

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

Route::controller(\App\Http\Controllers\Sources\FooBarController::class)->group(
    function () {
        Route::get('/collections/{collection}/sources/foo_bar/create', 'create')
            ->name('collections.sources.foo_bar.create');
        Route::post('/collections/{collection}/sources/foo_bar', 'store')
            ->name('collections.sources.foo_bar.store');
        Route::get('/collections/{collection}/sources/foo_bar/{source}/edit', 'edit')
            ->name('collections.sources.foo_bar.edit');
        Route::put('/collections/{collection}/sources/foo_bar/{source}/update', 'update')
            ->name('collections.sources.foo_bar.update');
    }
);
