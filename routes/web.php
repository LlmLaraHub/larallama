<?php

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ExampleChatBotController;
use App\Http\Controllers\ExampleController;
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
    Route::get('/dashboard', function () {
        return to_route('collections.index');
        //return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::controller(ExampleChatBotController::class)->group(function () {
        Route::get('/examples/chatbot', 'show')->name('example.chatbot.show');
        Route::put('/examples/chat', 'chat')->name('example.chatbot.chat');
    });

    Route::controller(CollectionController::class)->group(function () {
        Route::get('/collections', 'index')->name('collections.index');
        Route::post('/collections', 'store')->name('collections.store');
        Route::get('/collections/{collection}', 'show')->name('collections.show');
        Route::any('/collections/{collection}/upload', 'filesUpload')->name('collections.upload');
    });

});
Route::controller(ExampleController::class)->group(function () {
    Route::get('/examples/charts', 'charts')->name('example.charts');
});
