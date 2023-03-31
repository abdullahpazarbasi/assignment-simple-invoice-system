FROM php:7.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    libonig-dev \
    npm \
    nginx \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install bcmath mbstring pdo_mysql

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Local files
RUN cp /etc/nginx/sites-available/default /etc/nginx/sites-available/default.backup
RUN sed -i 's/www-data/root/g' /etc/nginx/nginx.conf
RUN sed -i 's/www-data/root/g' /usr/local/etc/php-fpm.d/www.conf
COPY cd/webapp/nginx/default.conf /etc/nginx/sites-available/default
COPY cd/webapp/php/local.ini /usr/local/etc/php/conf.d/local.ini

RUN echo "#!/bin/sh\n\nnginx -t\nservice nginx start\nphp-fpm -R -F" > /custom-entrypoint.sh \
    && chmod +x /custom-entrypoint.sh

EXPOSE 80

ENTRYPOINT [ "/custom-entrypoint.sh" ]
