FROM php:8.3-fpm-alpine

# Install php-extension-installer (pre-compiled binaries — much faster than docker-php-ext-install)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    bash \
    sed

# Install PHP extensions using pre-compiled binaries
RUN install-php-extensions \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    xml \
    ctype \
    fileinfo \
    opcache \
    zip \
    gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first for layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy package files and install npm deps
COPY package.json package-lock.json ./
RUN npm ci

# Copy the rest of the app
COPY . .

# Build frontend assets
RUN npm run build

# Run composer scripts after full copy
RUN composer run-script post-autoload-dump || true

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Copy configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/bin/sh", "/start.sh"]
