FROM php:8.4-fpm

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libldap2-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем расширения PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Устанавливаем и настраиваем LDAP расширение (ДОБАВЛЕНО)
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создаем группу и пользователя с переданными UID/GID (ИСПРАВЛЕНО)
ARG UID_HOST=1000
ARG GID_HOST=1000
RUN groupadd -g ${GID_HOST} www && \
    useradd -u ${UID_HOST} -g www -m www && \
    usermod -a -G www www

# Создаем рабочую директорию
RUN mkdir -p /var/www/html
WORKDIR /var/www/html

# Создаем директорию для скриптов
RUN mkdir -p /docker

# Копируем скрипты
COPY php/entrypoint.sh /docker/entrypoint.sh

# Делаем скрипты исполняемыми
RUN chmod +x /docker/entrypoint.sh

# Меняем владельца рабочей директории
RUN chown -R www:www /var/www/html

# Устанавливаем точку входа (ДОБАВЛЕНО)
ENTRYPOINT ["/docker/entrypoint.sh"]

EXPOSE 9000
CMD ["php-fpm"]