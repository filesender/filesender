#!/bin/bash
set -e

# Guna config_sample.php sebagai template
if [ -f /var/www/html/config/config_sample.php ]; then
    TEMPLATE_PATH="/var/www/html/config/config_sample.php"
else
    echo "ERROR: No config template found (config_sample.php missing)!"
    exit 1
fi

# Jika config.php belum wujud, salin dari template
if [ ! -f /var/www/html/config/config.php ]; then
    cp "$TEMPLATE_PATH" /var/www/html/config/config.php
    echo "Config file created from config_sample.php"
    echo "Please edit /var/www/html/config/config.php with your database details."
    chown www-data:www-data /var/www/html/config/config.php
fi

# Set ownership untuk direktori yang diperlukan
mkdir -p /var/www/html/data /var/www/html/tmp
chown -R www-data:www-data /var/www/html/config /var/www/html/data /var/www/html/tmp 2>/dev/null || true

exec "$@"
