FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    zip \
    curl \
    wget \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# RUN composer install --no-dev --optimize-autoloader
RUN composer install

RUN chown -R www-data:www-data /var/www/html/var /var/www/html/public

USER www-data

EXPOSE 9000

CMD ["php-fpm"]