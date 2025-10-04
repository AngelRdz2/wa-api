<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\MessageController;

// ✅ Webhook para recibir mensajes desde WAAPI
Route::post('/waapi-webhook', [WebhookController::class, 'handle']);

// ✅ Ruta para responder mensajes manualmente
Route::post('/messages/reply', [MessageController::class, 'reply'])->name('messages.reply');

// ✅ Ruta para mostrar respuestas en tu panel
Route::get('/respuestas', [MessageController::class, 'showResponses'])->name('messages.responses');

// ✅ Ruta de prueba para verificar que la API está viva
Route::get('/test', function () {
    return response()->json(['message' => '¡Funciona la API!']);
});
