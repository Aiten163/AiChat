<?php
namespace App\Services;

use LdapRecord\Connection;
use LdapRecord\Models\ActiveDirectory\User;
use LdapRecord\Models\ActiveDirectory\Group;

class LdapService
{
    static function ldapLogin($username, $password, $groupName=null)
    {
        try {
            $connection = new Connection();
            $connection->connect();

            $user = User::findByOrFail('samaccountname', $username);
            if (!$connection->auth()->attempt($user->getDn(), $password)) {
                return false;
            }

            $group = Group::findByOrFail('cn', $groupName);

            return $group->members()->exists($user);

        } catch (\Exception $e) {
            return false;
        }
    }
}
