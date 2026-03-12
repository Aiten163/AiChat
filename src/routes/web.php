<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth')->group(function () {
    Route::prefix('chats')->name('chats.')->group(function () {
        Route::get('/history', [HomeController::class, 'getHistoryChat'])->name('history');
        Route::post('/', [OllamaController::class, 'postRequest'])->name('store');
        Route::post('{chat}/rename', [ChatController::class, 'rename'])->name('rename');
        Route::delete('{chat}', [ChatController::class, 'destroy'])->name('destroy');
    });

    Route::get('/new-chat', fn() => redirect()->route('home'))->name('chat.new');

    Route::get('/{chatId?}', [HomeController::class, 'index'])
        ->where('chatId', '[0-9]+')  // Более явное условие
        ->name('chat.show');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
