#!/bin/sh

cd /var/www/html

# Установка зависимостей Composer
if [ ! -d "vendor" ] && [ -f "composer.json" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Настройка .env файла
if [ ! -f .env ] && [ -f .env.example ]; then
    echo "Creating .env file..."
    cp .env.example .env
    php artisan key:generate --force
fi

# Очистка кэша и миграции
if [ -f "artisan" ]; then
    echo "Optimizing Laravel..."
    php artisan optimize:clear
    php artisan migrate --force
fi

echo "Starting PHP-FPM..."
exec php-fpm