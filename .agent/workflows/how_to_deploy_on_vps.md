---
description: Comprehensive guide to deploying RestoFy/Servio on a VPS (GoDaddy/Ubuntu)
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

## 3. Install PHP 8.2

Add PHP repository:
```bash
add-apt-repository ppa:ondrej/php -y
apt update
```

Install PHP and necessary extensions:
```bash
apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-bcmath php8.2-intl php8.2-gd php8.2-dev php-pear
```

Install MongoDB PHP Driver:
```bash
pecl install mongodb
```

Enable MongoDB extension:
```bash
echo "extension=mongodb.so" > /etc/php/8.2/mods-available/mongodb.ini
phpenmod mongodb
systemctl restart php8.2-fpm
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
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
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
