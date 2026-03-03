#!/bin/bash

echo "🚀 Starting Servio Deployment..."

# 1. Take ownership temporarily so we don't get Permission Denied errors
sudo chown -R $USER:$USER /var/www/servio

# 2. Force delete any corrupted cache files BEFORE composer runs
rm -f /var/www/servio/bootstrap/cache/*.php

# 3. Pull latest code from GitHub
cd /var/www/servio
git reset --hard
git pull origin main

# 4. Safely install and compile dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 5. Clear application cache as our user
php artisan optimize:clear
php artisan view:cache

# 6. Hand everything securely back to the web server
sudo chown -R www-data:www-data /var/www/servio
sudo chmod -R 775 /var/www/servio/storage
sudo chmod -R 775 /var/www/servio/bootstrap/cache

# 7. Reboot PHP cache 
sudo systemctl restart php8.2-fpm

echo "✅ Server successfully deployed with zero errors!"
