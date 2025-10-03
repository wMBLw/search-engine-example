#!/bin/bash

# Exit on any error
set -e

echo "Starting Search Engine Example setup..."

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 storage bootstrap/cache

cd /var/www/html

echo "Installing application packages (npm and composer)..."
npm install
composer install

echo "Generating application key..."
php artisan key:generate

echo "Clearing caches..."
php artisan optimize:clear

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
until php artisan migrate:status > /dev/null 2>&1; do
    echo "Database not ready yet, waiting 2 seconds..."
    sleep 2
done

echo "Running database migrations..."
php artisan migrate --force

echo "Seeding database..."
php artisan db:seed --force

echo "Syncing providers..."
php artisan providers:sync

echo "Optimizing application..."
composer dump-autoload
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan config:cache
php artisan optimize:clear

echo "Application setup completed successfully!"
echo "Starting supervisord..."

# Start supervisord in foreground
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
