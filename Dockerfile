# ===== Stage: base PHP-FPM =====
FROM php:7.1-fpm-alpine

# Ext yang umum untuk Laravel 5.4
RUN set -eux; \
    apk add --no-cache bash git unzip icu-dev libzip-dev zlib-dev libpng-dev oniguruma-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring tokenizer xml bcmath zip opcache

# Direktori kerja
WORKDIR /var/www/html

# Siapkan Composer v1 (lebih cocok utk dependency lama Laravel 5.x)
ENV COMPOSER_ALLOW_SUPERUSER=1
ADD https://getcomposer.org/download/1.10.26/composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer && composer --version

# Copy source code
COPY . /var/www/html

# Permission untuk cache Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && find storage -type d -exec chmod 775 {} \; \
    && find storage -type f -exec chmod 664 {} \; \
    && chmod -R 775 bootstrap/cache

# Install dependency (no-dev untuk produksi)
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Optimisasi Laravel (opsional, bisa juga dijalankan sebagai one-off command di Dokploy)
# RUN php artisan key:generate --force \
#     && php artisan config:cache \
#     && php artisan route:cache \
#     && php artisan view:cache

EXPOSE 9000
CMD ["php-fpm", "-F"]
