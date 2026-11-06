#!/bin/bash
set -e

PORT="${PORT:-80}"

# Configurar Apache para escuchar en el puerto dinámico de Render ($PORT)
echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed -ri -e "s!<VirtualHost \*:.*>!<VirtualHost \*:${PORT}>!g" /etc/apache2/sites-available/*.conf

# Optimización de Laravel en producción
if [ -n "$APP_KEY" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Iniciar Apache en primer plano
exec apache2-foreground
