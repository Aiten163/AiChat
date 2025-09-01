-- Создание базы данных
CREATE DATABASE IF NOT EXISTS `{{MYSQL_DATABASE}}`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Создание пользователя
CREATE USER IF NOT EXISTS '{{MYSQL_USER}}'@'%'
IDENTIFIED BY '{{MYSQL_PASSWORD}}';

-- Предоставление минимально необходимых прав для Laravel
GRANT SELECT, INSERT, UPDATE, DELETE,
CREATE, DROP, INDEX, ALTER,
      CREATE TEMPORARY TABLES,
      LOCK TABLES,
      EXECUTE
ON `{{MYSQL_DATABASE}}`.*
TO '{{MYSQL_USER}}'@'%';

-- - GRANT OPTION (не может давать права другим)
-- - SUPER (административные операции)
-- - PROCESS (просмотр процессов других пользователей)
-- - FILE (чтение/запись файлов на сервере)

FLUSH PRIVILEGES;