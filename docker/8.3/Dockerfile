FROM php:8.3.10-fpm-alpine

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC+8

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apk update && apk add --no-cache \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libxml2-dev \
    oniguruma-dev \
    php-curl

RUN docker-php-ext-configure pcntl --enable-pcntl

RUN docker-php-ext-install \
    zip \
    pdo_mysql \
    gd \
    mbstring \
    xml \
    pcntl \
    bcmath

# Create uploads directory
RUN mkdir -p /var/www/uploads && chown -R www-data:www-data /var/www/uploads

# Clear cache
RUN apk cache clean && \
    rm -rf /var/cache/apk/*

COPY php.ini /usr/local/etc/php/conf.d/php.ini

EXPOSE 9000
CMD ["php-fpm"]
