#!/bin/sh

# Use Railway's PORT or default to 8080
PORT=${PORT:-8080}

echo "=== Starting Panchayat Chatbot ==="
echo "PORT=$PORT"

# Replace port in nginx config
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/nginx.conf

# Create required dirs
mkdir -p /var/log/nginx /run

# Check php-fpm binary
echo "PHP-FPM binary: $(which php-fpm)"
php-fpm --version

# Start php-fpm (foreground mode, no daemon)
echo "Starting php-fpm..."
php-fpm --nodaemonize &
PHP_PID=$!
echo "php-fpm PID: $PHP_PID"

# Wait for php-fpm socket/port to be ready
sleep 2

# Check php-fpm is running
if kill -0 $PHP_PID 2>/dev/null; then
    echo "php-fpm is running"
else
    echo "ERROR: php-fpm failed to start"
    exit 1
fi

# Laravel setup
cd /app

# Only generate key if not already set via environment variable
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force || true
fi

# Wait for database to be ready using TCP check
DB_HOST_CHECK=${DB_HOST:-127.0.0.1}
DB_PORT_CHECK=${DB_PORT:-3306}
echo "Waiting for database at $DB_HOST_CHECK:$DB_PORT_CHECK..."
MAX_RETRIES=30
COUNT=0
until nc -z -w 2 "$DB_HOST_CHECK" "$DB_PORT_CHECK" 2>/dev/null; do
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge $MAX_RETRIES ]; then
        echo "ERROR: Database not reachable after $MAX_RETRIES attempts"
        exit 1
    fi
    echo "Database not ready, retrying ($COUNT/$MAX_RETRIES)..."
    sleep 2
done
echo "Database is ready."

echo "Running migrations..."
php artisan migrate --force || { echo "Migration failed"; exit 1; }

# Seed only if the users table is empty (fresh deploy)
USER_COUNT=$(php artisan tinker --no-interaction --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1)
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "Seeding database..."
    php artisan db:seed --force || echo "Seeder warning - continuing"
else
    echo "Database already has data, skipping seed."
fi

php artisan storage:link || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting nginx on port $PORT..."
exec nginx -g "daemon off;"
