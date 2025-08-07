<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookController;

Route::post('/waapi/webhook', [WebhookController::class, 'handle']);

// 🔐 Rutas públicas de autenticación
require __DIR__.'/auth.php';

// 🔒 Grupo de rutas protegidas (requiere login)
Route::middleware('auth')->group(function () {

    // 🚪 Dashboard principal
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('can:send-message')->name('dashboard');


    // 🧾 Plantillas de mensaje
    Route::get('/plantillas', [MessageController::class, 'verPlantillas'])->name('templates.index');
    Route::post('/plantillas', [MessageController::class, 'guardarPlantilla'])->name('templates.store');

    // 📤 Subir Excel
    Route::get('/subir-excel', [MessageController::class, 'formUploadExcel'])->name('excel.upload');
    Route::post('/subir-excel', [MessageController::class, 'subirExcel'])->name('subir.excel');

    // 💬 Enviar mensajes
    Route::get('/enviar-mensajes', [MessageController::class, 'formSendMessages'])->name('messages.sendForm');
    Route::post('/send-messages', [MessageController::class, 'sendMessage'])->name('messages.send');

    // 👁️ Vista previa de mensajes
    Route::get('/messages/preview', [MessageController::class, 'previewMessages'])->name('messages.preview');

    // 📥 Ver respuestas de clientes (usando el controlador)
    Route::get('/respuestas', [MessageController::class, 'showResponses'])->name('messages.responses');

    // 📤 Responder a un cliente
    Route::post('/messages/reply', [MessageController::class, 'reply'])->name('messages.reply');

    // 👤 Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('users/index', [\App\Http\Controllers\UserController::class, 'index'])->middleware('can:manage-users')->name('users.index');
    Route::post('users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::put('users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

});

// 🔀 Redirigir la raíz al login (o puedes redirigir al dashboard si ya está logueado)
Route::get('/', function () {
    return redirect()->route('login');
});
