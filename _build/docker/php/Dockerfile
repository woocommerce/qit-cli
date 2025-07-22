# Use an ARG for the PHP version. Default to 8.3 if not provided.
ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli

ARG CI

# Fix repositories for older Debian versions (Buster for PHP 7.2)
RUN if [ -f /etc/apt/sources.list ] && grep -q "buster" /etc/apt/sources.list; then \
        sed -i 's/deb.debian.org/archive.debian.org/g' /etc/apt/sources.list && \
        sed -i 's/security.debian.org/archive.debian.org/g' /etc/apt/sources.list && \
        sed -i '/buster-updates/d' /etc/apt/sources.list; \
    fi

RUN apt-get update \
    && apt-get install -y libzip-dev \
    && docker-php-ext-install zip pcntl \
    && docker-php-ext-enable zip pcntl

# Install php-ast
RUN pecl install ast && docker-php-ext-enable ast

# Install Xdebug only if not in CI environment and PHP major version >= 8
RUN if [ "$CI" != "true" ] && [ "${PHP_VERSION%%.*}" -ge 8 ]; then \
        pecl install xdebug && docker-php-ext-enable xdebug; \
    fi
