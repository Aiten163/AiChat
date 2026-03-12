<?php
namespace App\Services;

use LdapRecord\Connection;
use LdapRecord\Models\ActiveDirectory\User;
use LdapRecord\Models\ActiveDirectory\Group;

class LdapService
{
    public function ldapLogin(string $username, string $password): bool
    {
        try {
            $connection = new Connection(config('ldap.connections.default'));
            $connection->connect();

            $user = User::findByOrFail('samaccountname', $username);
            if (!$connection->auth()->attempt($user->getDn(), $password)) {
                return false;
            }

            $group = Group::findByOrFail('cn', config('ldap.group'));

            return $group->members()->exists($user);
        } catch (\Exception $e) {
            Log::error('LDAP login failed', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
