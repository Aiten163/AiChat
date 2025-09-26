FROM php:8.4-fpm

# Системные зависимости
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

# Расширения PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# LDAP расширение
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем entrypoint
COPY ./php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh

# Создаем группу и пользователя
ARG UID_HOST=1000
ARG GID_HOST=1000
RUN groupadd -g ${GID_HOST} www || true && \
    useradd -u ${UID_HOST} -g www -m www || true && \
    usermod -a -G www www || true


# Создаем рабочую директорию
RUN mkdir -p /var/www/html
WORKDIR /var/www/html

# Меняем владельца рабочей директории
RUN chown -R www:www /var/www/html
USER www
# Устанавливаем точку входа
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
EXPOSE 9000
CMD ["php-fpm"]
