<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OllamaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use LdapRecord\Models\ActiveDirectory\User;
use App\Http\Controllers\FanoController;
Route::get('/{chatId?}',[HomeController::class, 'index'])->whereNumber('chatId' )->name('home');

Route::get('/getHistoryChat',[ HomeController::class, 'getHistoryChat']);

Route::post('/login',[\App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::post('/postRequest', [OllamaController::class, 'postRequest'])->middleware('auth')->name('postRequest');


Route::get('/encode', [FanoController::class, 'showEncodePage'])->name('encode');

// Страница декодирования
Route::get('/decode', [FanoController::class, 'showDecodePage'])->name('decode');

// API маршруты для работы с кодированием Фано
Route::prefix('fano')->group(function () {
    // Кодирование текста
    Route::post('/encode', [FanoController::class, 'encode'])->name('fano.encode');

    // Получение закодированных данных
    Route::get('/encoded-data', [FanoController::class, 'getEncodedData'])->name('fano.encoded-data');

    // Получение информации о данных
    Route::get('/info', [FanoController::class, 'getInfo'])->name('fano.info');

    // Удаление данных
    Route::delete('/clear', [FanoController::class, 'clearData'])->name('fano.clear');
});
