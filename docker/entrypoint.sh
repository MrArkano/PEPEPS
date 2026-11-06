#!/bin/bash
set -e

# Adaptar el puerto si el proveedor en la nube asigna la variable  (ej. Railway, Render, Fly.io, Heroku)
if [ -n  ]; then
    sed -i s/80//g /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf
fi

# Optimización en producción cuando APP_KEY está definida
if [ -n  ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Iniciar Apache en primer plano
exec apache2-foreground
