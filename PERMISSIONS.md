# Permission System Documentation

This document outlines the permission system implementation in the **RestaurFy** project.

## Overview

The project uses the **[spatie/laravel-permission](https://spatie.be/docs/laravel-permission/v6/introduction)** package to manage roles and permissions. Since the project uses **MongoDB**, custom models extending `MongoDB\Laravel\Eloquent\Model` are used to interface with the package.

## Roles

Roles are defined in `config/roles.php`. This file serves as the single source of truth for available roles.

| Role Key | Display Name | Description | Level |
| :--- | :--- | :--- | :--- |
| `owner` | Restaurant Owner | Full access to all restaurant features | 100 |
| `manager` | Manager | Manage daily operations and staff | 80 |
| `head_chef` | Head Chef | Manage kitchen operations and menu | 70 |
| `kitchen_staff` | Kitchen Staff | Prepare orders and update kitchen status | 50 |
| `waiter` | Waiter | Take orders and serve customers | 40 |
| `cashier` | Cashier | Handle payments and POS operations | 40 |
| `delivery_driver` | Delivery Driver | Handle delivery orders | 30 |

## Permissions

Permissions are defined in `config/permissions.php`. This allows for easy management and reuse across the application.

### Groups
Permissions are grouped by feature area:
*   **Dashboard:** `view_dashboard`, `view_analytics`
*   **POS System:** `pos_system`, `view_pos`, `create_order`, `discount_order`, `void_order`
*   **Order Management:** `view_orders`, `edit_order`, `cancel_order`, `delete_order`, `print_bill`
*   **Kitchen Display:** `view_kitchen`, `update_item_status`, `complete_order`
*   **Menu Management:** `menu_management`, `view_menu`, `create_category`, `edit_category`, `delete_category`, `create_item`, `edit_item`, `delete_item`
*   **Table Management:** `view_tables`, `create_table`, `edit_table`, `delete_table`, 'manage_zones'
*   **Customer Management:** `view_customers`, `create_customer`, 'edit_customer', 'delete_customer'
*   **Staff Management:** `view_staff`, `create_staff`, `edit_staff`, `delete_staff`, `manage_permissions`
*   **Inventory & Stock:** `view_inventory`, `add_stock`, `deduct_stock`, `manage_suppliers`, `view_waste`, `record_waste`
*   **Loyalty Program:** `view_loyalty`, `manage_rewards`, `manage_earning_rules`, `adjust_points`
*   **Delivery Integrations:** `view_delivery_settings`, `toggle_providers`, `manage_menus_sync`
*   **Communication & Marketing:** `view_communication`, `purchase_sms_bundles`, `send_campaigns`, `manage_templates`
*   **Finance & Reports:** `view_sales_reports`, `view_expense_reports`, `view_staff_performance`, `export_reports`
*   **System Settings:** `view_settings`, `update_restaurant_profile`, `manage_billing`, `manage_printers`

## Implementation Details

### 1. Configuration
*   **Package:** `spatie/laravel-permission` (v6.0)
*   **Config File:** `config/permission.php` (points to custom models)
*   **Role Config:** `config/roles.php`
*   **Permission Config:** `config/permissions.php`

### 2. Models
Custom models are used to support MongoDB:
*   `App\Models\Permission`: Extends `MongoDB\Laravel\Eloquent\Model`, implements `Spatie\Permission\Contracts\Permission`.
*   `App\Models\Role`: Extends `MongoDB\Laravel\Eloquent\Model`, implements `Spatie\Permission\Contracts\Role`.

### 3. Database Seeding
*   `Database\Seeders\RoleSeeder.php`: Seeds the roles defined in `config/roles.php`.
*   `Database\Seeders\PermissionSeeder.php`: Seeds the permissions defined in `config/permissions.php`.
*   **Note:** Run `php artisan db:seed` to populate both.

### 4. Admin Controller (`PermissionController`)
*   **Location:** `App\Http\Controllers\Admin\PermissionController.php`
*   **Functionality:**
    *   Fetches roles and permissions from their respective config files.
    *   Retrieves assigned permissions directly from the `Role` model using `$role->permissions`.
    *   Updates permissions using `$role->syncPermissions()`.
    *   **Improvement:** The legacy `role_permissions` shadow table has been removed. The system now relies entirely on Spatie's architecture.

### 5. Route Enforcement
Permissions are enforced using middleware in `routes/web.php`.
*   **Middleware:** `permission:permission_name`
*   **Example:**
    ```php
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:view_dashboard');
    ```
*   **Behavior:** If a user attempts to access a route without the required permission, they will receive a `403 Forbidden` response.

## How to Add a New Permission
1.  Open `config/permissions.php`.
2.  Add the new permission string to the appropriate group's `permissions` array.
3.  Run `php artisan db:seed --class=PermissionSeeder` to add it to the database.
4.  Apply the middleware to the relevant route in `routes/web.php`: `->middleware('permission:new_permission_name')`.

## UI Testing

To verify the permission system from the UI, you can use the following test users. Each user has the password `password`.

| Role | Email | Expected Access |
| :--- | :--- | :--- |
| **Manager** | `manager@example.com` | Access to most features (Dashboard, Menu, Staff, Reports). |
| **Head Chef** | `head_chef@example.com` | Access to Kitchen View, Menu Management (Create/Edit Items). |
| **Kitchen Staff** | `kitchen_staff@example.com` | Access to Kitchen View only. Cannot edit menu. |
| **Waiter** | `waiter@example.com` | Access to POS, Orders, Tables. No access to reports or settings. |
| **Cashier** | `cashier@example.com` | Access to POS, Orders. |
| **Delivery Driver** | `delivery_driver@example.com` | Access to Delivery Orders only. |

### How to Test
1.  **Log out** of your current admin account.
2.  **Log in** with one of the emails above (password: `password`).
3.  **Verify Sidebar:** Check that the sidebar only shows the menu items relevant to that role.
    *   *Example:* A Waiter should not see "Financial" or "Settings".
4.  **Verify Buttons:** Navigate to pages like **Menu**.
    *   *Manager/Head Chef:* Should see "Add Category", "Add Item", "Edit", "Delete" buttons.
    *   *Waiter/Kitchen Staff:* Should NOT see these buttons.
5.  **Verify Route Protection:** Try to manually access a restricted URL (e.g., `/reports/sales` as a Waiter). You should be redirected or see a 403 Forbidden error.
