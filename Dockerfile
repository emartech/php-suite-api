FROM php:8.0-apache

RUN apt-get update && \
    apt-get install -y --force-yes unzip zip libzip-dev
RUN pecl install zip && docker-php-ext-enable zip

ENV COMPOSER_ALLOW_SUPERUSER="1"
RUN curl -Ss https://getcomposer.org/installer | php && \
    mv composer.phar /usr/bin/composer
