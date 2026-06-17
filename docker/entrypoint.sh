#!/bin/sh

PORT=${PORT:-80}
sed -i "s/listen 80/listen ${PORT}/g" /etc/nginx/conf.d/default.conf

# Always clear first to avoid stale config
php artisan config:clear
php artisan route:clear  
php artisan view:clear

# Then rebuild
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

RUN_MIGRATIONS=${RUN_MIGRATIONS:-true}
if [ "$RUN_MIGRATIONS" != "false" ]; then
    php artisan migrate:fresh --force
    php artisan db:seed --force
fi

php-fpm -D

nginx -g "daemon off;"