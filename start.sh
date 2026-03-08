#!/bin/sh

# Run migrations
php artisan migrate --force

# Optimize Laravel
php artisan optimize


# Start PHP-FPM in background
php-fpm &

# Start Nginx in foreground
nginx -g "daemon off;"
