# Usando a imagem base do PHP 8.3
FROM php:8.3-fpm

# Instalações de pacotes necessários
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip  \
    libpq-dev \
    supervisor \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd sockets

# Instala Composer globalmente
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurando o diretório de trabalho
WORKDIR /var/www

# Copiando os arquivos da aplicação
COPY . .

# Instalando dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Ajustando permissões
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Expondo a porta 80
EXPOSE 80

# Criando o arquivo de configuração do supervisor para rodar o php artisan serve e o websocket
COPY ./docker/supervisor-app.conf /etc/supervisor/conf.d/supervisor-app.conf

# Comando de inicialização do supervisor
CMD ["/usr/bin/supervisord", "-n"]
