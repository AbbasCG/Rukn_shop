FROM php:8.3-cli

# System dependencies + Node.js
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install JS deps & build Vite assets
RUN npm install
RUN npm run build

# Laravel setup
RUN php artisan key:generate --force

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000
