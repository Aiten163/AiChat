<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use LdapRecord\Models\ActiveDirectory\User;
Route::get('/{chatId?}',[HomeController::class, 'index'])->whereNumber('chatId' )->name('home');
Route::get('/new-chat', function () {
    return redirect('/');
});
Route::get('/login', function () {
    return redirect('/');
});

Route::get('/getHistoryChat',[ HomeController::class, 'getHistoryChat']);

Route::post('/login',[\App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::post('/postRequest', [OllamaController::class, 'postRequest'])->name('postRequest');
    // Переименовать чат
    Route::post('/chats/{chat}/rename', [ChatController::class, 'rename'])->name('chats.rename');

    // Удалить чат
    Route::delete('/chats/{chat}', [ChatController::class, 'destroy'])->name('chats.destroy');
});
