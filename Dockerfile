# Use an official PHP 8.3 image as the base
FROM php:8.3-fpm

# Set the working directory to /var/www
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    vim \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libicu-dev \
    libmariadb-dev \
    zlib1g-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    supervisor \
    nginx \
    netcat-openbsd

# Install Node.js and npm
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd gettext intl zip sockets

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create supervisor directories with absolute paths
RUN mkdir -p /etc/supervisor/conf.d /var/log/supervisor

# Copy the application code
COPY . .

# Copy supervisor and nginx configurations with absolute paths
COPY ./docker/supervisor/supervisor.conf /etc/supervisor/conf.d/supervisor.conf
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Install Composer dependencies
RUN composer update --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Clear all Laravel caches and regenerate
RUN php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# Install Node.js dependencies and run npm scripts
RUN npm install

# Set permissions for the storage folder
RUN chown -R www-data:www-data /var/www/storage
RUN chmod -R 775 /var/www/storage

# create worker log in storage folder
RUN touch /var/www/storage/logs/worker.log

# Expose ports
EXPOSE 9000 80

# Set PHP configurations
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini
RUN echo "upload_max_filesize=1000M" > /usr/local/etc/php/conf.d/upload_max_filesize-limit.ini
RUN echo "post_max_size=1000M" > /usr/local/etc/php/conf.d/post_max_size-limit.ini
RUN echo "opcache.enable=1" >>  "$PHP_INI_DIR/php.ini"
RUN echo "opcache.enable_cli=1" >> "$PHP_INI_DIR/php.ini"
RUN echo "max_execution_time=360" > /usr/local/etc/php/conf.d/max_execution_time-limit.ini

# Verify supervisor installation and paths
RUN which supervisord
RUN ls -la /usr/bin/supervisord

COPY docker-entrypoint.sh /usr/local/bin
#set permission of who can run the script
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT [ "/usr/local/bin/docker-entrypoint.sh" ]