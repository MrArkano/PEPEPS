# =============================================================================
# PEPEPS - Plataforma Digital de Mercados Campesinos de Santander
# Dockerfile Multi-Stage de Producción (Cloud Deployment: Render / Railway / Fly.io)
# =============================================================================

# Etapa 1: Compilación de Assets con Vite y Tailwind CSS
FROM node:20-alpine AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources/ ./resources/
COPY vite.config.js ./
RUN npm run build

# Etapa 2: Servidor PHP 8.4 con Apache y Extensión Oficial de MongoDB
FROM php:8.4-apache

# Dependencias del sistema necesarias para extensiones y utilidades
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libssl-dev \
    libzip-dev \
    zip \
    curl \
    ca-certificates \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP requeridas (mongodb, bcmath, zip, opcache, pdo_mysql)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions mongodb bcmath zip opcache pdo_mysql

# Habilitar módulos de Apache para Laravel
RUN a2enmod rewrite headers

# Configurar DocumentRoot de Apache apuntando a /var/www/html/public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Instalar Composer oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Copiar dependencias de Composer e instalar con bandera tolerante de plataforma
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-reqs

# Copiar el resto del código del proyecto
COPY . .

# Generar el autoloader final de Composer para todas las clases de app/
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative --ignore-platform-reqs

# Copiar assets compilados desde la etapa de Node
COPY --from=node_builder /app/public/build ./public/build

# Permisos para storage y bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configurar script de inicio
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
