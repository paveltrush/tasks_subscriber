# Use the official PHP 8.1 image as the base image
FROM php:8.1.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy existing application directory contents to the container
COPY . /var/www/html

# Install PHP dependencies
RUN composer install

# Add crontab file to the cron directory
COPY crontab.txt /etc/cron.d/my-cron-job

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/my-cron-job

# Apply cron job
RUN crontab /etc/cron.d/my-cron-job

# Create the log file to be able to run tail
RUN touch /var/log/cron.log

# Run the cron service and the main application
CMD cron && tail -f /var/log/cron.log

# Expose port 9000 and start php-fpm server
EXPOSE 9000

CMD ["php-fpm"]
