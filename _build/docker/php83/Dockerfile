FROM php:8.3-cli

ARG CI

RUN apt-get update \
     && apt-get install -y libzip-dev \
     && docker-php-ext-install zip

# Install Xdebug only if not in CI environment
RUN if [ "$CI" != "true" ]; then \
        pecl install xdebug && docker-php-ext-enable xdebug; \
    fi