<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginService $loginService
    ) {}

    public function login(LoginRequest $request): RedirectResponse
    {
        $this->loginService->login(
            $request->input('name'),
            $request->input('password')
        );

        return redirect()->route('home');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->loginService->logout();
        return redirect()->route('home');
    }
}
