#!/bin/sh
set -e

cd /var/www/html

php artisan config:clear --no-interaction >/dev/null 2>&1 || true
php artisan config:cache --no-interaction

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    attempts=0
    max_attempts="${MIGRATION_ATTEMPTS:-10}"

    until php artisan migrate --force --no-interaction; do
        attempts=$((attempts + 1))

        if [ "$attempts" -ge "$max_attempts" ]; then
            echo "Database migrations failed after $attempts attempts." >&2
            exit 1
        fi

        echo "Database is not ready yet; retrying migrations in 5 seconds ($attempts/$max_attempts)." >&2
        sleep 5
    done
fi

exec "$@"
