FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN composer dump-autoload --optimize \
    && npm run build \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV APP_NAME="Hotel Happy Dreams" \
    APP_ENV=production \
    APP_DEBUG=false \
    APP_KEY=base64:A/X4EtqosiePXXy2xby30ZuRQtDHkW3ba1BCcoIjEXc= \
    DB_CONNECTION=sqlite \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    LOG_CHANNEL=stderr

EXPOSE 10000

CMD sh -c "touch /app/database/database.sqlite && php artisan migrate --force && php -r \"require 'vendor/autoload.php'; \\\$app = require 'bootstrap/app.php'; \\\$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); if (\\App\\Models\\Rol::count() === 0) { \\Illuminate\\Support\\Facades\\Artisan::call('db:seed', ['--force' => true]); }\" && php artisan serve --host=0.0.0.0 --port=\${PORT:-10000}"
