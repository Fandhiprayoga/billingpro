FROM php:8.2-apache

# -----------------------------------------------------------------------------
# Description: Dockerfile untuk membangun image PHP 8.2 dengan Apache,
# dioptimalkan untuk menjalankan aplikasi CodeIgniter 4.
#
# Usage:
# 1. Start the application using Docker Compose:
#    docker-compose -f docker-compose.yml up --build
# 2. Access the application in your browser at http://localhost:8080
#
# Author: FANDHI D. PRAYOGA
# -----------------------------------------------------------------------------

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required for CodeIgniter 4
RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    intl \
    zip \
    opcache

# Aktifkan mod_rewrite untuk URL yang bersih
RUN a2enmod rewrite

# Ubah DocumentRoot Apache ke folder public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Konfigurasi Apache AllowOverride agar .htaccess berfungsi
RUN sed -i '/<Directory \${APACHE_DOCUMENT_ROOT}>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory di dalam container
WORKDIR /var/www/html

# Salin composer files dan install dependencies
COPY composer.json composer.json
RUN composer install --no-dev --optimize-autoloader --no-scripts || true

# Salin seluruh isi proyek CodeIgniter 4 ke dalam container
COPY . /var/www/html

# Atur izin direktori writable agar bisa ditulis oleh Apache
RUN chown -R www-data:www-data writable
RUN chmod -R 775 writable

# Expose port 80 untuk web server Apache
EXPOSE 80