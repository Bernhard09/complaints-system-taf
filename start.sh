#!/bin/sh

# Run migrations
php artisan migrate --force

# Optimize Laravel
php artisan optimize


# Start PHP-FPM in background
# php-fpm &
php-fpm -D

# Start Nginx in foreground
# nginx -g "daemon off;"
nginx -c /etc/nginx/nginx.conf