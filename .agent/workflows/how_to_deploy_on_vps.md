---
description: Comprehensive guide to deploying Servio/Servio on a VPS (GoDaddy/Ubuntu)
---

This guide steps you through deploying the application on a fresh Ubuntu VPS (e.g., from GoDaddy).

## 1. Initial Server Setup & Essentials

SSH into your VPS:
```bash
ssh root@your_server_ip
```

Update system packages:
```bash
apt update && apt upgrade -y
apt install -y git curl unzip zip software-properties-common
```

## 2. Install MongoDB

Install MongoDB (Assuming Ubuntu 22.04 LTS). For other versions, check official MongoDB docs.

```bash
curl -fsSL https://pgp.mongodb.com/server-7.0.asc | \
   sudo gpg -o /usr/share/keyrings/mongodb-server-7.0.gpg \
   --dearmor

echo "deb [ arch=amd64,arm64 signed-by=/usr/share/keyrings/mongodb-server-7.0.gpg ] https://repo.mongodb.org/apt/ubuntu jammy/mongodb-org/7.0 multiverse" | \
   sudo tee /etc/apt/sources.list.d/mongodb-org-7.0.list

apt update
apt install -y mongodb-org

systemctl start mongod
systemctl enable mongod
```

## 3. Install PHP 8.4

Add PHP repository:
```bash
add-apt-repository ppa:ondrej/php -y
apt update
```

Install PHP and necessary extensions:
```bash
apt install -y php8.4 php8.4-fpm php8.4-cli php8.4-common php8.4-mysql php8.4-xml php8.4-curl php8.4-mbstring php8.4-zip php8.4-bcmath php8.4-intl php8.4-gd php8.4-dev php-pear
```

Install MongoDB PHP Driver:
```bash
pecl install mongodb
```

Enable MongoDB extension:
```bash
echo "extension=mongodb.so" > /etc/php/8.4/mods-available/mongodb.ini
phpenmod mongodb
systemctl restart php8.4-fpm
```

## 4. Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

## 5. Install Node.js & NPM

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

## 6. Install & Configure Nginx

```bash
apt install -y nginx
```

Create a new site configuration. Replace `your_domain.com` with your actual domain.
```bash
nano /etc/nginx/sites-available/servio
```

Paste the following configuration:
```nginx
server {
    listen 80;
    server_name your_domain.com www.your_domain.com;
    root /var/www/servio/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site and restart Nginx:
```bash
ln -s /etc/nginx/sites-available/servio /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx
```

## 7. Deploy Application

Clone the repository to `/var/www/servio`:
```bash
cd /var/www
git clone https://github.com/Ahmad-N-Mhala/Servio.git servio
cd servio
```

Setup Environment:
```bash
cp .env.example .env
nano .env
```
Edit `.env` to match your production settings (URL, MongoDB credentials, App Key). Set `APP_ENV=production` and `APP_DEBUG=false`.

Install Dependencies:
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

Clear Corrupted Compiled Caches:
```bash
# This prevents "Class translator does not exist" 404 errors during caching
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/views/*.php
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
```

> [!WARNING]
> Do NOT run `php artisan optimize` or `php artisan route:cache` as caching routes is incompatible with dynamic translation mappings and triggers a `404 Not Found` error.

Set Permissions:
```bash
chown -R www-data:www-data /var/www/servio
chmod -R 775 /var/www/servio/storage
chmod -R 775 /var/www/servio/bootstrap/cache
```

Generate Key:
```bash
php artisan key:generate
php artisan storage:link
```

## 8. Database Restore

Upload your local backup (`database_backups/*.gz`) to the VPS (using `scp` or SFTP).
Then restore it:

```bash
mongorestore --gzip --archive=backup_file.gz --nsInclude="servio.*"
```

## 9. Setup SSL (HTTPS)

```bash
apt install -y python3-certbot-nginx
certbot --nginx -d your_domain.com -d www.your_domain.com
```

## 10. Process Manager (Supervisor)

To keep queues running:
```bash
apt install -y supervisor
```

Create worker config:
```bash
nano /etc/supervisor/conf.d/servio-worker.conf
```

Content:
```ini
[program:servio-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/servio/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/servio/storage/logs/worker.log
stopwaitsecs=3600
```

Start Supervisor:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start servio-worker:*
```

Done! Your app should be live.

## 11. Updating an Existing Deployment

To update the application to the latest version as the standard `servioadmin` user without switching to `root`, run the following commands:

```bash
# 1. Re-assign folder ownership back to servioadmin
sudo chown -R servioadmin:www-data /var/www/servio
sudo chmod -R 775 /var/www/servio

# 2. Allow Git to run in this folder as servioadmin
git config --global --add safe.directory /var/www/servio

# 3. Discard any local conflicts and pull latest changes
git fetch origin
git reset --hard origin/main

# 4. Build the latest frontend assets
npm run build

# 5. Clear caches (Warning: Do NOT use php artisan optimize or route:cache)
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/views/*.php
```
