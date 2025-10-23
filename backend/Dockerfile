FROM php:8.2-fpm-alpine

RUN apk add --no-cache bash git zip libzip-dev oniguruma-dev icu-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip intl

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist \
    && php artisan key:generate || true \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm", "-F"]
