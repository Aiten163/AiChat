<?php

use App\Http\Controllers\OllamaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LdapController;
use App\Http\Controllers\Auth\LoginController;
use LdapRecord\Models\ActiveDirectory\User;
Route::get('/', function () {
    return view('main');
});


Route::post('/login',[LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::post('/postRequest', [OllamaController::class, 'postRequest']);
