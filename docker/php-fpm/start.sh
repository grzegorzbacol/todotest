#!/bin/sh
# Script to fix PHP-FPM configuration for Docker/Coolify
# Remove or comment out listen.allowed_clients from all PHP-FPM config files

echo "=== PHP-FPM Startup Script ==="
echo "Starting PHP-FPM configuration fix..."

# Find and fix all PHP-FPM pool config files
find /usr/local/etc/php-fpm.d/ -name "*.conf" -type f 2>/dev/null | while read config_file; do
    echo "Processing config file: $config_file"
    
    # Show original content
    echo "Original listen configuration:"
    grep "^listen\s*=" "$config_file" 2>/dev/null || echo "  (none found)"
    echo "Original listen.allowed_clients lines:"
    grep "listen.allowed_clients" "$config_file" 2>/dev/null || echo "  (none found)"
    
    # Fix listen address - change 127.0.0.1 to 0.0.0.0 for Docker networking
    sed -i.bak 's/^listen\s*=\s*127\.0\.0\.1:9000$/listen = 0.0.0.0:9000/' "$config_file" 2>/dev/null || true
    sed -i.bak 's/^listen\s*=\s*localhost:9000$/listen = 0.0.0.0:9000/' "$config_file" 2>/dev/null || true
    
    # Remove or comment out listen.allowed_clients lines (Alpine Linux compatible)
    sed -i.bak 's/^listen\.allowed_clients\s*=.*$/# Removed for Docker networking - allow all connections/' "$config_file" 2>/dev/null || true
    # Also handle lines with 'any' value
    sed -i.bak 's/^listen\.allowed_clients\s*=\s*any$/# Removed for Docker networking - allow all connections/' "$config_file" 2>/dev/null || true
    # Remove backup files
    rm -f "${config_file}.bak" 2>/dev/null || true
    
    # Show modified content
    echo "After modification:"
    echo "  listen configuration:"
    grep "^listen\s*=" "$config_file" 2>/dev/null || echo "    (not found)"
    echo "  listen.allowed_clients:"
    grep "listen.allowed_clients" "$config_file" 2>/dev/null || echo "    (removed/commented)"
done

# Verify PHP-FPM configuration
echo ""
echo "Testing PHP-FPM configuration..."
php-fpm -t 2>&1 || echo "Warning: PHP-FPM config test failed, but continuing..."

# Show final configuration
echo ""
echo "Final PHP-FPM pool configuration:"
grep -h "listen\|allowed_clients" /usr/local/etc/php-fpm.d/*.conf 2>/dev/null || echo "No pool configs found"

# Check if port 9000 is available
echo ""
echo "Checking network configuration:"
echo "Listening on: 0.0.0.0:9000"

# Start PHP-FPM in foreground mode
echo ""
echo "Starting PHP-FPM in foreground mode..."
echo "=== End of startup script ==="
exec php-fpm -F

