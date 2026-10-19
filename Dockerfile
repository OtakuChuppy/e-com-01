# ---------------------------------------------------------------------------
# Render deployment image (free web service) — PHP 8.3 + built Vite assets
# ---------------------------------------------------------------------------
# Stage 1: build frontend assets with Node
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# Stage 2: PHP runtime
FROM php:8.3-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        mbstring \
        pdo_pgsql \
        pcntl \
        dom \
        opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies last for better layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts \
    && rm -rf /root/.composer/cache

COPY . .
COPY --from=assets /app/public/build public/build

RUN mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions \
             storage/framework/testing storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Migrate + seed on boot (seeders are idempotent), then serve on Render's $PORT
CMD php artisan storage:link || true; \
    php artisan migrate --force && php artisan db:seed --force && \
    exec php artisan serve --host 0.0.0.0 --port "${PORT:-10000}"
