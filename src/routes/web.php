<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LdapController;
use App\Http\Controllers\Auth\LoginController;
use LdapRecord\Models\ActiveDirectory\User;
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

Route::post('/login',[LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// routes/web.php
Route::get('/ldap-debug-ports', function () {




    try {
        $user = (new User)->inside('ou=Users,dc=local,dc=com');

        $user->cn = 'John Doe';
        $user->unicodePwd = 'SecretPassword';
        $user->samaccountname = 'jdoe';
        $user->userPrincipalName = 'jdoe@acme.org';

        $user->save();

// Sync the created users attributes.
        $user->refresh();

// Enable the user.
        $user->userAccountControl = 512;
        $user->save();
    } catch (\LdapRecord\LdapRecordException $e) {
        // Failed saving user.
    }

    return response()->json($results);
});
