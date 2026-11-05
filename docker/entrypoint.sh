#!/bin/sh
# Nexora Wholesale container entrypoint.
# Fails fast when mandatory configuration is missing, then warms Laravel caches.
set -eu

for var in APP_KEY APP_URL DB_HOST DB_PASSWORD; do
    eval "value=\${$var:-}"
    if [ -z "$value" ]; then
        echo "FATAL: required environment variable $var is not set." >&2
        exit 1
    fi
done

php artisan package:discover --ansi
php artisan storage:link --force >/dev/null 2>&1 || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

# Idempotent: upserts demo accounts/catalog, never duplicates data.
if [ "${RUN_SEED:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec "$@"
