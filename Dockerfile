FROM php:8.0-apache

ENV COMPOSER_ALLOW_SUPERUSER="1"
RUN curl -Ss https://getcomposer.org/installer | php && \
    mv composer.phar /usr/bin/composer
