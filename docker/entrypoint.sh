#!/bin/sh
set -e

cd /var/www/html

if [ -n "${MYSQL_URL:-}" ] && [ -z "${DATABASE_URL:-}" ]; then
    export DATABASE_URL="$MYSQL_URL"
fi

if [ -n "${MYSQLHOST:-}" ]; then
    export DB_CONNECTION="${DB_CONNECTION:-mysql}"
    export DB_HOST="${DB_HOST:-$MYSQLHOST}"
    export DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
    export DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-}}"
    export DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-}}"
    export DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"
fi

if [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ] && { [ -z "${APP_URL:-}" ] || [ "${APP_URL}" = "http://localhost:8000" ]; }; then
    export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
fi

APP_PORT="${PORT:-80}"
sed "s/listen 80;/listen ${APP_PORT};/" /etc/nginx/http.d/default.conf > /tmp/default.conf
mv /tmp/default.conf /etc/nginx/http.d/default.conf

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
