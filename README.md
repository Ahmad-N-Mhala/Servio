# RestaurFy - Multi-Tenant Restaurant Management SaaS

A comprehensive, scalable SaaS platform for restaurant management built with Laravel 11, Vue 3, Inertia.js, and TypeScript. Features multi-tenancy, internationalization (i18n), RTL support, subscription management, and role-based access control.

## 🚀 Tech Stack

### Backend
- **Laravel 11** - PHP framework
- **PostgreSQL** - Primary database (central + tenant databases)
- **Redis** - Caching, sessions, and queues
- **Stancl/Tenancy** - Multi-tenancy package
- **Spatie Permissions** - Role-based access control
- **Spatie Translatable** - Multi-language support
- **Laravel Horizon** - Queue monitoring
- **Laravel Octane** - High-performance server
- **Laravel Sanctum** - API authentication
- **Stripe** - Payment processing

### Frontend
- **Vue 3** - Progressive JavaScript framework
- **TypeScript** - Type safety
- **Inertia.js** - SPA without API complexity
- **Pinia** - State management
- **Vue I18n** - Frontend internationalization
- **Tailwind CSS** - Utility-first CSS
- **Headless UI** - Accessible UI components
- **Heroicons** - Icon library

## 📋 Features

### Core Features
- ✅ **Multi-Tenancy** - Isolated databases per restaurant (subdomain-based)
- ✅ **Subscription System** - Stripe integration with 3 plans (Basic, Pro, Enterprise)
- ✅ **Internationalization** - 6 languages (English, Arabic, French, Spanish, German, Chinese)
- ✅ **RTL Support** - Full right-to-left support for Arabic
- ✅ **Role-Based Access** - Owner, Manager, Waiter, Chef roles
- ✅ **Menu Management** - Categories and items with multi-language names
- ✅ **Order Management** - Track orders from creation to completion
- ✅ **Staff Management** - Invite and manage restaurant staff
- ✅ **Public Menu API** - Public endpoint for displaying restaurant menus
- ✅ **Customer Loyalty System** - Points, rewards, and tier-based program
- ✅ **Automatic Customer Creation** - Customers auto-created from orders (phone-based)
- ✅ **Points Earning** - Automatic points on order completion (1 point per currency unit)
- ✅ **Rewards Redemption** - Customers can redeem points for discounts/free items
- ✅ **Loyalty Tiers** - Bronze, Silver, Gold, Platinum based on total spending

### Architecture
- **Central Database** - Stores tenants, plans, and subscriptions
- **Tenant Databases** - Isolated database per restaurant (prefix: `restaurfy_tenant_`)
- **Domain/Subdomain Routing** - Automatic tenant detection
- **Redis Caching** - Tenant-aware caching with locale keys
- **Queue System** - Background jobs with Horizon monitoring

## 📁 Project Structure

```
RestaurFy/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Tenant/
│   │   │       ├── DashboardController.php
│   │   │       ├── MenuController.php          # Menu CRUD operations
│   │   │       ├── OnboardingController.php    # Tenant signup flow
│   │   │       └── PublicMenuController.php    # Public menu API
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php       # RTL & locale sharing
│   ├── Models/
│   │   ├── Tenant.php                          # Central tenant model
│   │   ├── Plan.php                            # Subscription plans
│   │   ├── User.php                            # Tenant users
│   │   ├── Restaurant.php                      # Restaurant details
│   │   ├── Staff.php                           # Staff members
│   │   ├── MenuCategory.php                    # Menu categories
│   │   ├── MenuItem.php                        # Menu items
│   │   ├── Order.php                           # Orders
│   │   ├── OrderItem.php                       # Order line items
│   │   ├── Subscription.php                    # Tenant subscriptions
│   │   └── Payment.php                         # Payment records
│   └── Jobs/
│       └── StaffInviteJob.php                  # Staff invitation emails
├── config/
│   ├── tenancy.php                             # Multi-tenancy config
│   ├── laravellocalization.php                 # i18n config
│   ├── permission.php                          # RBAC config
│   └── services.php                            # Stripe config
├── database/
│   ├── migrations/
│   │   ├── central/                            # Central DB migrations
│   │   │   ├── create_tenants_table.php
│   │   │   └── create_plans_table.php
│   │   └── tenant/                             # Tenant DB migrations
│   │       ├── create_users_table.php
│   │       ├── create_restaurants_table.php
│   │       ├── create_staff_table.php
│   │       ├── create_menu_categories_table.php
│   │       ├── create_menu_items_table.php
│   │       ├── create_orders_table.php
│   │       ├── create_order_items_table.php
│   │       ├── create_subscriptions_table.php
│   │       ├── create_payments_table.php
│   │       └── create_permission_tables.php
│   └── seeders/
│       ├── PlanSeeder.php                      # Seed subscription plans
│       └── RoleSeeder.php                      # Seed user roles
├── lang/                                       # Backend translations
│   ├── en/
│   │   ├── auth.php
│   │   ├── validation.php
│   │   ├── dashboard.php
│   │   ├── menu.php
│   │   └── orders.php
│   └── ar/                                     # Arabic translations
├── resources/
│   ├── js/
│   │   ├── app.ts                              # Vue app entry
│   │   ├── Pages/
│   │   │   ├── Dashboard/Home.vue
│   │   │   ├── Menu/Builder.vue                # Menu management UI
│   │   │   ├── Staff/Manage.vue
│   │   │   ├── Orders/Live.vue
│   │   │   ├── Reports/Sales.vue
│   │   │   └── Onboarding/
│   │   ├── locales/                            # Frontend translations
│   │   │   ├── en/index.ts
│   │   │   ├── ar/index.ts
│   │   │   └── ...
│   │   └── stores/
│   │       └── tenant.ts                       # Pinia store
│   ├── css/
│   │   └── app.css                             # Tailwind + RTL styles
│   └── views/
│       └── app.blade.php                       # Inertia root template
└── routes/
    ├── web.php                                 # Web routes (locale + tenant)
    └── api.php                                 # API routes (public menu)
```

## 🛠️ Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- PostgreSQL 14+
- Redis 6+
- Stripe account (for payments)

### Step 1: Clone & Install Dependencies

```bash
cd RestaurFy
composer install
npm install
```

### Step 2: Environment Configuration

Copy `.env.example` to `.env` and configure:

```bash
cp .env.example .env
php artisan key:generate
```

**Required Environment Variables:**

```env
APP_NAME=RestaurFy
APP_URL=http://restaurfy.test

# Database (Central)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=restaurfy_central
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Stripe
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Tenancy
CENTRAL_DOMAIN=restaurfy.test
```

### Step 3: Database Setup

```bash
# Create central database
createdb restaurfy_central

# Run central migrations
php artisan migrate --path=database/migrations/central

# Seed plans
php artisan db:seed --class=PlanSeeder
```

### Step 4: Database Import/Export

The application includes artisan commands to export and import all databases (central + tenant databases) for easy backup and sharing.

#### Exporting Databases

Export all databases to `database/dumps/`:

```bash
php artisan db:export
```

Or specify a custom output directory:

```bash
php artisan db:export --output=/path/to/dumps
```

This creates:
- `central_<dbname>.sql` - Central database dump (PostgreSQL or MySQL)
- `tenant_<tenant_id>.sql` - MySQL dump for each tenant database

**Example output:**
```
Exporting databases...
Exporting central database (mysql)...
  ✅ Central DB exported: database/dumps/central_restaurfy.sql (39K)
Exporting tenant databases (MySQL)...
  ✅ Tenant 'foo' (foo): database/dumps/tenant_foo.sql (28K)
✅ All databases exported successfully!
```

#### Importing Databases

Import all databases from `database/dumps/`:

```bash
php artisan db:import
```

Or specify a custom input directory:

```bash
php artisan db:import --input=/path/to/dumps
```

**⚠️ Warning:** This will drop and recreate existing databases. Use `--force` to skip confirmation prompts:

```bash
php artisan db:import --force
```

#### Manual Import (Alternative)

If you prefer to import manually:

**Central Database:**
```bash
# PostgreSQL
createdb -U forge restaurfy
psql -U forge -d restaurfy < database/dumps/central_restaurfy.sql

# MySQL
mysql -u forge -p restaurfy < database/dumps/central_restaurfy.sql
```

**Tenant Databases:**
```bash
# For each tenant dump:
mysql -u forge -p <tenant_db_name> < database/dumps/tenant_<tenant_id>.sql
```

#### Requirements

- **PostgreSQL**: `pg_dump` and `psql` commands must be available
- **MySQL**: `mysqldump` and `mysql` commands must be available
- Database credentials must be configured in `.env`

**Note:** Dumps are created without database ownership/ACL information for portability. Make sure to backup your databases before importing if you have important data.

### Step 5: Build Frontend

```bash
npm run dev    # Development
npm run build  # Production
```

### Step 6: Start Servers

```bash
# Laravel development server
php artisan serve

# Vite dev server (in another terminal)
npm run dev

# Horizon (queue monitoring)
php artisan horizon

# Octane (optional, for production)
php artisan octane:start
```

## 🔄 Multi-Tenancy Flow

### Tenant Onboarding

1. **Visit** `http://restaurfy.test/onboard`
2. **Choose** subdomain (e.g., `myrestaurant`)
3. **Select** subscription plan
4. **Complete** Stripe checkout
5. **System creates:**
   - Tenant record in central DB
   - Domain record (`myrestaurant.restaurfy.test`)
   - Tenant database (`restaurfy_tenant_<uuid>`)
   - Runs tenant migrations
   - Seeds roles
   - Creates owner user & restaurant

### Accessing Tenant

- **Central domain:** `http://restaurfy.test` (onboarding)
- **Tenant subdomain:** `http://myrestaurant.restaurfy.test` (dashboard)

## 📝 Menu Management

### Creating Categories & Items

**Via Dashboard (Authenticated):**
- Navigate to `/menu` in tenant dashboard
- Create categories with multi-language names
- Add items to categories
- Set prices, images, allergens

**Menu Data Structure:**
```php
// Category
{
  "name": {
    "en": "Appetizers",
    "ar": "المقبلات",
    "fr": "Entrées"
  },
  "description": "...",
  "sort_order": 0,
  "is_active": true
}

// Item
{
  "name": {
    "en": "Hummus",
    "ar": "حمص"
  },
  "price": 25.00,
  "currency": "AED",
  "description": "...",
  "allergens": ["sesame"],
  "is_available": true
}
```

### Public Menu API

**Endpoint:** `GET /api/menu` or `GET /api/menu/{locale}`

**Example:**
```bash
# Default locale (restaurant's locale)
curl https://myrestaurant.restaurfy.test/api/menu

# Specific locale
curl https://myrestaurant.restaurfy.test/api/menu/ar
```

**Response:**
```json
{
  "restaurant": {
    "name": "My Restaurant",
    "slug": "my-restaurant",
    "currency": "AED",
    "locale": "en"
  },
  "categories": [
    {
      "id": 1,
      "name": "Appetizers",
      "description": "Start your meal",
      "items": [
        {
          "id": 1,
          "name": "Hummus",
          "description": "Creamy chickpea dip",
          "price": 25.00,
          "currency": "AED",
          "image": "https://...",
          "allergens": ["sesame"]
        }
      ]
    }
  ]
}
```

## 🌍 Internationalization

### Supported Locales
- `en` - English (default)
- `ar` - Arabic (RTL)
- `fr` - French
- `es` - Spanish
- `de` - German
- `zh` - Chinese

### How It Works

1. **URL-based locale:** `/{locale}/dashboard` (e.g., `/ar/dashboard`)
2. **Auto-detection:** Browser language → Session → Default
3. **RTL Support:** Automatic `dir="rtl"` for Arabic
4. **Translations:**
   - Backend: `lang/{locale}/*.php`
   - Frontend: `resources/js/locales/{locale}/index.ts`

### Adding Translations

**Backend:**
```php
// lang/en/menu.php
return ['title' => 'Menu'];

// lang/ar/menu.php
return ['title' => 'القائمة'];
```

**Frontend:**
```typescript
// resources/js/locales/en/index.ts
export default {
  menu: { title: 'Menu' }
};

// resources/js/locales/ar/index.ts
export default {
  menu: { title: 'القائمة' }
};
```

## 👥 Roles & Permissions

### Default Roles
- **owner** - Full access, subscription management
- **manager** - Restaurant operations, staff management
- **waiter** - Order management, menu viewing
- **chef** - Order preparation, menu viewing

### Using Permissions

```php
// In controller
$this->authorize('manage-menu');

// In Blade/Vue
@can('manage-menu')
    <button>Edit Menu</button>
@endcan
```

## 💳 Subscription Plans

### Default Plans (AED)

1. **Basic** - AED 99/month
   - 1 restaurant
   - Up to 5 staff
   - Basic features

2. **Pro** - AED 299/month
   - 3 restaurants
   - Unlimited staff
   - Advanced features

3. **Enterprise** - AED 799/month
   - Unlimited restaurants
   - All features
   - API access

## 🔐 Security Features

- **Tenant Isolation** - All queries scoped to current tenant
- **Role-Based Access** - Spatie Permissions
- **CSRF Protection** - Laravel built-in
- **API Authentication** - Sanctum tokens
- **Secure Payments** - Stripe webhooks
- **Input Validation** - Form requests
- **SQL Injection Protection** - Eloquent ORM

## 📊 Database Architecture

### Central Database (`restaurfy_central`)
- `tenants` - Tenant records
- `plans` - Subscription plans
- `domains` - Domain mappings (Stancl)

### Tenant Database (`restaurfy_tenant_*`)
- `users` - Restaurant users
- `restaurants` - Restaurant details
- `staff` - Staff members
- `menu_categories` - Menu categories
- `menu_items` - Menu items
- `orders` - Orders
- `order_items` - Order line items
- `subscriptions` - Subscription records
- `payments` - Payment records
- `roles`, `permissions` - RBAC tables

## 🚀 Deployment

### Production Checklist

1. **Environment:**
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Optimize:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   composer install --optimize-autoloader --no-dev
   npm run build
   ```

3. **Database:**
   ```bash
   php artisan migrate --force
   ```

4. **Queue:**
   ```bash
   php artisan horizon:install
   php artisan horizon:publish
   ```

5. **Octane (Optional):**
   ```bash
   php artisan octane:install --server=swoole
   php artisan octane:start --server=swoole --workers=4
   ```

## 📚 API Documentation

### Public Endpoints

#### Get Menu
```
GET /api/menu
GET /api/menu/{locale}
```

**Response:** Restaurant menu with categories and items

#### Loyalty Program (Public)
```
POST /api/loyalty/check-points
Body: { "phone": "+971501234567" }
Response: Customer points and tier information

GET /api/loyalty/rewards
Response: List of available rewards

POST /api/loyalty/redeem
Body: { "phone": "+971501234567", "reward_id": 1 }
Response: Redemption code and details

POST /api/loyalty/history
Body: { "phone": "+971501234567" }
Response: Points transactions and redemption history
```

### Protected Endpoints (Require Authentication)

#### Menu Management
```
GET    /menu                    - List categories & items
POST   /menu/categories         - Create category
PUT    /menu/categories/{id}    - Update category
DELETE /menu/categories/{id}    - Delete category
POST   /menu/items              - Create item
PUT    /menu/items/{id}         - Update item
DELETE /menu/items/{id}         - Delete item
```

#### Order Management
```
GET    /orders                  - List orders
POST   /orders                  - Create new order
PUT    /orders/{id}/status      - Update order status
```

#### Loyalty Management
```
GET    /loyalty                 - Loyalty dashboard (customers & rewards)
GET    /loyalty/customers/{id}  - Customer details
POST   /loyalty/rewards         - Create reward
PUT    /loyalty/rewards/{id}    - Update reward
DELETE /loyalty/rewards/{id}    - Delete reward
POST   /loyalty/customers/{id}/adjust-points - Manually adjust points
```

## 🎁 Loyalty & Rewards System

### How It Works

**Automatic Customer Creation:**
- When an order is placed with a phone number, a customer is automatically created
- Phone number is the unique identifier per restaurant
- Customer profile includes name, email, birthday, preferences

**Points Earning:**
- **1 point per 1 currency unit** (e.g., 1 AED = 1 point)
- Points are automatically awarded when order status changes to "completed"
- Points expire after 365 days
- Full transaction history is maintained

**Loyalty Tiers:**
- **Bronze** - Default tier (0-499 AED spent)
- **Silver** - 500+ AED spent
- **Gold** - 2000+ AED spent
- **Platinum** - 5000+ AED spent
- Tiers automatically update based on total spending

**Rewards System:**
- Restaurants can create rewards with points requirements
- Reward types:
  - `discount_percentage` - Percentage discount (e.g., 10% off)
  - `discount_fixed` - Fixed amount discount (e.g., 50 AED off)
  - `free_item` - Free menu item
  - `cashback` - Cashback reward
- Rewards can have validity dates and max redemption limits
- Customers receive redemption codes when redeeming

**Customer Features:**
- Check points balance via phone number (public API)
- View transaction history
- Redeem rewards
- Track redemption codes and status

### Example Flow

1. **Customer places order:**
   ```
   POST /orders
   {
     "customer_phone": "+971501234567",
     "customer_name": "Ahmed Ali",
     "items": [...],
     "total": 150.00
   }
   ```

2. **Order completed → Points automatically awarded:**
   - 150 points added to customer account
   - Customer tier updated if threshold reached
   - Transaction recorded

3. **Customer checks points:**
   ```
   POST /api/loyalty/check-points
   { "phone": "+971501234567" }
   ```

4. **Customer redeems reward:**
   ```
   POST /api/loyalty/redeem
   {
     "phone": "+971501234567",
     "reward_id": 1
   }
   Response: { "code": "ABC12345", "expires_at": "..." }
   ```

## 🧪 Testing

```bash
# Run tests
php artisan test

# With coverage
php artisan test --coverage
```

## 📝 License

MIT License

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 📞 Support

For issues and questions, please open an issue on GitHub.

---

**Built with ❤️ for restaurant owners worldwide**
