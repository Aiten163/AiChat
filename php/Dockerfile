FROM php:8.4-fpm

# Определяем аргументы
ARG UID_HOST
ARG GID_HOST

# Устанавливаем зависимости
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем расширения PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создаем группу и пользователя с переданными UID/GID
RUN groupadd -g ${GID_HOST} www
RUN useradd -u ${UID_HOST} -ms /bin/bash -g www www

# Создаем рабочую директорию
WORKDIR /var/www/html

# Создаем директорию для скриптов
RUN mkdir -p /docker

# Копируем скрипты
COPY php/entrypoint.sh /docker/entrypoint.sh

# Делаем скрипты исполняемыми
RUN chmod +x /docker/entrypoint.sh

# Меняем владельца рабочей директории
RUN chown -R www:www /var/www/html

EXPOSE 9000

# Используем entrypoint
ENTRYPOINT ["/docker/entrypoint.sh"]
CMD ["php-fpm"]