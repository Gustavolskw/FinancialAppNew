FROM php:8.4-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    git unzip curl ca-certificates \
    libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl zip pdo pdo_pgsql opcache \
    && rm -rf /var/lib/apt/lists/*

# Xdebug (instala e habilita UMA vez)
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Config Xdebug (não precisa setar zend_extension aqui de novo)
RUN printf "%s\n" \
  "xdebug.mode=debug,develop" \
  "xdebug.start_with_request=yes" \
  "xdebug.client_host=host.docker.internal" \
  "xdebug.client_port=9003" \
  "xdebug.discover_client_host=0" \
  "xdebug.log=/tmp/xdebug.log" \
  > /usr/local/etc/php/conf.d/90-xdebug.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Nginx config (Symfony)
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copia o projeto inteiro (aí bin/console existe)
COPY . .

# Instala deps no build SEM rodar scripts (evita cache:clear falhar)
RUN composer install --no-interaction --prefer-dist --no-scripts || true

# Remove configs padrão que conflitam
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf || true

# Nginx config (Symfony)
COPY docker/nginx/default.conf /etc/nginx/conf.d/app.conf

# Permissões Symfony
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var \
    && chmod -R 775 var

# Start script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/start.sh"]
