<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserActivity;
use App\Services\LdapService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class LoginService
{
    public function __construct(
        private readonly LdapService $ldapService,
        private readonly UserActivity $userActivity
    ) {}

    public function login(string $username, string $password): bool
    {
        if (!$this->authenticateLdap($username, $password)) {
            throw ValidationException::withMessages([
                'name' => ['Неверные учетные данные']
            ]);
        }

        $user = $this->findOrCreateUser($username);

        Auth::login($user, true);
        session()->regenerate();

        $this->userActivity->updateLastLogin($user->id);

        return Auth::check();
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    private function authenticateLdap(string $username, string $password): bool
    {
        if (Config::get('ldap.test_mode', false)) {
            return true;
        }

        return $this->ldapService->ldapLogin($username, $password);
    }

    private function findOrCreateUser(string $username): User
    {
        return User::firstOrCreate(
            ['name' => $username],
            ['name' => $username]
        );
    }
}
