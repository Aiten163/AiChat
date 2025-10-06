<?php
namespace App\Services;


use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginService {
    private string $username;
    private string $password;
    private string $group;
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->group = env('LDAP_GROUP','faberlic');
    }

    public function login()
    {

        if (!$this->login_ldap()) {
            return false;
        }

        $user = User::where('name', $this->username)->first();

        if (!$user) {
            $user = User::create([
                'name' => $this->username,
            ]);
        }

        Auth::login($user, true); // true = remember me
        session()->save(); // Принудительно сохраняем сессию


        return Auth::check();
    }

    private function login_ldap():bool
    {
        return true;
        return LdapService::ldapLogin($this->username, $this->password);
    }

}
