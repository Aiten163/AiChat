FROM php:8.4-fpm

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

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh

ARG UID_HOST=1000
ARG GID_HOST=1000
RUN groupadd -g ${GID_HOST} www || true && \
    useradd -u ${UID_HOST} -g www -m www || true && \
    usermod -a -G www www || true

RUN mkdir -p /var/www/html
WORKDIR /var/www/html

RUN chown -R www:www /var/www/html
USER www

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
EXPOSE 9000
CMD ["php-fpm"]
