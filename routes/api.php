<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeminiController;
use App\Http\Controllers\AuthController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/gemini/receive', [GeminiController::class, 'receiveMessage']);
Route::post('/gemini/reset', [GeminiController::class, 'resetChat']);

Route::post('/login', [AuthController::class, 'login']);
