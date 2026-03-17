# Dockerfile para Laravel no Render
FROM php:8.4-fpm

# Instala extensões necessárias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    nginx

RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Permissões
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copia configuração do Nginx
COPY ./deployment/nginx.conf /etc/nginx/sites-available/default

# Atualiza dependências e gera o lock file sincronizado
RUN composer update --no-dev --optimize-autoloader

# Script de Inicialização
RUN chmod +x ./deployment/init.sh

EXPOSE 80

CMD ["./deployment/init.sh"]
