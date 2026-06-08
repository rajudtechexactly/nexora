#!/usr/bin/env bash
set -e

# This script ALWAYS runs the boot sequence (nginx config, caches, migrations)
# and then hands off to the container command ($@), which defaults to the
# Dockerfile CMD: supervisord (nginx + php-fpm + queue worker).
#
# Important on Render: leave the service's "Docker Command" BLANK so CMD runs.
# Anything you put there replaces CMD — set it to
#   supervisord -c /etc/supervisor/conf.d/supervisord.conf
# if you must set one, or the queue worker won't start.

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

echo "==> Handing off to: ${*:-<Dockerfile CMD: supervisord>}"
# Default CMD is supervisord (nginx + php-fpm + queue worker). A one-off like
# `docker run <img> php artisan tinker` still gets the boot sequence above.
exec "$@"
