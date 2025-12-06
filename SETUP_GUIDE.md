# RestaurFy - Setup Guide

This guide will help you set up and run the RestaurFy multi-tenant restaurant management application on your local machine.

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.1 or higher**
- **Composer** (PHP dependency manager)
- **Node.js 18.x or higher** and **npm**
- **PostgreSQL 14 or higher**
- **Git**

---

## Step 1: Clone the Repository

```bash
git clone <your-repository-url>
cd RestoFy-main
```

---

## Step 2: Install Dependencies

### Install PHP Dependencies

```bash
composer install
```

### Install Node.js Dependencies

```bash
npm install
```

---

## Step 3: Environment Configuration

### Copy Environment File

```bash
cp .env.example .env
```

### Configure Database

Edit the `.env` file and set your database credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=restaurfy_central
DB_USERNAME=your_postgres_username
DB_PASSWORD=your_postgres_password
```

**Important:** Replace `your_postgres_username` and `your_postgres_password` with your actual PostgreSQL credentials.

### Generate Application Key

```bash
php artisan key:generate
```

---

## Step 4: Database Setup

### Create Databases

Create the required PostgreSQL databases:

```bash
# Connect to PostgreSQL
psql -U your_postgres_username

# Create central database
CREATE DATABASE restaurfy_central;

# Create tenant database
CREATE DATABASE restaurfy_tenant_ahmadtest;

# Exit psql
\q
```

### Restore Database Backups

The project includes database backups in the `database/backups/` directory. Restore them:

```bash
# Restore central database
psql -U your_postgres_username -d restaurfy_central < database/backups/restaurfy_central.sql

# Restore tenant database
psql -U your_postgres_username -d restaurfy_tenant_ahmadtest < database/backups/restaurfy_tenant_ahmadtest.sql
```

**Alternative:** If you want to start fresh without the backup data:

```bash
# Run migrations
php artisan migrate

# Run seeders (if available)
php artisan db:seed
```

---

## Step 5: Configure Hosts File

The application uses subdomain-based multi-tenancy. You need to add the tenant domain to your hosts file.

### On macOS/Linux:

```bash
sudo nano /etc/hosts
```

Add this line:

```
127.0.0.1    ahmadtest.localhost
```

Save and exit (`Ctrl + X`, then `Y`, then `Enter`).

### On Windows:

1. Open Notepad as Administrator
2. Open file: `C:\Windows\System32\drivers\etc\hosts`
3. Add this line:
   ```
   127.0.0.1    ahmadtest.localhost
   ```
4. Save the file

---

## Step 6: Build Frontend Assets

```bash
npm run build
```

For development with hot-reload:

```bash
npm run dev
```

---

## Step 7: Start the Application

### Start Laravel Development Server

In one terminal window:

```bash
php artisan serve
```

The server will start at `http://localhost:8000`

### Start Vite Development Server (For Development)

In another terminal window:

```bash
npm run dev
```

---

## Step 8: Access the Application

### Tenant Login

Open your browser and navigate to:

```
http://ahmadtest.localhost:8000/en/login
```

### Login Credentials

Use the following credentials to log in:

```
Email: admin@ahmadtest.com
Password: password
```

---

## Available Locales

The application supports multiple locales:

- **English:** `/en/login`
- **Arabic:** `/ar/login`
- **Chinese:** `/zh/login`
- **French:** `/fr/login`

You can switch between locales by changing the URL prefix.

---

## Common Issues & Troubleshooting

### Issue: "Connection refused" or Database Error

**Solution:** Make sure PostgreSQL is running and the credentials in `.env` are correct.

```bash
# Check if PostgreSQL is running (macOS)
brew services list

# Start PostgreSQL if not running
brew services start postgresql@14
```

### Issue: "Tenant not found"

**Solution:** Make sure you're accessing the application through `ahmadtest.localhost:8000`, not `localhost:8000`.

### Issue: Assets not loading

**Solution:** Make sure both `php artisan serve` and `npm run dev` are running in separate terminal windows.

### Issue: "Class not found" errors

**Solution:** Clear the cache and regenerate autoload files:

```bash
composer dump-autoload
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## Database Backup & Restore

### Creating a New Backup

To create a fresh backup of your databases:

```bash
# Backup central database
pg_dump -U your_postgres_username -d restaurfy_central > database/backups/restaurfy_central_$(date +%Y%m%d).sql

# Backup tenant database
pg_dump -U your_postgres_username -d restaurfy_tenant_ahmadtest > database/backups/restaurfy_tenant_ahmadtest_$(date +%Y%m%d).sql
```

---

## Project Structure

```
RestoFy-main/
├── app/                    # Application logic
├── database/
│   ├── backups/           # Database SQL backups
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── public/                # Public assets
├── resources/
│   ├── js/               # Vue.js components
│   └── views/            # Blade templates
├── routes/               # Application routes
├── .env                  # Environment configuration
├── composer.json         # PHP dependencies
└── package.json          # Node.js dependencies
```

---

## Technology Stack

- **Backend:** Laravel 10.x (PHP)
- **Frontend:** Vue.js 3 with Inertia.js
- **Database:** PostgreSQL
- **Multi-tenancy:** Laravel Tenancy (stancl/tenancy)
- **Styling:** CSS
- **Localization:** mcamara/laravel-localization

---

## Development Workflow

### Starting Development Servers

Every time you want to work on the project:

1. **Terminal 1:** Start Laravel server
   ```bash
   php artisan serve
   ```

2. **Terminal 2:** Start Vite dev server
   ```bash
   npm run dev
   ```

3. **Browser:** Navigate to `http://ahmadtest.localhost:8000/en/login`

### Stopping the Servers

Press `Ctrl + C` in each terminal window.

**Note:** Your data persists in the PostgreSQL database, so stopping the servers does not delete any data.

---

## Creating Additional Tenants

To create a new tenant, you'll need to:

1. Create a new tenant in the central database
2. Run tenant-specific migrations
3. Add the tenant domain to your hosts file

Example:

```bash
php artisan tinker

# In tinker:
$tenant = \App\Models\Tenant::create([
    'id' => 'newrestaurant',
    'name' => 'New Restaurant'
]);

$tenant->domains()->create([
    'domain' => 'newrestaurant.localhost'
]);

exit
```

Then add to your hosts file:
```
127.0.0.1    newrestaurant.localhost
```

---

## Support & Documentation

For more information about the technologies used:

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Laravel Tenancy Documentation](https://tenancyforlaravel.com/)

---

## License

[Your License Here]

---

## Contact

For questions or support, please contact [Your Contact Information]
