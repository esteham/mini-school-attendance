# If you want, you can change the PHP version here, e.g., 8.2, 8.1, etc.
ARG PHP_VERSION=8.3

FROM php:${PHP_VERSION}-fpm-alpine

# System dependencies + Laravel-required extensions
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    bash \
    $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip intl gd \
    && apk del $PHPIZE_DEPS

# Copy Composer from the separate official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Working directory
WORKDIR /var/www/html

# Use www-data as the default user inside the container
RUN chown -R www-data:www-data /var/www/html

USER www-data

# In development, code will come via bind mount, so not copying separately here
# CMD: Laravel dev server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
