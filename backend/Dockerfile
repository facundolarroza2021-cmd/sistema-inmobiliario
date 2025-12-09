FROM php:8.2-fpm

# Instalamos dependencias del sistema (necesarias para PDF y Zip)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev

# Instalamos extensiones de PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalamos Composer (El gestor de paquetes de PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Comando para arrancar Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000