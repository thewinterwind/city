FROM php:8.4-fpm-alpine
RUN apk add --no-cache icu-dev oniguruma-dev libzip-dev sqlite-dev unzip git && docker-php-ext-install -j2 mbstring intl pdo_sqlite pdo_mysql zip opcache
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
