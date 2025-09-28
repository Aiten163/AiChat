<?php

namespace App\Http\Controllers;

use App\Services\LoginService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(request $request)
    {
        $username = $request->get('name');
        $password = $request->get('password');

        $login = new LoginService($username, $password);
        $login->login();

        return redirect('/');
    }
}
