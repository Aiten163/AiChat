<?php
require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/LoginLdap.php';
echo("Service Account DN: " . $_ENV['LDAP_LOGIN']);
echo("Service Account Password: " . $_ENV['LDAP_PASSWORD']);
echo("Trying to bind with service account...");
echo '<br>';
use src\LoginLdap;

$ldap = new LoginLdap(
    $_ENV['LDAP_HOST'],
    $_ENV['LDAP_PORT'],
    $_ENV['LDAP_BASE_DN'],
    $_ENV['LDAP_LOGIN_ATTRIBUTE']
);
$ldap->setServiceAccount(
    $_ENV['LDAP_LOGIN'],
    $_ENV['LDAP_PASSWORD']
);
try {
    if ($ldap->connect()) {
        echo "Подключение к LDAP успешно установлено\n";

        $result = $ldap->authenticate('e.bickchurin', 'Qweasdzxc1234', 'GptUsers');

        if ($result['success']) {
            echo "Аутентификация успешна!\n";
            echo "User DN: " . $result['userDn'] . "\n";
            echo "User Data: " . print_r($result['userData'], true) . "\n";

            session_start();
            $_SESSION['user'] = $result['userData'];
            $_SESSION['loggedin'] = true;

        } else {
            echo "Ошибка аутентификации: " . $result['error'] . "\n";
        }
        $ldap->disconnect();
    }
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}