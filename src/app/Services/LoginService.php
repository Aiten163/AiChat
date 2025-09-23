<?php
namespace App\Services;


use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginService {
    private string $username;
    private string $password;
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function login()
    {
        if(!$this->login_ldap()) {
            return false;
        }
        $user = User::firstOrCreate('name', $this->username);
        Auth::setUser($user);
        return true;
    }

    private function login_ldap():bool
    {
        return true;
    }

}
