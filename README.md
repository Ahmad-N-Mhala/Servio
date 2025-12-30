# 🍽️ Servio - Multi-Tenant Restaurant Management System

A comprehensive multi-tenant restaurant management application built with Laravel and Vue.js.

## ✨ Features

- 🏢 **Multi-Tenancy** - Support for multiple restaurants with isolated databases
- 🌍 **Multi-Language** - English, Arabic, Chinese, and French support
- 🔐 **Authentication** - Secure login system with tenant isolation
- 📊 **Dashboard** - Comprehensive management dashboard with Net Profit metrics
- 💰 **Financial Management** - Monthly expenses, sales reports, and auto-calculated inventory costs
- 📦 **Inventory FIFO & Tracking** - Smart stock tracking with Batch management, Total Value calculation, and Expiry date alerts
- 🎨 **Modern UI** - Built with Vue.js 3 and Inertia.js

## 🚀 Quick Start

### Prerequisites

- MongoDB (running locally or via Atlas)
- Node.js 18+
- Composer

### Description

Servio is an all-in-one restaurant management platform designed to streamline operations from kitchen to table. It features multi-tenancy support, comprehensive inventory management with FIFO tracking, real-time sales reporting, and a modern, localized interface for staff and administrators.

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
   DB_CONNECTION=mongodb
   DB_HOST=127.0.0.1
   DB_PORT=27017
   DB_DATABASE=restaurfy_central
   ```

5. **Restore database backup**
   ```bash
   # Restore the bundled MongoDB dump
   mongorestore --archive=database_dump.gz --gzip
   ```

6. **Start the application**
   ```bash
   # Terminal 1
   php artisan serve
   
   # Terminal 2
   npm run dev
   ```

7. **Access the application**
   
   Visit: `http://localhost:8000/en/login`
   
   **Login credentials:**
   - Email: `admin@ahmadtest.com`
   - Password: `password`

## 📖 Documentation

For detailed setup instructions, troubleshooting, and more, see [SETUP_GUIDE.md](SETUP_GUIDE.md)

## 🛠️ Technology Stack

- **Backend:** Laravel 10.x
- **Frontend:** Vue.js 3 + Inertia.js
- **Database:** MongoDB
- **Multi-tenancy:** stancl/tenancy (configured for MongoDB)
- **Localization:** mcamara/laravel-localization

## 📁 Project Structure

```
Servio/
├── app/                 # Application logic
├── database/
│   ├── backups/        # Database backups
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
