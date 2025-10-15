<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OllamaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use LdapRecord\Models\ActiveDirectory\User;
Route::get('/{chatId?}',[HomeController::class, 'index'])->whereNumber('chatId' )->name('home');

Route::get('/getHistoryChat',[ HomeController::class, 'getHistoryChat']);

Route::post('/login',[\App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::post('/postRequest', [OllamaController::class, 'postRequest'])->middleware('auth')->name('postRequest');
