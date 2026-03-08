FROM php:8.4.18-fpm-alpine

RUN apk update && apk add --no-cache nginx wget git bash \
    postgresql-dev \
    libpng-dev zlib-dev libzip-dev \
    nodejs npm \
    && docker-php-ext-install pdo_pgsql pgsql gd zip

WORKDIR /var/www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Run composer and npm
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/log/nginx /var/lib/nginx
RUN chmod -R 777 /var/www/storage /var/www/bootstrap/cache /var/log/nginx /var/lib/nginx /run/nginx

# Setup nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Setup startup script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
# CMD sh -c "php artisan migrate --force && php artisan optimize && /usr/sbin/nginx && php-fpm"