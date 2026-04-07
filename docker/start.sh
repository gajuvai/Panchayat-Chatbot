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
echo "Running migrations and seeders..."
cd /app
# Only generate key if not already set via environment variable
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force || true
fi
php artisan migrate --force || echo "Migration warning - continuing"
php artisan db:seed --force || echo "Seeder warning - continuing"
php artisan storage:link || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting nginx on port $PORT..."
exec nginx -g "daemon off;"
