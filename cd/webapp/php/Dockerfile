FROM php:7.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    libonig-dev \
    npm \
    nginx \
    supervisor \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN pecl install -o -f redis \
    && docker-php-ext-install bcmath mbstring pdo_mysql \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Local files
RUN cp /etc/nginx/sites-available/default /etc/nginx/sites-available/default.backup
RUN sed -i 's/www-data/root/g' /etc/nginx/nginx.conf
RUN sed -i 's/www-data/root/g' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/^\(\[supervisord\]\)$/\1\nnodaemon=true/' /etc/supervisor/supervisord.conf
COPY cd/webapp/nginx/default.conf /etc/nginx/sites-available/default
COPY cd/webapp/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY cd/webapp/supervisor/conf.d/nginx.conf /etc/supervisor/conf.d/nginx.conf
COPY cd/webapp/supervisor/conf.d/php-fpm.conf /etc/supervisor/conf.d/php-fpm.conf
COPY cd/webapp/supervisor/conf.d/consumer-invoice-saved.conf /etc/supervisor/conf.d/consumer-invoice-saved.conf

RUN echo "#!/bin/sh\n\nset -e\nnginx -t\nsupervisord -c /etc/supervisor/supervisord.conf" > /custom-entrypoint.sh \
    && chmod +x /custom-entrypoint.sh

EXPOSE 80

ENTRYPOINT [ "/custom-entrypoint.sh" ]
