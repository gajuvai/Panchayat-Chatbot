#!/bin/sh
set -e

# Use Railway's PORT or default to 8080
PORT=${PORT:-8080}

echo "Starting with PORT=$PORT"

# Replace port placeholder in nginx config
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/nginx.conf

# Create required directories
mkdir -p /var/log/nginx /var/run/php-fpm /run/nginx

# Start php-fpm in background
echo "Starting php-fpm..."
php-fpm -D -F &
PHP_PID=$!

# Wait for php-fpm to be ready
echo "Waiting for php-fpm..."
sleep 3

# Run Laravel setup
echo "Running Laravel setup..."
php /app/artisan migrate --force || echo "Migration failed, continuing..."
php /app/artisan config:cache || true
php /app/artisan route:cache || true
php /app/artisan view:cache || true

# Start nginx in foreground
echo "Starting nginx on port $PORT..."
nginx -g "daemon off;"
