#!/bin/sh
# Container start: prepare Laravel, then run Apache in the foreground.
set -e
cd /var/www/html

# Render provides the port in $PORT (default 10000).
export PORT="${PORT:-10000}"
echo "Listen ${PORT}" > /etc/apache2/ports.conf
echo "ServerName localhost" >> /etc/apache2/ports.conf

php artisan storage:link --force >/dev/null 2>&1 || true

# Apply new database migrations on every deploy (set RUN_MIGRATIONS=false to skip).
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

# Cache config, routes, views and events with the real environment variables.
php artisan optimize

chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
