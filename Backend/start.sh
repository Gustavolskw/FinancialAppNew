#!/bin/bash
set -e

cd /var/www

if [ ! -d "vendor" ]; then
  echo "$(date "+%Y-%m-%d %H:%M:%S") WARN Pasta 'vendor' não encontrada. Executando 'composer install'..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
else
  echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Dependências já instaladas."
fi

# garante dirs do nginx
mkdir -p /run/nginx

# inicia php-fpm (background) e nginx (foreground)
php-fpm -D
exec nginx -g "daemon off;"
