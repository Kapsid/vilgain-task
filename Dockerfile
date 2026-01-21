FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Install dependencies
RUN composer update --no-interaction --optimize-autoloader

# Generate JWT keys if they don't exist
RUN mkdir -p config/jwt

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/var

EXPOSE 9000

CMD ["php-fpm"]
