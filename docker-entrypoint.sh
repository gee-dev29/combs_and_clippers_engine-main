#!/bin/sh
set -e

cd /var/www

# Wait for database to be ready using a simpler method
echo "Waiting for database connection..."
while ! nc -z mysql 3306; do
    echo "Database not ready, waiting..."
    sleep 2
done

echo "Database is ready, running migrations..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan migrate --force || true
php artisan optimize:clear || true
php artisan optimize || true
# npm run production

exec supervisord -n -c /etc/supervisor/conf.d/supervisor.conf