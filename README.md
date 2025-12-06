# 🍽️ RestaurFy - Multi-Tenant Restaurant Management System

A comprehensive multi-tenant restaurant management application built with Laravel and Vue.js.

## ✨ Features

- 🏢 **Multi-Tenancy** - Support for multiple restaurants with isolated databases
- 🌍 **Multi-Language** - English, Arabic, Chinese, and French support
- 🔐 **Authentication** - Secure login system with tenant isolation
- 📊 **Dashboard** - Comprehensive management dashboard
- 🎨 **Modern UI** - Built with Vue.js 3 and Inertia.js

## 🚀 Quick Start

### Prerequisites

- PHP 8.1+
- PostgreSQL 14+
- Node.js 18+
- Composer

### Installation

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd RestoFy-main
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Set up environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database in `.env`**
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=restaurfy_central
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Restore database backups**
   ```bash
   createdb restaurfy_central
   createdb restaurfy_tenant_ahmadtest
   
   psql -d restaurfy_central < database/backups/restaurfy_central.sql
   psql -d restaurfy_tenant_ahmadtest < database/backups/restaurfy_tenant_ahmadtest.sql
   ```

6. **Add to hosts file**
   ```
   127.0.0.1    ahmadtest.localhost
   ```

7. **Start the application**
   ```bash
   # Terminal 1
   php artisan serve
   
   # Terminal 2
   npm run dev
   ```

8. **Access the application**
   
   Visit: `http://ahmadtest.localhost:8000/en/login`
   
   **Login credentials:**
   - Email: `admin@ahmadtest.com`
   - Password: `password`

## 📖 Documentation

For detailed setup instructions, troubleshooting, and more, see [SETUP_GUIDE.md](SETUP_GUIDE.md)

## 🛠️ Technology Stack

- **Backend:** Laravel 10.x
- **Frontend:** Vue.js 3 + Inertia.js
- **Database:** PostgreSQL
- **Multi-tenancy:** stancl/tenancy
- **Localization:** mcamara/laravel-localization

## 📁 Project Structure

```
RestoFy-main/
├── app/                 # Application logic
├── database/
│   ├── backups/        # SQL database backups
│   └── migrations/     # Database migrations
├── resources/
│   ├── js/            # Vue.js components
│   └── views/         # Blade templates
├── routes/            # Application routes
└── public/            # Public assets
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

[Your License Here]

## 👨‍💻 Author

Ahmad Mhala

---

**Note:** This is a multi-tenant application. Each restaurant operates as an independent tenant with its own isolated database.
