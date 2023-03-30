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
RUN docker-php-ext-install bcmath mbstring

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Local files
RUN cp /etc/nginx/sites-available/default /etc/nginx/sites-available/default.backup
RUN sed -i 's/www-data/root/g' /etc/nginx/nginx.conf
RUN sed -i 's/www-data/root/g' /usr/local/etc/php-fpm.d/www.conf
COPY cd/nginx/conf.d/default.conf /etc/nginx/sites-available/default
COPY cd/php/local.ini /usr/local/etc/php/conf.d/local.ini

RUN echo "#!/bin/sh\n\nnginx -t\nservice nginx start\nphp-fpm -R -F" > /custom-entrypoint.sh \
    && chmod +x /custom-entrypoint.sh

# Create system user to run Composer and Artisan Commands
# RUN addgroup --gid $gid $user \
#     && adduser --disabled-password --gecos '' --home /home/$user --uid $uid --gid $gid $user \
#     && adduser $user www-data \
#     && adduser $user sudo \
#     && adduser $user root
# RUN echo '%sudo ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers
# RUN mkdir -p /home/$user/.composer \
#     && chown -R $user:$user /home/$user

EXPOSE 80

ENTRYPOINT [ "/custom-entrypoint.sh" ]
