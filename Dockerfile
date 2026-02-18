# Build stage for frontend assets
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Create required directories
RUN mkdir -p /var/log/supervisor /var/run/supervisor

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci --only=production=false

# Copy source files needed for build
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Build frontend assets
RUN npm run build

# PHP application stage
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    nginx \
    nodejs \
    npm \
    supervisor \
    zip

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        opcache \
        pdo_mysql \
        zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Create required directories
RUN mkdir -p /var/log/supervisor /var/run/supervisor

# Copy nginx and supervisor configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80 5173

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
