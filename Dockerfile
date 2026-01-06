FROM php:8.4-fpm-alpine

# Instalacja zależności
RUN apk add --no-cache \
    git curl libpng-dev libzip-dev zip unzip postgresql-dev oniguruma-dev nodejs npm

# Instalacja rozszerzeń PHP
RUN docker-php-ext-install pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalacja Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Kopiowanie plików (zakładamy, że masz je w głównym folderze)
COPY . .

# Instalacja zależności (wyłączone jeśli nie masz plików lock)
RUN composer install --no-dev --optimize-autoloader --no-interaction || true
RUN npm install && npm run build || true

# Copy PHP-FPM configuration
COPY docker/php-fpm/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# Copy start script
COPY docker/php-fpm/start.sh /usr/local/bin/start-php-fpm.sh
RUN chmod +x /usr/local/bin/start-php-fpm.sh

# Uprawnienia
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# PHP-FPM zawsze działa na porcie 9000
EXPOSE 9000

CMD ["/usr/local/bin/start-php-fpm.sh"]
