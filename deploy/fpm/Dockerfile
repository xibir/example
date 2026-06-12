FROM php:8.3-fpm

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/install-php-extensions
RUN install-php-extensions \
    gd \
    pdo_mysql \
    intl \
    bcmath \
    sockets \
    zip \
    mcrypt \
    redis-^6 \
    mongodb-^1 \
    amqp-^2

COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

USER root

RUN rm -rf /var/www/html/*
ADD . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/

RUN rm -f /usr/local/etc/php/conf.d/*xdebug.ini

USER www-data
