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

# Install Node.js and npm for frontend build
RUN apk add --no-cache nodejs npm

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

# Copy package files for npm install
COPY package.json package-lock.json* ./
COPY tsconfig.json tsconfig.node.json vite.config.ts tailwind.config.js postcss.config.js ./

# Install Node.js dependencies
RUN npm ci --only=production || npm install

# Copy application files
COPY . .

# Build frontend assets
RUN npm run build

# Regenerate Laravel cache (without dev packages)
RUN php artisan package:discover --ansi || true

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Production stage
FROM base AS production

# Copy PHP-FPM configuration (after all files are copied)
COPY docker/php-fpm/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# Copy start script
COPY docker/php-fpm/start.sh /usr/local/bin/start-php-fpm.sh
RUN chmod +x /usr/local/bin/start-php-fpm.sh

# Expose port
EXPOSE 9000

CMD ["/usr/local/bin/start-php-fpm.sh"]

