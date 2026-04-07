FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    curl \
    zip \
    unzip \
    git \
    libpng-dev \
    libxml2-dev \
    oniguruma-dev \
    libzip-dev \
    sqlite \
    sqlite-dev \
    bash \
    sed

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_sqlite \
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

# Set permissions and create sqlite database file
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/database \
    && chmod -R 775 /app/storage /app/bootstrap/cache /app/database \
    && touch /app/database/database.sqlite \
    && chown www-data:www-data /app/database/database.sqlite

# Copy configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/bin/sh", "/start.sh"]
