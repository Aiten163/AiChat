<?php

use LdapRecord\Models\ActiveDirectory\User;
use LdapRecord\Models\ActiveDirectory\Group;

class LdapService
{
    public function findUser($username)
    {
        // Поиск по различным атрибутам
        return User::where('samaccountname', '=', $username)
            ->orWhere('cn', '=', $username)
            ->orWhere('userprincipalname', '=', $username)
            ->first();
    }

    public function isUserInGroup($username, $groupName)
    {
        $user = $this->findUser($username);

        if (!$user) {
            return false;
        }

        // Поиск группы по имени
        $group = Group::where('cn', '=', $groupName)->first();

        return $group && $user->groups()->exists($group);
    }
}

// Использование
$ldapService = new LdapService();

// Динамический поиск любого пользователя
$user = $ldapService->findUser('vlad.levin');
$inGroup = $ldapService->isUserInGroup('vlad.levin', 'faberlic');
