#!/bin/bash
set -e

PORT="${PORT:-80}"

# Crear directorios de framework y storage si no existen
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Configurar Apache para escuchar en el puerto dinámico de Render ($PORT)
echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed -ri -e "s!<VirtualHost \*:.*>!<VirtualHost \*:${PORT}>!g" /etc/apache2/sites-available/*.conf

# Suprimir advertencia de ServerName en Apache
echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Optimización de Laravel en producción
if [ -n "$APP_KEY" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Asegurar permisos estrictos para www-data sobre storage y bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Iniciar Apache en primer plano
exec apache2-foreground
