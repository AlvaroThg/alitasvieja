#!/bin/bash
set -e

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
