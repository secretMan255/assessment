<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Middleware\VerifyCsrfToken;

Route::post('/login', [AuthController::class, 'login'])
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/notes',           [NoteController::class, 'index']);
    Route::post('/notes',          [NoteController::class, 'store']);
    Route::put('/notes/{note}',    [NoteController::class, 'update']);
    Route::delete('/notes/{note}', [NoteController::class, 'destroy']);
});