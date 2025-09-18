<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LdapController;
use App\Http\Controllers\ldap;

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
Route::get('/test', [Ldap::class, 'exists']);


Route::get('/test-ldap', function () {
    try {
        $connection = new \LdapRecord\Connection([
            'hosts' => [env('LDAP_HOST')],
            'port' => env('LDAP_PORT', 389),
            'base_dn' => env('LDAP_BASE_DN'),
            'username' => env('LDAP_USERNAME'),
            'password' => env('LDAP_PASSWORD'),
            'use_ssl' => env('LDAP_SSL', false),
            'use_tls' => env('LDAP_TLS', false),
            'timeout' => env('LDAP_TIMEOUT', 5),
        ]);

        $connection->connect();

        return response()->json([
            'success' => true,
            'message' => 'Connected to LDAP server successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'LDAP connection failed: ' . $e->getMessage()
        ], 500);
    }
});


Route::get('/ldap-t', function () {
    try {
        $connection = new \LdapRecord\Connection([
            'hosts' => [env('LDAP_HOST')],
            'port' => env('LDAP_PORT', 389),
            'base_dn' => env('LDAP_BASE_DN'),
            'username' => env('LDAP_USERNAME'),
            'password' => env('LDAP_PASSWORD'),
        ]);

        $connection->connect();

        // Простой поиск пользователей
        $query = $connection->query();
        $users = $query->where('objectclass', '=', 'inetOrgPerson')
            ->select(['cn', 'uid', 'mail'])
            ->limit(5)
            ->get();

        // Обрабатываем массив без map()
        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = [
                'name' => $user->getFirstAttribute('cn'),
                'username' => $user->getFirstAttribute('uid'),
                'email' => $user->getFirstAttribute('mail'),
                'dn' => $user->getDn()
            ];
        }

        return response()->json([
            'connection' => 'success',
            'users_count' => count($users),
            'users' => $usersData
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'connection' => 'failed',
            'error' => $e->getMessage()
        ], 500);
    }
});
