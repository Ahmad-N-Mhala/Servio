# Data Consistency Documentation

## Overview
This document outlines how data is consistently managed across the Servio application to ensure all pages read from the same source.

## Data Models & Their Single Source of Truth

### 1. **Plans** (`App\Models\Plan`)
**Single Source:** `plans` table in central database

**Used By:**
- ✅ **Admin Panel** (`/admin/plans`) - Full CRUD operations
- ✅ **Onboarding** (`/onboard`) - Displays active plans only
- ✅ **Subscriptions** - Links restaurants to plans

**Consistency Rules:**
- Admin can create/edit/delete plans
- Onboarding shows only `where('is_active', true)` plans
- Both use the same `Plan` model
- Features stored as JSON array
- Prices in decimal format

**Fields:**
- name, slug, description
- price_monthly, price_yearly
- features (JSON)
- max_restaurants, max_users, max_orders_per_month
- is_active, is_featured

---

### 2. **Restaurants** (`App\Models\Restaurant`)
**Single Source:** `restaurants` table in central database

**Used By:**
- ✅ **Admin Panel** (`/admin/restaurants`) - View all restaurants
- ✅ **Admin Subscriptions** (`/admin/subscriptions`) - Assign plans to restaurants
- ✅ **Onboarding** - Creates new restaurants
- ✅ **Dashboard** - Restaurant selection dropdown

**Consistency Rules:**
- All restaurants stored centrally
- Each restaurant has unique slug
- Linked to users via `restaurant_user` pivot table
- Can have one active subscription

---

### 3. **Users** (`App\Models\User`)
**Single Source:** `users` table in central database

**Used By:**
- ✅ **Admin Dashboard** - Total users count
- ✅ **Onboarding** - Creates new users
- ✅ **Authentication** - Login/logout
- ✅ **Restaurant Staff** - Linked via pivot table

**Consistency Rules:**
- Users stored centrally
- Linked to restaurants via `restaurant_user` pivot
- Email is unique across system
- Can belong to multiple restaurants

---

### 4. **Restaurant Subscriptions** (`App\Models\RestaurantSubscription`)
**Single Source:** `restaurant_subscriptions` table in central database

**Used By:**
- ✅ **Admin Subscriptions** (`/admin/subscriptions`) - Manage all subscriptions
- ✅ **Admin Dashboard** - Subscription metrics
- ✅ **Restaurant Access** - Determines features available

**Consistency Rules:**
- Links restaurants to plans
- One active subscription per restaurant
- Status: active, cancelled, expired
- Tracks start and end dates

---

### 5. **Delivery Integrations** (`App\Models\RestaurantSubscription`)
**Single Source:** `delivery_integrations` table in central database

**Used By:**
- ✅ **Admin Integrations** (`/admin/integrations`) - Manage providers per restaurant
- ✅ **Restaurant Settings** - Configure delivery providers

**Consistency Rules:**
- Each integration belongs to one restaurant
- Providers: Noon, Kareem, UberEats, Deliveroo, Talabat
- API credentials encrypted
- is_enabled flag controls activation

---

## Data Flow Diagram

```
Central Database (PostgreSQL)
├── plans
│   └── Used by: Admin Plans, Onboarding, Subscriptions
├── restaurants
│   └── Used by: Admin Restaurants, Subscriptions, Dashboard
├── users
│   └── Used by: Admin Dashboard, Authentication, Staff
├── restaurant_subscriptions
│   └── Used by: Admin Subscriptions, Dashboard Analytics
├── restaurant_user (pivot)
│   └── Links users to restaurants
└── delivery_integrations
    └── Used by: Admin Integrations, Restaurant Settings
```

## Ensuring Consistency

### ✅ DO:
1. Always use the Eloquent models (`Plan`, `Restaurant`, `User`, etc.)
2. Use relationships (`$plan->restaurantSubscriptions()`)
3. Apply consistent filters (e.g., `where('is_active', true)`)
4. Use the same validation rules across controllers
5. Keep fillable fields updated in models

### ❌ DON'T:
1. Query tables directly with `DB::table()`
2. Create duplicate data sources
3. Use different field names for the same data
4. Hard-code data that should come from database
5. Skip model relationships

## Controller Consistency

### Admin Controllers
All admin controllers follow the same pattern:
- `index()` - List with pagination
- `create()` - Show create form
- `store()` - Validate and create
- `edit()` - Show edit form
- `update()` - Validate and update
- `destroy()` - Delete with checks

### Data Sharing
- Admin pages get data via controller methods
- Inertia shares data to Vue components
- All use same models and relationships

## Validation Consistency

Plans validation (used in both Admin and Onboarding):
```php
'plan_id' => 'required|exists:plans,id'
```

Restaurant validation:
```php
'restaurant_id' => 'required|exists:restaurants,id'
```

This ensures data integrity across the application.

## Summary

✅ **Single Source of Truth** - Each entity has one table
✅ **Consistent Models** - Same Eloquent models everywhere  
✅ **Shared Validation** - Same rules across controllers
✅ **Relationships** - Proper model relationships
✅ **No Duplication** - Data not duplicated across tables

**Result:** All pages read from and write to the same data source, ensuring consistency across the entire application.
