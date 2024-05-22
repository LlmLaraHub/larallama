<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(\App\Http\Controllers\ApiOutputController::class)->group(
    function () {
        Route::post('/outputs/{output:id}/chat', 'api')
            ->name('collections.outputs.api_output.api')
            ->middleware(\App\Http\Middleware\ApiOutputTokenIsValid::class);
    }
);
