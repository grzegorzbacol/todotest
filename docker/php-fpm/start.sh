#!/bin/sh
# Script to fix PHP-FPM configuration for Docker/Coolify
# Remove or comment out listen.allowed_clients from all PHP-FPM config files

# Find and fix all PHP-FPM pool config files
find /usr/local/etc/php-fpm.d/ -name "*.conf" -type f | while read config_file; do
    # Remove or comment out listen.allowed_clients lines
    sed -i 's/^listen\.allowed_clients\s*=.*$/# Removed for Docker networking - allow all connections/' "$config_file"
    # Also handle lines with 'any' value
    sed -i 's/^listen\.allowed_clients\s*=\s*any$/# Removed for Docker networking - allow all connections/' "$config_file"
done

# Start PHP-FPM in foreground mode
exec php-fpm -F

