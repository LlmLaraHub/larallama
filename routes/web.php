<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ExampleChatBotController;
use App\Http\Controllers\ExampleController;
use App\Http\Controllers\ReindexCollectionController;
use App\Http\Controllers\TextDocumentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
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

    Route::controller(ExampleChatBotController::class)->group(function () {
        Route::get('/examples/chatbot', 'show')->name('example.chatbot.show');
        Route::put('/examples/chat', 'chat')->name('example.chatbot.chat');
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
Route::controller(ExampleController::class)->group(function () {
    Route::get('/examples/charts', 'charts')->name('example.charts');
});
