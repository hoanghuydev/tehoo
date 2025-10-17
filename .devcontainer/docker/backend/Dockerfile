FROM php:8.3-fpm-alpine

# Làm việc với root để cài dependency
USER root

# Cài các gói cần thiết cho Laravel
RUN apk add --no-cache \
    build-base \
    curl \
    git \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev \
    bash \
    nodejs \
    npm \
    librdkafka-dev \
    autoconf \
    gcc \
    g++ \
    make

# Cài PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Cài rdkafka PHP extension
RUN pecl install rdkafka \
    && docker-php-ext-enable rdkafka

# Cài Redis PHP extension
RUN pecl install redis \
    && docker-php-ext-enable redis


# Cài Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Tạo user laravel thay vì đụng www-data
RUN addgroup -g 1000 laravel \
    && adduser -u 1000 -G laravel -s /bin/bash -D laravel

# Tạo thư mục project
WORKDIR /workspace/backend
RUN chown -R laravel:laravel /workspace/backend

# Switch sang user thường
USER laravel

EXPOSE 9000
CMD ["php-fpm"]
