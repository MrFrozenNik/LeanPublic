FROM php:8.5-fpm-alpine

RUN apk add --no-cache nodejs npm \
    && docker-php-ext-install pdo_mysql bcmath \
    && apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader --no-dev \
    && npm ci && npm run build \
    && chown -R www-data:www-data storage bootstrap/cache \
    && rm -rf node_modules

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
