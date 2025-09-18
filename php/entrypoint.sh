#!/bin/sh

cd /var/www/html

echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

if [ ! -f .env ] && [ -f .env.example ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

if [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env; then
    if touch .env 2>/dev/null; then
        echo "Generating APP_KEY..."
        php artisan key:generate --force
    else
        echo "Warning: Cannot generate APP_KEY - permission denied for .env"
    fi
fi

if [ -f "artisan" ]; then
    echo "Clearing cache..."
    php artisan optimize:clear
fi

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
    done

    echo "Running migrations..."
    php artisan migrate --force
fi

# Меняем владельца файлов (на всякий случай)
chown -R www:www /var/www/html

echo "Starting PHP-FPM..."
exec php-fpm