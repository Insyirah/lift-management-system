#!/bin/sh
set -e

# Run database migrations
php /var/www/html/artisan migrate --force

# Clear and cache config for production
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

# Create storage symlink if not exists
php /var/www/html/artisan storage:link --force 2>/dev/null || true

# Start supervisord (nginx + php-fpm)
exec /usr/bin/supervisord -c /etc/supervisord.conf
