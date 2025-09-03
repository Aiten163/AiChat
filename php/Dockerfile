FROM php:8.4-fpm
RUN umask 0002
# Установка зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# Очистка кеша
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка расширений PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создание пользователя для приложения
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Создание рабочей директории
WORKDIR /var/www/html

# Копирование скрипта точки входа ДО смены пользователя
COPY php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Смена текущего пользователя на www
USER www

# Открытие порта 9000
EXPOSE 9000

# Использование скрипта точки входа (ПРАВИЛЬНЫЙ ПУТЬ)
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]