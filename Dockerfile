FROM php:rc-alpine

WORKDIR /app

COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer
COPY . .

RUN composer install --ignore-platform-reqs
