FROM php:8.1-cli-alpine

COPY --from=composer:2.4.4 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache git
