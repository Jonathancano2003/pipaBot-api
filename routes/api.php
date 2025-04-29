<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeminiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatHistoryController;
use App\Http\Controllers\RegisterController;


Route::post('/register', [RegisterController::class, 'register']);

Route::get('/chats', [ChatHistoryController::class, 'index']);
Route::post('/chats', [ChatHistoryController::class, 'store']);
Route::get('/chats/{id}', [ChatHistoryController::class, 'show']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/gemini/receive', [GeminiController::class, 'receiveMessage']);
Route::post('/gemini/reset', [GeminiController::class, 'resetChat']);
Route::post('/gemini/update-prompt', [GeminiController::class, 'updatePrompt']);
Route::post('/login', [AuthController::class, 'login']);


Route::get('/prompt', [GeminiController::class, 'getPrompt']);
Route::post('/prompt', [GeminiController::class, 'updatePrompt']);
Route::get('/prompt/reset', [GeminiController::class, 'resetPrompt']);
Route::get('/prompt/history', [GeminiController::class, 'getPromptHistory']);
Route::delete('/prompt/{id}', [GeminiController::class, 'deletePrompt']);
