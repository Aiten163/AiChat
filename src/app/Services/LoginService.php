<?php
namespace App\Services;


use App\Models\User;
use App\Models\UserActivity;
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

        Auth::login($user, true);
        session()->save();

        UserActivity::updateLastLogin(Auth::id());
        return Auth::check();
    }

    private function login_ldap():bool
    {
        if (config('ldap.test_mode', false))
        {
            return true;
        }
        return LdapService::ldapLogin($this->username, $this->password);
    }

}
