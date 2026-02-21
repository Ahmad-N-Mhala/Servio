---
description: How to run the Servio project and login credentials
---

This workflow describes the steps to set up and run the Servio application locally, and provides the default login credentials.

# 1. Setup & Installation (One-time)

If you haven't set up the project yet, run the following commands:

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Setup Environment File
cp .env.example .env
php artisan key:generate
```

Make sure your database credentials in `.env` are correct.

# 2. Database Setup

**Option 1: Restore Database Backup (Recommended)**
This restores the pre-configured environment with all demo data.

```bash
mongorestore --archive=database_dump.gz --gzip
```

**Option 2: Fresh Install**
This runs migrations and seeds default data.

```bash
php artisan migrate --seed
// turbo
php artisan db:seed --class=DashboardDemoSeeder
```

# 3. Running the Application

You need two terminal windows running simultaneously:

**Terminal 1 (Backend):**
```bash
// turbo
php artisan serve
```

**Terminal 2 (Frontend):**
```bash
// turbo
npm run dev
```

Access the application at: `http://127.0.0.1:8000`

# 4. Login Credentials

**Super Admin (Admin Panel)**
- **URL**: `/login` (redirects to admin dashboard if super admin)
- **Email**: `superadmin@Servio.com`
- **Password**: `password`

**Restaurant Owner (Tenant Panel)**
- **URL**: `/login`
- **Email**: `admin@demo.com`
- **Password**: `password`
