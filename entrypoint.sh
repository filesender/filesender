#!/bin/bash
set -e

if [ ! -f /var/www/html/config/config.php ]; then
    cp /var/www/html/config/config.php.template /var/www/html/config/config.php
    echo "Config file created from template."
    # Pastikan web server boleh baca dan tulis fail config yang baru
    chown www-data:www-data /var/www/html/config/config.php
fi

# Pastikan web server boleh tulis ke direktori ini
chown -R www-data:www-data /var/www/html/config /var/www/html/data /var/www/html/tmp

exec "$@"
