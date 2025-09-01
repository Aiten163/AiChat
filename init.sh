#!/bin/bash

# Создание папки src если её нет
mkdir -p src

# Проверка существования Laravel
if [ ! -f src/composer.json ]; then
    echo "Установка Laravel в папку src..."
    docker run --rm -v $(pwd)/src:/app composer create-project laravel/laravel . --prefer-dist --no-scripts

    # Копирование composer.json в корень для Docker
    cp src/composer.json .
fi

# Копирование .env если его нет в src
if [ ! -f src/.env ]; then
    if [ -f .env ]; then
        cp .env src/.env
    else
        cp src/.env.example .env
        cp .env src/.env
    fi
fi

# Запуск контейнеров
echo "Запуск Docker контейнеров..."
docker-compose up -d --build

# Ожидание готовности MySQL
echo "Ожидание готовности MySQL..."
sleep 10

# Выполнение composer скриптов после установки
echo "Выполнение post-install скриптов..."
docker-compose exec php composer run-script post-autoload-dump

# Установка Orchid (если еще не установлен)
if ! grep -q "orchid/platform" composer.json; then
    echo "Установка Orchid..."
    docker-compose exec php composer require orchid/platform --no-scripts
fi

# Генерация ключа приложения
echo "Генерация ключа приложения..."
docker-compose exec php php artisan key:generate

# Копирование сгенерированного ключа обратно в корневой .env
docker-compose exec php cat .env > .env.tmp
mv .env.tmp .env
cp .env src/.env

# Установка Orchid
echo "Установка Orchid..."
docker-compose exec php php artisan orchid:install

# Миграции
echo "Выполнение миграций..."
docker-compose exec php php artisan migrate

# Создание администратора
echo "Создание администратора..."
docker-compose exec php php artisan orchid:admin

echo "Проект готов! Откройте http://localhost:${NGINX_PORT:-80}/admin"