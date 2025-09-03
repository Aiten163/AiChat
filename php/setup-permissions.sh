#!/bin/bash

# Параметры
TARGET_GROUP="Faberlic_AI"
TARGET_GID="1254"

echo "🔧 Setting up file permissions..."

# Создаем группу если не существует
if ! getent group $TARGET_GID > /dev/null; then
    groupadd -g $TARGET_GID $TARGET_GROUP
    echo "Created group $TARGET_GROUP with GID $TARGET_GID"
fi

# Меняем группу проекта (только если нужно)
if [ -d "/var/www/html" ]; then
    chgrp -R $TARGET_GROUP /var/www/html/ || true
    find /var/www/html -type d -exec chmod 775 {} \; || true
    find /var/www/html -type f -exec chmod 664 {} \; || true
    chmod -R 775 /var/www/html/storage/ /var/www/html/bootstrap/cache/ || true
    find /var/www/html -type d -exec chmod g+s {} \; || true
fi

echo "✅ Permissions setup complete"