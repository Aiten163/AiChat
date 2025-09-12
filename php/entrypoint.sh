#!/bin/sh

cd /var/www/html

# Установка зависимостей Composer
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Настройка .env файла если его нет
if [ ! -f .env ] && [ -f .env.example ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

# Генерация APP_KEY если его нет (пропускаем если нет прав)
if [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env; then
    if touch .env 2>/dev/null; then
        echo "Generating APP_KEY..."
        php artisan key:generate --force
    else
        echo "Warning: Cannot generate APP_KEY - permission denied for .env"
    fi
fi

# Очистка кэша
if [ -f "artisan" ]; then
    echo "Clearing cache..."
    php artisan optimize:clear
fi

# Ожидание базы данных перед миграциями
if [ -f "artisan" ]; then
    echo "Waiting for database to be ready..."

    max_attempts=5
    attempt=1

    while [ $attempt -le $max_attempts ]; do
        if php artisan db:monitor --timeout=2 >/dev/null 2>&1; then
            echo "Database is ready!"
            break
        fi

        echo "Database not ready yet (attempt $attempt/$max_attempts)..."
        sleep 2
        attempt=$((attempt + 1))

        if [ $attempt -gt $max_attempts ]; then
            echo "Warning: Database not ready after $max_attempts attempts, continuing anyway..."
        fi
    done

    # Запуск миграций
    echo "Running migrations..."
    php artisan migrate --force
fi

echo "Starting PHP-FPM..."
exec php-fpm