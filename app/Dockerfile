FROM php:8.3.9-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd sockets

RUN docker-php-ext-install fileinfo opcache

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# set your user name, ex: user=carlos
ARG user=projetoroot
ARG uid=1000

## Diretório da aplicação
ARG APP_DIR=/var/www/app

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Install redis
RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

WORKDIR $APP_DIR
RUN cd $APP_DIR
RUN chown www-data:www-data $APP_DIR

COPY --chown=www-data:www-data ./app .
# Ativar essas linhas quando quiser atualizar as dependencias do projeto
# RUN rm -rf vendor
# RUN composer install --no-interaction

# Set permissions for storage and bootstrap/cache directories
USER root
RUN chmod -R 775 storage bootstrap/cache
RUN chmod -R 775 storage/framework/views

# Copy custom configurations PHP
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

USER $user
