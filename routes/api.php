<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::post('/messages/reply', [\App\Http\Controllers\MessageController::class, 'reply'])->name('messages.reply');

Route::get('/respuestas', [\App\Http\Controllers\MessageController::class, 'showResponses'])->name('messages.responses');
Route::get('/test', function () {
    return response()->json(['message' => '¡Funciona la API!']);
});
