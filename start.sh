#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# Make writable dirs writable for www-data (php-fpm user)
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Generate app key if not provided
if [ -z "${APP_KEY:-}" ]; then
    php artisan key:generate --force
fi

# Public storage symlink for employee photos
php artisan storage:link || true

# Run migrations with retry (DB may not be ready on first boot)
for i in $(seq 1 10); do
    if php artisan migrate --force --no-interaction; then
        break
    fi
    echo "Migration attempt $i failed, retrying in 5s..."
    sleep 5
done

# Seed an admin account (DatabaseSeeder is idempotent - skips if admin exists)
php artisan db:seed --force --no-interaction || true

# Optimize
php artisan config:cache || true
php artisan view:cache || true
php artisan event:cache || true

# Render nginx config with the Render-provided PORT
export PORT="${PORT:-10000}"
envsubst '${PORT}' < /etc/nginx/conf.d/laravel.conf.template > /etc/nginx/conf.d/laravel.conf

# Start php-fpm in the background, then nginx in the foreground
php-fpm -D
nginx -g 'daemon off;'
