#!/bin/bash
set -e

echo "==> [entrypoint] Creando directorios de storage..."
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/app/public \
         storage/logs
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "==> [entrypoint] Sincronizando archivos públicos al volumen compartido..."
cp -rn /var/www/html/public-static/. /var/www/html/public/
chown -R www-data:www-data /var/www/html/public

echo "==> [entrypoint] Esperando MySQL..."
until php artisan db:monitor --databases=mysql 2>/dev/null; do
    sleep 2
done

echo "==> [entrypoint] Ejecutando migraciones..."
php artisan migrate --force

echo "==> [entrypoint] Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> [entrypoint] Creando symlink de storage..."
php artisan storage:link 2>/dev/null || true

echo "==> [entrypoint] Iniciando PHP-FPM..."
exec "$@"
