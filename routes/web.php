<?php


use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('index');
})->name('index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ Esta es la ruta que faltaba
   Route::post('/send/message', [MessageController::class, 'sendMessage'])->name('send.message');
});

require __DIR__.'/auth.php';

//Route::get('/index', function () {
 //   return view('index');
//})->middleware('auth')->name('index');
