# RestaurFy - Setup Guide

This guide will help you set up and run the RestaurFy multi-tenant restaurant management application on your local machine.

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.1 or higher**
- **Composer** (PHP dependency manager)
- **Node.js 18.x or higher** and **npm**
- **MongoDB** (Local or Atlas)
- **Git**

---

## Step 1: Clone the Repository

```bash
git clone <your-repository-url>
cd Servio-main
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

Edit the `.env` file and set your MongoDB credentials:

```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=Servio_central
```

### Generate Application Key

```bash
php artisan key:generate
```

---

## Step 4: Database Setup

### Restore Database Backup

The project includes a full database backup in the root directory. Restore it using `mongorestore`:

```bash
# Restore the bundled MongoDB dump
mongorestore --archive=database_dump.gz --gzip
```

**Alternative 1: Fresh Install (Essential Data Only)**
This will set up the database structure and create the default roles, plans, and super admin.

```bash
php artisan migrate --seed
```

**Alternative 2: Fresh Install with Demo Data**
This adds sample data to populate the dashboard for testing purposes.

```bash
php artisan migrate --seed
php artisan db:seed --class=DashboardDemoSeeder
```

---

## Step 5: Configure Hosts File (Optional)

The application can run on `localhost`. However, if you want to test subdomain-based multi-tenancy (e.g., `ahmadtest.localhost`), add the domain to your hosts file.

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

### Login

Open your browser and navigate to:

```
http://localhost:8000/en/login
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

**Solution:** Make sure MongoDB is running and the credentials in `.env` are correct.

```bash
# Check if MongoDB is running (macOS)
brew services list

# Start MongoDB if not running
brew services start mongodb-community
```

### Issue: "Tenant not found"

**Solution:** Make sure you're accessing the application through the correct domain configured in your hosts file (e.g., `ahmadtest.localhost:8000`).

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
# Backup all databases
mongodump --uri="mongodb://127.0.0.1:27017" --gzip --archive=database/backups/full_backup_$(date +%Y%m%d).gz
```

---

## Project Structure

```
Servio-main/
├── app/                    # Application logic
├── database/
│   ├── backups/           # Database backups
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
- **Database:** MongoDB
- **Multi-tenancy:** Laravel Tenancy (stancl/tenancy configured for MongoDB)
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

**Note:** Your data persists in the MongoDB database, so stopping the servers does not delete any data.

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
