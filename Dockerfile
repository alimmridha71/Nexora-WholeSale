# syntax=docker/dockerfile:1.7
# =====================================================================
# Nexora Wholesale - production image
#   Stage 1: PHP dependencies (Composer, from composer.lock)
#   Stage 2: Front-end assets (Vite + Tailwind, from package-lock.json)
#   Stage 3: Runtime (PHP 8.3-FPM + Nginx, supervised, non-root)
# The build needs NO secrets: all configuration is read at container start.
# =====================================================================

# ---------- Stage 1: Composer dependencies ---------------------------
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev --no-interaction --no-progress --prefer-dist \
        --no-scripts --no-autoloader \
        --ignore-platform-req=ext-*

COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

# ---------- Stage 2: Front-end assets ---------------------------------
FROM node:22-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------- Stage 3: Runtime ------------------------------------------
FROM php:8.3-fpm-alpine AS runtime

# pdo_pgsql for Supabase/PostgreSQL (default), pdo_mysql kept for MySQL users.
RUN apk add --no-cache nginx supervisor libpq icu-libs \
    && apk add --no-cache --virtual .build-deps postgresql-dev icu-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pdo_mysql bcmath intl opcache pcntl \
    && apk del .build-deps \
    && mkdir -p /run/nginx /var/lib/nginx/tmp /var/log/supervisor \
    && chown -R www-data:www-data /run/nginx /var/lib/nginx /var/log/nginx /var/log/supervisor

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-nexora.ini
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY --chmod=0755 docker/entrypoint.sh /usr/local/bin/nexora-entrypoint

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

RUN rm -f .env \
    && chown -R www-data:www-data storage bootstrap/cache

# Run the scheduler inside this container (single-service hosts such as Render).
ENV RUN_SCHEDULER=false

USER www-data

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD wget -qO- http://127.0.0.1:8080/up >/dev/null || exit 1

ENTRYPOINT ["nexora-entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
