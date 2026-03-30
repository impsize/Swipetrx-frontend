FROM php:8.3-apache

# Install extensions
RUN apt-get update && apt-get install -y \
    libzip-dev libpng-dev libjpeg-dev \
    && docker-php-ext-install pdo_mysql zip gd

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy files
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# CLI for cron
RUN ln -s /usr/local/bin/php /usr/bin/php

EXPOSE 80
CMD ["apache2-foreground"]