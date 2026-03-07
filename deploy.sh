#!/bin/bash

set -e

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

# 5. Force delete stale cache AGAIN after composer (ensures no dev-only providers remain)
rm -f /var/www/servio/bootstrap/cache/services.php
rm -f /var/www/servio/bootstrap/cache/packages.php

# 6. Re-discover packages cleanly (respects dont-discover in composer.json)
php artisan package:discover --ansi

npm install
npm run build

# 7. Clear & rebuild application caches
php artisan optimize:clear
php artisan view:cache

# 8. Hand everything securely back to the web server
sudo chown -R www-data:www-data /var/www/servio
sudo chmod -R 775 /var/www/servio/storage
sudo chmod -R 775 /var/www/servio/bootstrap/cache

# 9. Reboot PHP cache
sudo systemctl restart php8.2-fpm

echo "✅ Server successfully deployed with zero errors!"
