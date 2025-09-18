<?php

namespace App\Services;

use App\Models\LdapUser;
use Illuminate\Support\Collection;
use LdapRecord\Auth\BindException;
use LdapRecord\Connection;
use LdapRecord\Models\Attributes\AccountControl;
use Illuminate\Support\Facades\Log;

class LdapService
{
    protected $connection;

    public function __construct()
    {
        $this->connection = new Connection(config('ldap.connections.default'));
    }


    /**
     * Проверяет существование пользователя в LDAP
     */
    public function userExists($username, $attribute = 'samaccountname')
    {
        try {
            return !is_null(LdapUser::where($attribute, $username)->first());
        } catch (\Exception $e) {
            Log::error('LDAP User Search Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Получает информацию о пользователе
     */
    public function getUserInfo($username, $attribute = 'samaccountname')
    {
        try {
            return LdapUser::where($attribute, $username)->first();
        } catch (\Exception $e) {
            Log::error('LDAP User Info Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Аутентификация пользователя в LDAP
     */
    public function authenticate($username, $password): bool
    {
        try {
            $user = LdapUser::where('samaccountname', $username)->first();

            if (!$user) {
                return false;
            }

            // Пробуем привязаться с учетными данными пользователя
            $this->connection->setUsername($user->getDn());
            $this->connection->setPassword($password);
            $this->connection->connect();

            return true;

        } catch (BindException $e) {
            Log::warning('LDAP Authentication Failed: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error('LDAP Authentication Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Поиск пользователей по различным критериям
     */
    public function searchUsers($searchTerm): Collection
    {
        try {
            return LdapUser::where('cn', 'contains', $searchTerm)
                ->orWhere('samaccountname', 'contains', $searchTerm)
                ->orWhere('mail', 'contains', $searchTerm)
                ->get();
        } catch (\Exception $e) {
            Log::error('LDAP Search Error: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Проверяет, активен ли пользователь
     */
    public function isUserActive($user): mixed
    {
        if (!$user->hasAttribute('userAccountControl')) {
            return true;
        }

        $accountControl = new AccountControl(
            $user->getFirstAttribute('userAccountControl')
        );

        return !$accountControl->has(AccountControl::ACCOUNTDISABLE);
    }
}
