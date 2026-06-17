FROM php:8.2-cli

RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip zip libzip-dev \
 && docker-php-ext-install zip \
 && rm -rf /var/lib/apt/lists/*

# Coverage driver for local dev (PHPUnit --coverage-text).
RUN pecl install pcov-1.0.12 && docker-php-ext-enable pcov

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
