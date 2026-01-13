# 🚀 Servio Quickstart Guide

This guide provides the essential commands and credentials to run the **Servio** project.

## 🛠️ Running the Application

You need to run two terminal commands in parallel to start the application (Backend & Frontend).

### 1. Start Backend Server (Laravel)
```bash
php artisan serve
```
*Server runs at:* [http://localhost:8000](http://localhost:8000)

### 2. Start Frontend Dev Server (Vite)
```bash
npm run dev
```

---

## 🔑 Login Credentials

### **Primary Restaurant Owner**
Use this account to access the main dashboard.
- **URL:** [http://localhost:8000/en/login](http://localhost:8000/en/login)
- **Email:** `owner1@example.com`
- **Password:** `password`

### **Super Admin**
Use this account for system-wide administration.
- **Email:** `superadmin@restofy.com`
- **Password:** `password`

### **Demo Staff Accounts**
- **Manager:** `manager@restaurant.com` / `password`
- **Chef:** `chef@restaurant.com` / `password`
- **Waiter:** `waiter1@restaurant.com` / `password`

---

## 💰 Financial & Inventory Features (New)

### **Financial Section**
Located in **Growth > Financial**, this section provides:
- **Monthly Expenses**: Manual expense tracking + **Auto-calculated Inventory Purchases**.
- **Sales Reports**: Comprehensive analytics on revenue and orders.

### **📦 Inventory FIFO & Tracking**
The system now uses **First-In, First-Out (FIFO)** for inventory valuation:
- **Automatic Cost Update**: The "Cost/Unit" of an ingredient automatically reflects the cost of the **oldest batch** currently in stock.
- **Batch Tracking**: Each stock addition is tracked as a separate batch with its specific purchase price.
- **Total Value**: New column in inventory table showing the total financial value of each item (Stock × Cost).
- **Expiry Dates**: Optional expiry tracking for both new ingredients and specific stock additions.
- **Dynamic Pricing**: Once the oldest batch is finished, the ingredient price automatically updates to the next oldest batch.

---

## ⚙️ Setup Commands (If needed)

If you are setting up the project from scratch or need to reset the database:

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node dependencies
npm install

# 3. Setup Environment
cp .env.example .env
php artisan key:generate

# 4. Migrate and Seed Database
php artisan migrate:fresh --seed
# OR specifically for demo data:
php artisan db:seed --class=DashboardDemoSeeder
```

## 📝 Troubleshooting

If you encounter a blank page or errors:
1. Ensure both `php artisan serve` and `npm run dev` are running.
2. Clear cache: `php artisan optimize:clear`
3. Check logs: `tail -f storage/logs/laravel.log`
