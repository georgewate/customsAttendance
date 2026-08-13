# ---------- Stage 1: build frontend assets ----------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---------- Stage 2: PHP runtime ----------
FROM php:8.3-fpm

# System packages + nginx
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        nodejs \
        npm \
        libpq-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libicu-dev \
        libonig-dev \
        unzip \
        git \
        curl \
        ca-certificates \
        openssl \
        gettext-base \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        intl \
        bcmath \
        mbstring \
        exif \
        pcntl \
        opcache \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP settings (uploads, memory, timeouts)
RUN printf 'memory_limit=512M\nupload_max_filesize=25M\npost_max_size=30M\nmax_execution_time=300\nmax_input_time=300\n' > /usr/local/etc/php/conf.d/render.ini

ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html

# App code
COPY . .
COPY --from=assets /app/public/build public/build
COPY --from=assets /app/node_modules node_modules

# Install PHP dependencies (package:discover runs during install)
COPY .env.example .env
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && php artisan key:generate --force \
    && rm .env

# Headless Chromium for Playwright-based PDFs (user guide / technical docs).
# Best-effort: if the browser install fails, the rest of the app still deploys.
RUN npx playwright install --with-deps chromium || true

# Nginx config + entrypoint
RUN rm -f /etc/nginx/sites-enabled/default
COPY nginx.conf /etc/nginx/conf.d/laravel.conf.template
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 10000

CMD ["/usr/local/bin/start.sh"]
