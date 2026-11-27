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

# Background worker for queued jobs (emails), next to Apache in the same
# container, so no separate Render service is needed. It runs as www-data
# (like Apache, so files it writes stay usable), restarts itself every hour
# to keep memory low, and is restarted if it stops for any reason.
# Set RUN_QUEUE_WORKER=false when a separate worker service is used instead.
if [ "${RUN_QUEUE_WORKER:-true}" = "true" ]; then
    (
        while true; do
            runuser -u www-data -- php artisan queue:work --sleep=3 --max-time=3600 --memory=128 || true
            sleep 2
        done
    ) &
fi

exec apache2-foreground
