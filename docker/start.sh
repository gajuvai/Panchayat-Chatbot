#!/bin/sh
set -e

# Use Railway's PORT or default to 8080
PORT=${PORT:-8080}

# Replace port placeholder in nginx config
sed "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/nginx.conf > /tmp/nginx.conf
cp /tmp/nginx.conf /etc/nginx/nginx.conf

# Run Laravel setup
php /app/artisan migrate --force
php /app/artisan config:cache
php /app/artisan route:cache
php /app/artisan view:cache

# Create supervisor log directory
mkdir -p /var/log/supervisor

# Start php-fpm in background
php-fpm -D

# Start nginx in foreground
nginx -g "daemon off;"
