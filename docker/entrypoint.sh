#!/usr/bin/env bash
set -e

# Allow one-off commands (e.g. `docker run <img> php artisan migrate`) to run
# directly, skipping the web boot sequence. Render starts the container with no
# command override, so the full boot below runs for the web service.
if [ "$#" -gt 0 ]; then
    exec "$@"
fi

# Render injects $PORT; default to 10000 for local `docker run`.
export PORT="${PORT:-10000}"

echo "==> Rendering nginx config for port ${PORT}"
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf
# Remove the distro default server so it doesn't clash on port 80.
rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

# Ensure runtime dirs exist and are writable (storage is ephemeral on Render).
mkdir -p storage/framework/{sessions,views,cache} storage/logs
chown -R www-data:www-data storage bootstrap/cache || true

echo "==> Caching framework config"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link 2>/dev/null || true

# Run migrations on boot unless explicitly disabled (RUN_MIGRATIONS=false).
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "==> Running database migrations"
    php artisan migrate --force --no-interaction || echo "!! migrate failed (continuing)"
fi

echo "==> Starting supervisor (nginx + php-fpm + queue)"
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
