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
    if(auth()->check()) {
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
        }
    );

    Route::get('/dashboard', function () {
        return to_route('collections.index');
        //return Inertia::render('Dashboard');
    })->name('dashboard');

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
