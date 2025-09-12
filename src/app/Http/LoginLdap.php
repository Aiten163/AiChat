<?php

namespace src;

class LoginLdap {
    private string $ldapHost;
    private int $ldapPort;
    private string $baseDn;
    private string $loginAttribute;
    private $connection;
    private bool $isConnected = false;
    private ?string $serviceUserDn = null;
    private ?string $servicePassword = null;
    private bool $useServiceAccount = true;


    public function __construct(string $host = 'localhost', int $port = 389,
                                string $baseDn = 'ou=People,dc=example,dc=com',
                                string $loginAttribute = 'uid') {
        $this->ldapHost = $host;
        $this->ldapPort = $port;
        $this->baseDn = $baseDn;
        $this->loginAttribute = $loginAttribute;
    }

    /**
     * Установка учетных данных привилегированного пользователя
     */
    public function setServiceAccount(string $userDn, string $password): void {
        $this->serviceUserDn = $userDn;
        $this->servicePassword = $password;
    }

    public function connect(): bool {
        try {
            $this->connection = ldap_connect($this->ldapHost . ':' . $this->ldapPort);
            if (!$this->connection) {
                throw new \Exception("Не удалось подключиться к LDAP-серверу");
            }

            ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);

            $this->isConnected = true;
            return true;

        } catch (\Exception $e) {
            $this->isConnected = false;
            throw new \Exception("Ошибка подключения LDAP: " . $e->getMessage());
        }
    }

    /**
     * Выполняет bind с использованием service account или анонимно
     */
    private function bindForSearch(): bool {
        if ($this->useServiceAccount && $this->serviceUserDn && $this->servicePassword) {
            return @ldap_bind($this->connection, $this->serviceUserDn, $this->servicePassword);
        } else {
            return @ldap_bind($this->connection);
        }
    }

    public function authenticate(string $username, string $password, string $groupCn = null): array {
        if (!$this->isConnected) {
            throw new \Exception("Нет подключения к LDAP-серверу");
        }

        try {
            // Bind для поиска (service account)
            if (!$this->bindForSearch()) {
                $error = ldap_error($this->connection);
                throw new \Exception("Ошибка bind для поиска: " . $error);
            }

            // Базовый фильтр для пользователя
            $searchFilter = "(" . $this->loginAttribute . "=" . $this->escapeLdapFilter($username) . ")";

            // Если указана группа, добавляем условие
            if ($groupCn !== null) {
                $groupDn = "CN=" . $this->escapeLdapFilter($groupCn) . "," . $this->baseDn;
                $searchFilter = "(&" . $searchFilter . "(memberOf=" . $this->escapeLdapFilter($groupDn) . "))";
            }

            error_log("LDAP Search Filter: " . $searchFilter); // Для отладки

            $searchResult = @ldap_search($this->connection, $this->baseDn, $searchFilter);

            if (!$searchResult) {
                $error = ldap_error($this->connection);
                throw new \Exception("Ошибка поиска пользователя: " . $error);
            }

            $entries = ldap_get_entries($this->connection, $searchResult);

            if ($entries['count'] === 0) {
                if ($groupCn !== null) {
                    throw new \Exception("Пользователь не найден или не состоит в группе " . $groupCn);
                } else {
                    throw new \Exception("Пользователь не найден");
                }
            }

            if ($entries['count'] > 1) {
                throw new \Exception("Найдено несколько пользователей");
            }

            $userDn = $entries[0]['dn'];
            $userData = $this->extractUserData($entries[0]);

            // Проверка пароля
            $passwordVerified = $this->verifyPassword($userDn, $password);

            if (!$passwordVerified) {
                throw new \Exception("Неверный пароль");
            }

            return [
                'success' => true,
                'userDn' => $userDn,
                'userData' => $userData
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function verifyPassword(string $userDn, string $password): bool {
        // Создаем новое соединение для проверки пароля
        $verifyConnection = ldap_connect($this->ldapHost, $this->ldapPort);
        ldap_set_option($verifyConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($verifyConnection, LDAP_OPT_REFERRALS, 0);

        try {
            $bindResult = @ldap_bind($verifyConnection, $userDn, $password);
            ldap_unbind($verifyConnection);
            return $bindResult;
        } catch (\Exception $e) {
            ldap_unbind($verifyConnection);
            return false;
        }
    }

    private function extractUserData(array $ldapEntry): array {
        $userData = [];

        foreach ($ldapEntry as $key => $value) {
            if (!is_numeric($key) && $key !== 'count' && is_array($value) && isset($value[0])) {
                $userData[$key] = $value[0];
            }
        }

        return $userData;
    }

    private function escapeLdapFilter(string $value): string {
        $charsToEscape = ['\\', '*', '(', ')', "\0"];
        $escapedValue = '';

        for ($i = 0; $i < strlen($value); $i++) {
            $char = $value[$i];
            if (in_array($char, $charsToEscape)) {
                $escapedValue .= '\\' . dechex(ord($char));
            } else {
                $escapedValue .= $char;
            }
        }

        return $escapedValue;
    }

    public function disconnect(): void {
        if ($this->isConnected && $this->connection) {
            ldap_unbind($this->connection);
            $this->isConnected = false;
        }
    }

    public function isConnected(): bool {
        return $this->isConnected;
    }

    public function isUsingServiceAccount(): bool {
        return $this->useServiceAccount;
    }

    public function __destruct() {
        $this->disconnect();
    }

}