#!/bin/sh
set -e

cd /var/www
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
# npm run production

exec supervisord -n -c /etc/supervisor/conf.d/supervisor.conf