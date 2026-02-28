---
description: How to set up and run the application on a new machine (Mac/Linux)
---

This guide outlines the steps to set up the RestaurFy application on a new development machine.

## 1. System Requirements & Setup

Ensure you have the following installed. You can use the provided `setup_env.sh` script on macOS to install these automatically.

- **PHP** (>= 8.2)
- **Composer**
- **Node.js** & **NPM**
- **PostgreSQL** (v14+)
- **Redis**

### Automatic Setup (macOS)
Run the setup script included in the repository:
```bash
./setup_env.sh
```

## 2. Clone the Repository

```bash
git clone <repository-url>
cd Servio-main
```

## 3. Install Dependencies

### Backend (Laravel)
```bash
composer install
```

### Frontend (Vue/Inertia)
```bash
npm install
```

## 4. Environment Configuration

1.  **Create .env file**:
    If `.env.example` exists:
    ```bash
    cp .env.example .env
    ```
    *Note: If `.env.example` is missing, you may need to copy the `.env` file from your existing machine or ask a team member for a copy.*

2.  **Generate Application Key**:
    ```bash
    php artisan key:generate
    ```

3.  **Configure Database**:
    Open `.env` and update the database credentials:
    ```ini
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=Servio
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

## 5. Database Setup

1.  **Create the Database**:
    Make sure the database specified in `DB_DATABASE` exists in PostgreSQL.
    ```bash
    createdb Servio
    ```

2.  **Run Migrations**:
    ```bash
    php artisan migrate
    ```

3.  **Seed Database (Optional)**:
    ```bash
    php artisan db:seed
    ```

## 6. Running the Application

You need to run both the backend server and the frontend development server.

### Terminal 1: Backend
```bash
php artisan serve
```

### Terminal 2: Frontend
```bash
npm run dev
```

### Terminal 3: Queue Worker (REQUIRED for SMS/Email)
```bash
php artisan queue:work
```

**Tip:** You can run all three automatically using `./run_dev.sh`.

Access the application at `http://localhost:8000`.

## 7. Importing Existing Databases (Optional)

If you have exported SQL dumps from another machine (e.g., `database/dumps/`), you can import them:

1.  **Drop existing databases** (if any):
    ```bash
    dropdb Servio
    # Drop tenant DBs if they exist
    ```

2.  **Create and Import Central DB**:
    ```bash
    createdb Servio
    psql Servio < database/dumps/central.sql
    ```

3.  **Create and Import Tenant DBs**:
    Check the dump filenames to know the tenant DB names.
    ```bash
    createdb Servio_tenant_demo
    psql Servio_tenant_demo < database/dumps/tenant_demo.sql
    ```

