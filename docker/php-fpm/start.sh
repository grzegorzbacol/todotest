#!/bin/sh
echo "=== PHP-FPM DRASTIC STARTUP ==="

# 1. Usuwamy WSZYSTKIE stare konfiguracje pul, które mogą nam przeszkadzać
rm -rf /usr/local/etc/php-fpm.d/*

# 2. Tworzymy jedną, jedyną i czystą konfigurację puli [www]
cat > /usr/local/etc/php-fpm.d/www.conf <<EOF
[www]
user = www-data
group = www-data
listen = 0.0.0.0:9000

; Usuwamy CAŁKOWICIE jakiekolwiek ograniczenia IP
; listen.allowed_clients = any

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35

access.log = /proc/self/fd/2
catch_workers_output = yes
decorate_workers_output = no

php_admin_value[error_log] = /proc/self/fd/2
php_admin_flag[log_errors] = on
php_admin_value[display_errors] = On
php_admin_value[display_startup_errors] = On
clear_env = no
EOF

echo "New config created. Testing..."
php-fpm -t

echo "Starting PHP-FPM..."
exec php-fpm -F
