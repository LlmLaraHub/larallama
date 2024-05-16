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

    Route::controller(DownloadController::class)->group(
        function () {
            Route::get('/collections/{collection}/download', 'download')
                ->name('download.document');
        }
    );

    Route::controller(\App\Http\Controllers\OutputController::class)->group(
        function () {
            Route::get('/collections/{collection}/outputs', 'index')
                ->name('collections.outputs.index');
        }
    );

    Route::controller(SourceController::class)->group(
        function () {
            Route::get('/collections/{collection}/sources', 'index')
                ->name('collections.sources.index');

            Route::post('/sources/{source}/run', 'run')
                ->name('collections.sources.run');
        }
    );

    Route::controller(WebSourceController::class)->group(
        function () {
            Route::get('/collections/{collection}/sources/websearch/create', 'create')
                ->name('collections.sources.websearch.create');
            Route::post('/collections/{collection}/sources/websearch', 'store')
                ->name('collections.sources.websearch.store');
            Route::get('/collections/{collection}/sources/websearch/{source}/edit', 'edit')
                ->name('collections.sources.websearch.edit');
            Route::put('/collections/{collection}/sources/websearch/{source}/update', 'update')
                ->name('collections.sources.websearch.update');
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

    Route::get('/dashboard', function () {
        return to_route('collections.index');
        //return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::controller(\App\Http\Controllers\DeleteDocumentsController::class)->group(
        function () {
            Route::post('/documents/delete', 'delete')
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
