#!/bin/bash

chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 storage bootstrap/cache

cd /var/www/html

echo "Install application package  (npm and composer)"
npm install
composer install

php artisan key:generate
php artisan optimize:clear

echo "Run migration for database"
php artisan migrate

composer dump-autoload

echo "Cache, config, view cleaning and optimize..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan config:cache

php artisan optimize:clear

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

php artisan db:seed
php artisan providers:sync
echo "Application ready."
