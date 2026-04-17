#!/bin/sh

# Create an empty .env so artisan commands can bootstrap Laravel
# Real config is read from environment variables injected by Render
touch /var/www/html/.env

# Generate APP_KEY if not already set or not in base64 format
if [ -z "$APP_KEY" ]; then
    php /var/www/html/artisan key:generate --force
fi

# Run migrations — retry up to 5 times in case DB isn't ready yet
RETRIES=5
until php /var/www/html/artisan migrate --force 2>&1; do
    RETRIES=$((RETRIES - 1))
    if [ $RETRIES -eq 0 ]; then
        echo "Migration failed after 5 attempts, continuing anyway..."
        break
    fi
    echo "DB not ready, retrying in 5s... ($RETRIES attempts left)"
    sleep 5
done

# Seed database with initial data (seeder skips if data already exists)
php /var/www/html/artisan db:seed --force

# Cache config/routes/views for production performance
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

# Create storage symlink
php /var/www/html/artisan storage:link --force 2>/dev/null || true

# Start nginx + php-fpm via supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf
