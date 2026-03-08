FROM php:8.4.18-fpm-alpine

RUN apk update && apk add --no-cache nginx wget git bash \
    postgresql-dev \
    libpng-dev zlib-dev libzip-dev \
    nodejs npm \
    && docker-php-ext-install pdo_pgsql pgsql gd zip

WORKDIR /var/www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

# Run composer and npm
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Setup nginx
COPY nginx.conf /etc/nginx/nginx.conf

EXPOSE 8080

CMD sh -c "php artisan migrate --force && php artisan optimize && /usr/sbin/nginx && php-fpm"
