# syntax=docker/dockerfile:1
# ---------------------------------------------------------------------------
# Nexora — production image for Render.com (Docker runtime)
#
# Single container running nginx + php-fpm (+ a queue worker) under supervisor,
# listening on the port Render injects as $PORT. The database is external
# (Neon Postgres), so no DB is bundled here.
# ---------------------------------------------------------------------------
FROM php:8.3-fpm-bookworm

# --- System packages + PHP extensions -------------------------------------
# install-php-extensions resolves all build/runtime deps for each extension.
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/bin/
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx supervisor gettext-base ffmpeg \
    && install-php-extensions \
        pdo_pgsql pgsql gd zip bcmath intl pcntl exif opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer (from the official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# --- PHP / OPcache production tuning ---------------------------------------
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-nexora.ini

# --- Composer dependencies (cached layer) ----------------------------------
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

# --- Application source -----------------------------------------------------
COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi || true

# Writable dirs for the web/worker user
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# --- Runtime config ---------------------------------------------------------
COPY docker/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# Render sets $PORT (defaults to 10000); nginx is templated to it at startup.
ENV PORT=10000
EXPOSE 10000

# entrypoint runs the boot sequence then execs CMD. CMD is supervisor, which
# starts nginx + php-fpm + the queue worker. Keep Render's "Docker Command"
# blank so this CMD runs (anything there replaces it and kills the worker).
ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
