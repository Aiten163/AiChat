<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LdapController;
use App\Http\Controllers\Auth\LoginController;
Route::get('/', function () {
    return view('main');
});

Route::prefix('ldap')->group(function () {
    Route::get('/check/{username}', [LdapController::class, 'checkUser']);
    Route::get('/user/{username}', [LdapController::class, 'getUserInfo']);
    Route::post('/check', [LdapController::class, 'checkUser']);
    Route::post('/authenticate', [LdapController::class, 'authenticate']);
    Route::get('/search', [LdapController::class, 'search']);
});

Route::get('/login',[LoginController::class, 'login']);

