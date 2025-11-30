<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use LdapRecord\Models\ActiveDirectory\User;
use App\Http\Controllers\ReportController;
Route::get('/debug/reports/image/{filename}', [App\Http\Controllers\ReportController::class, 'showImage'])
    ->name('debug.reports.image');
Route::get('/login', function () {
    return redirect('/');
});
Route::post('/login',[\App\Http\Controllers\AuthController::class, 'login'])->name('login');


Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/getHistoryChat',[ HomeController::class, 'getHistoryChat']);
    Route::get('/{chatId?}',[HomeController::class, 'index'])->whereNumber('chatId' )->name('home');
    Route::get('/new-chat', function () {
        return redirect('/');
    });
    Route::post('/postRequest', [OllamaController::class, 'postRequest'])->name('postRequest');
    Route::post('/chats/{chat}/rename', [ChatController::class, 'rename'])->name('chats.rename');
    Route::delete('/chats/{chat}', [ChatController::class, 'destroy'])->name('chats.destroy');

});
