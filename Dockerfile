FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

WORKDIR /var/www

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Install dependencies
RUN composer install --no-interaction

# Create var directory and set permissions
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var \
    && chmod -R 777 var
