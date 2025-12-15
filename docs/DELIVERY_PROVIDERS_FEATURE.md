# Delivery Provider Management Feature

## Overview
This feature allows superadmins to manage the master list of delivery provider applications (like Talabat, Noon, Careem, etc.) that restaurants can integrate with.

## What Was Created

### 1. Database & Models
- **Migration**: `2025_12_15_044120_create_delivery_providers_table.php`
  - Stores delivery provider information
  - Fields: name, slug, description, logo_url, API requirements, status, sort order
  
- **Model**: `App\Models\DeliveryProvider`
  - Manages delivery provider data
  - Relationships with DeliveryIntegration model
  - Scopes for active providers and ordering

### 2. Controller
- **File**: `App\Http\Controllers\Admin\DeliveryProviderController`
- **Features**:
  - Full CRUD operations (Create, Read, Update, Delete)
  - Toggle provider active/inactive status
  - Prevents deletion of providers with active integrations
  - Auto-generates slug from provider name

### 3. Routes
Added to `/routes/web.php` under admin section:
- `GET /admin/delivery-providers` - List all providers
- `GET /admin/delivery-providers/create` - Create form
- `POST /admin/delivery-providers` - Store new provider
- `GET /admin/delivery-providers/{id}/edit` - Edit form
- `PUT /admin/delivery-providers/{id}` - Update provider
- `DELETE /admin/delivery-providers/{id}` - Delete provider
- `POST /admin/delivery-providers/{id}/toggle-status` - Toggle active status

### 4. Vue Pages
All pages feature modern, premium UI with gradients, animations, and responsive design:

#### Index Page (`resources/js/Pages/Admin/DeliveryProviders/Index.vue`)
- **Features**:
  - Card-based grid layout for providers
  - Stats cards showing total providers, active providers, and total integrations
  - Logo display for each provider
  - Status badges (Active/Inactive)
  - Integration count per provider
  - Quick actions: Edit, Activate/Deactivate, Delete
  - Pagination support
  - Empty state with call-to-action

#### Create Page (`resources/js/Pages/Admin/DeliveryProviders/Create.vue`)
- **Sections**:
  - Basic Information (name, slug, description, logo URL, API docs URL)
  - Configuration Requirements (checkboxes for API key, secret, store ID, webhook)
  - Status & Display (active toggle, sort order)
- **Features**:
  - Auto-generates slug from name if not provided
  - Form validation with error display
  - Modern checkbox styling with hover effects
  - Organized sections with icons

#### Edit Page (`resources/js/Pages/Admin/DeliveryProviders/Edit.vue`)
- **Additional Features**:
  - Pre-filled form with existing data
  - Logo preview
  - Warning banner if provider is being used by restaurants
  - Delete button (disabled if provider has active integrations)
  - All create page features

### 5. Seeder
- **File**: `database/seeders/DeliveryProviderSeeder`
- **Providers Included**:
  1. Talabat
  2. Noon Food
  3. Careem NOW
  4. Deliveroo
  5. Uber Eats
  6. Zomato
  7. HungerStation
  8. Jahez

Each provider includes:
- Name and slug
- Description
- Logo URL (placeholder)
- API documentation URL
- Configuration requirements
- Active status
- Sort order

### 6. Navigation
- Added "Delivery Providers" link to AdminLayout sidebar
- Located under "Configurations" section
- Icon: Archive/box icon
- Active state highlighting

## How to Use

### For Superadmin:

1. **Access the Feature**:
   - Log in as superadmin
   - Navigate to sidebar → Configurations → Delivery Providers

2. **View Providers**:
   - See all available delivery providers in a card grid
   - View stats: total providers, active providers, total integrations

3. **Add New Provider**:
   - Click "Add Provider" button
   - Fill in provider details:
     - Name (required)
     - Slug (auto-generated if empty)
     - Description
     - Logo URL
     - API Documentation URL
   - Select configuration requirements
   - Set active status and sort order
   - Click "Create Provider"

4. **Edit Provider**:
   - Click "Edit" on any provider card
   - Update any fields
   - View usage warning if provider is being used
   - Click "Update Provider"

5. **Toggle Status**:
   - Click "Activate" or "Deactivate" button on provider card
   - Provider status updates immediately

6. **Delete Provider**:
   - Only possible if provider has no active integrations
   - Click "Delete" button (disabled if in use)
   - Confirm deletion

## Database Schema

```sql
delivery_providers
├── id
├── name (string)
├── slug (string, unique)
├── description (text, nullable)
├── logo_url (string, nullable)
├── api_documentation_url (string, nullable)
├── requires_api_key (boolean, default: true)
├── requires_api_secret (boolean, default: true)
├── requires_store_id (boolean, default: true)
├── requires_webhook_secret (boolean, default: false)
├── configuration_fields (json, nullable)
├── is_active (boolean, default: true)
├── sort_order (integer, default: 0)
├── created_at
└── updated_at
```

## API Endpoints

All endpoints require admin authentication (`AdminMiddleware`):

- `GET /en/admin/delivery-providers` - List providers
- `GET /en/admin/delivery-providers/create` - Create form
- `POST /en/admin/delivery-providers` - Store provider
- `GET /en/admin/delivery-providers/{id}/edit` - Edit form
- `PUT /en/admin/delivery-providers/{id}` - Update provider
- `DELETE /en/admin/delivery-providers/{id}` - Delete provider
- `POST /en/admin/delivery-providers/{id}/toggle-status` - Toggle status

## Design Features

### UI/UX Highlights:
- ✨ Gradient backgrounds and buttons
- 🎨 Color-coded requirement badges
- 📊 Stats cards with icons
- 🖼️ Logo display and preview
- ⚡ Smooth transitions and hover effects
- 📱 Fully responsive design
- 🎯 Empty states with CTAs
- ⚠️ Usage warnings for safety
- 🔒 Disabled states for protected actions

### Color Scheme:
- Primary: Blue-Purple gradient
- Success: Green
- Warning: Orange/Yellow
- Danger: Red
- Info: Blue

## Testing

To test the feature:

```bash
# Run migrations
php artisan migrate

# Seed initial providers
php artisan db:seed --class=DeliveryProviderSeeder

# Access the page
# Navigate to: http://localhost/en/admin/delivery-providers
```

## Future Enhancements

Potential improvements:
1. File upload for logos instead of URLs
2. Custom configuration fields builder
3. API testing/validation
4. Provider analytics (usage stats)
5. Bulk operations (activate/deactivate multiple)
6. Import/export providers
7. Provider categories/tags
8. Integration templates per provider

## Notes

- Providers cannot be deleted if they have active restaurant integrations
- Slug is auto-generated from name if not provided
- All sensitive fields (API keys, secrets) are encrypted in DeliveryIntegration model
- Sort order determines display order (lower numbers first)
- Logo URLs should be publicly accessible
- The feature is only accessible to superadmins

## Files Modified/Created

### Created:
- `app/Models/DeliveryProvider.php`
- `app/Http/Controllers/Admin/DeliveryProviderController.php`
- `database/migrations/2025_12_15_044120_create_delivery_providers_table.php`
- `database/seeders/DeliveryProviderSeeder.php`
- `resources/js/Pages/Admin/DeliveryProviders/Index.vue`
- `resources/js/Pages/Admin/DeliveryProviders/Create.vue`
- `resources/js/Pages/Admin/DeliveryProviders/Edit.vue`

### Modified:
- `routes/web.php` - Added delivery-providers routes
- `resources/js/Layouts/AdminLayout.vue` - Added navigation link
