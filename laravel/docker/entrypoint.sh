#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=.' .env 2>/dev/null; then
    php artisan key:generate --force
fi

until php artisan migrate --pretend 2>/dev/null; do
    sleep 2
done

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force
php artisan db:seed --force

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

exec php-fpm
