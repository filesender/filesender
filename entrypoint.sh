#!/bin/bash
set -e

# Jika fail config.php belum wujud, salin dari template
if [ ! -f /var/www/html/config/config.php ]; then
    cp /var/www/html/config/config.php.template /var/www/html/config/config.php
    echo "Config file created from template. Please edit config/config.php with your database details."
fi

# Set ownership yang betul
chown -R www-data:www-data /var/www/html/config /var/www/html/data /var/www/html/tmp

# Jalankan perintah asal (apache2-foreground)
exec "$@"
