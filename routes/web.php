<?php

use Illuminate\Support\Facades\Route;

Route::get('/index', [\App\Http\Controllers\MessageController::class, 'index'])->name('index');
Route::post('/send/message', [\App\Http\Controllers\MessageController::class, 'sendMessage'])->name('send.message');

