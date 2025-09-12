<?php
require_once __DIR__ . '/../vendor/autoload.php';
echo 3;
// Отладочная информация
error_log('Loading .env from: ' . __DIR__ . '/../.env');
// Проверяем существование .env файла
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    error_log('.env loaded successfully');

    // Проверяем загруженные переменные
    $requiredEnvVars = ['LDAP_SERVER', 'LDAP_BASE_DN', 'LDAP_USER_DOMAIN'];
    foreach ($requiredEnvVars as $var) {
        $value = $_ENV[$var] ?? getenv($var) ?? null;
        error_log("$var: " . ($value ? $value : 'NOT SET'));
    }
} else {
    error_log('.env file not found at: ' . __DIR__ . '/../.env');
    // Список файлов в корне для отладки
    $rootFiles = scandir(__DIR__ . '/..');
    error_log('Root files: ' . implode(', ', $rootFiles));
}