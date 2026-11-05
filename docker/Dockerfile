FROM php:8.5.12-apache

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
    nodejs npm \
    && docker-php-ext-install zip mysqli \
    && apt-get clean

RUN a2enmod rewrite headers

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html