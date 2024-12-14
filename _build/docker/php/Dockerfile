# Use an ARG for the PHP version. Default to 8.3 if not provided.
ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli

ARG CI
RUN apt-get update \
    && apt-get install -y libzip-dev \
    && docker-php-ext-install zip pcntl \
    && docker-php-ext-enable zip pcntl

# Install Xdebug only if not in CI environment
RUN if [ "$CI" != "true" ]; then \
        pecl install xdebug && docker-php-ext-enable xdebug; \
    fi
