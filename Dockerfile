# ASSUMPTION: Multi-stage build for production deployment on Coolify
# Coolify will automatically add nginx as reverse proxy
# This Dockerfile only builds PHP-FPM application
# Note: Using PHP 8.4 to match Symfony 8.0 requirements

FROM php:8.4-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files, artisan, bootstrap, and routes (needed for post-install scripts)
COPY composer.json composer.lock artisan ./
COPY bootstrap ./bootstrap
COPY routes ./routes

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Production stage
FROM base AS production

# Expose port
EXPOSE 9000

CMD ["php-fpm"]

