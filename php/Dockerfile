FROM php:8.4-fpm

# Устанавливаем зависимости
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем расширения PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создаем пользователя и группу
RUN groupadd -g 1000 www && \
    useradd -u 1000 -ms /bin/bash -g www www

# Создаем рабочую директорию
WORKDIR /var/www/html

# Создаем директорию для скриптов
RUN mkdir -p /docker

# Копируем entrypoint скрипт в правильное место
COPY php/entrypoint.sh /docker/entrypoint.sh
RUN chmod +x /docker/entrypoint.sh

# Копируем setup-permissions скрипт
COPY php/setup-permissions.sh /docker/setup-permissions.sh
RUN chmod +x /docker/setup-permissions.sh

# Копируем приложение с правильными правами (раскомментируйте когда нужно)
# COPY --chown=www:www . .

# Меняем пользователя
USER www

EXPOSE 9000

# Используем entrypoint (указываем полный путь)
ENTRYPOINT ["/docker/entrypoint.sh"]
CMD ["php-fpm"]