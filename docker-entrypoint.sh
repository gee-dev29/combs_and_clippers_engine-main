#!/bin/sh
set -e

cd /var/www

# Wait for database to be ready
echo "Waiting for database connection..."
while ! php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; do
    echo "Database not ready, waiting..."
    sleep 2
done

echo "Database is ready, running migrations..."
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
# npm run production

exec supervisord -n -c /etc/supervisor/conf.d/supervisor.conf