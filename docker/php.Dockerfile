# Use PHP 8.2 FPM as the base image
FROM php:8.2-fpm

# Install required packages and Supervisor
RUN apt-get update && \
    apt-get install -y \
    supervisor \
    procps \
    libpq-dev \
    zlib1g-dev \
    libmemcached-dev \
    curl \
    vim \
    zip \
    librabbitmq-dev \
    libssl-dev && \
    docker-php-ext-install mysqli pdo_mysql pdo_pgsql pgsql && \
    pecl install -o -f redis amqp && \
    docker-php-ext-enable redis amqp && \
    rm -rf /var/lib/apt/lists/* /tmp/pear

# Copy application files
COPY ./app /var/www/html

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# Configure PHP-FPM
COPY docker/etc/php/php.ini-development /usr/local/etc/php/php.ini
COPY docker/etc/php/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

# Create directory for Supervisor configuration
RUN mkdir -p /var/log/supervisor /etc/supervisor/conf.d

# Copy Supervisor configuration files
COPY docker/etc/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/etc/supervisor/conf.d/worker.conf /etc/supervisor/conf.d/worker.conf

RUN mkdir -p /var/www/html/public/var && \
    chown -R www-data:www-data /var/www/html/var && \
    chmod -R 777 /var/www/html/var

# Set working directory
WORKDIR /var/www/html

# Expose PHP-FPM port
EXPOSE 9000

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]


