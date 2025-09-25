<?php

namespace App\Http\Controllers;

use App\Services\LdapService;
use Illuminate\Http\Request;

class LdapController extends Controller
{
    protected $ldapService;

    public function __construct(LdapService $ldapService)
    {
        $this->ldapService = $ldapService;
    }

    /**
     * Проверка существования пользователя
     */
    public function checkUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string'
        ]);

        $username = $request->input('username');
        $exists = $this->ldapService->userExists($username);

        return response()->json([
            'exists' => $exists,
            'username' => $username
        ]);
    }

    /**
     * Получение информации о пользователе
     */
    public function getUserInfo($username)
    {
        $user = $this->ldapService->getUserInfo($username);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'user' => [
                'name' => $user->getFirstAttribute('cn'),
                'username' => $user->getFirstAttribute('samaccountname'),
                'email' => $user->getFirstAttribute('mail'),
                'department' => $user->getFirstAttribute('department'),
                'title' => $user->getFirstAttribute('title'),
                'phone' => $user->getFirstAttribute('telephoneNumber'),
                'active' => $this->ldapService->isUserActive($user),
                'dn' => $user->getDn()
            ]
        ]);
    }

    /**
     * Аутентификация пользователя
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        $authenticated = $this->ldapService->authenticate($username, $password);

        if ($authenticated) {
            $user = $this->ldapService->getUserInfo($username);

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'user' => [
                    'name' => $user->getFirstAttribute('cn'),
                    'username' => $user->getFirstAttribute('samaccountname'),
                    'email' => $user->getFirstAttribute('mail')
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Authentication failed'
        ], 401);
    }

    /**
     * Поиск пользователей
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $users = $this->ldapService->searchUsers($request->input('query'));

        return response()->json([
            'users' => $users->map(function ($user) {
                return [
                    'name' => $user->getFirstAttribute('cn'),
                    'username' => $user->getFirstAttribute('samaccountname'),
                    'email' => $user->getFirstAttribute('mail'),
                    'department' => $user->getFirstAttribute('department')
                ];
            })
        ]);
    }
}
