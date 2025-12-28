# Restaurant Soft Delete & Restore Feature

**Date:** 2025-12-28  
**Feature:** Soft Delete Restaurants with User Deactivation/Reactivation  
**Status:** ✅ COMPLETE

---

## 🎯 Objective

Implement soft delete functionality for restaurants where:
1. Deleting a restaurant marks it as deleted (not permanently removed)
2. All associated users are automatically deactivated
3. Super admins can restore deleted restaurants
4. Restoring a restaurant reactivates all its users

---

## ✅ Implementation Summary

### **What Was Changed:**

1. **Restaurant Model** - Added SoftDeletes trait
2. **RestaurantController** - Updated destroy() and added restore() methods
3. **Routes** - Added restore route
4. **Frontend** - Added status filter and restore button

---

## 📝 Detailed Changes

### 1. **Restaurant Model** (`app/Models/Restaurant.php`)

**Added SoftDeletes Trait:**
```php
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;
    // ...
}
```

**What This Does:**
- Adds `deleted_at` timestamp field
- When `delete()` is called, sets `deleted_at` instead of removing record
- Provides `restore()` method to undelete
- Provides `withTrashed()`, `onlyTrashed()` query scopes

---

### 2. **RestaurantController** (`app/Http/Controllers/Admin/RestaurantController.php`)

#### **Updated `index()` Method:**

**Before:**
```php
$query = \App\Models\Restaurant::with(['subscription.plan'])
    ->where('status', '!=', 'deleted');
```

**After:**
```php
// Include soft-deleted restaurants
$query = \App\Models\Restaurant::withTrashed()->with(['subscription.plan']);

// Filter by status
if ($request->input('status') === 'deleted') {
    $query->onlyTrashed();
} elseif ($request->input('status') === 'active') {
    $query->whereNull('deleted_at');
}
```

**Benefits:**
- Shows all restaurants by default
- Can filter to show only active or only deleted
- Deleted restaurants are visible to super admins

---

#### **Updated `destroy()` Method:**

**Before (Permanent Delete):**
```php
public function destroy(\App\Models\Restaurant $restaurant)
{
    // Delete users from database
    $userEmails = DB::table('restaurant_user')
        ->where('restaurant_id', $restaurant->id)
        ->pluck('email');
    
    \App\Models\User::whereIn('email', $userEmails)->delete();
    
    // Delete pivot records
    DB::table('restaurant_user')
        ->where('restaurant_id', $restaurant->id)
        ->delete();
    
    // Delete staff
    \App\Models\Staff::where('restaurant_id', $restaurant->id)->delete();
    
    // Delete restaurant
    $restaurant->delete();
}
```

**After (Soft Delete with User Deactivation):**
```php
public function destroy(\App\Models\Restaurant $restaurant)
{
    try {
        // Soft delete the restaurant (sets deleted_at timestamp)
        $restaurant->delete();

        // Deactivate all users in pivot table
        DB::table('restaurant_user')
            ->where('restaurant_id', (string) $restaurant->id)
            ->update([
                'is_active' => false,
                'updated_at' => now()
            ]);

        // Update restaurant status
        $restaurant->update(['status' => 'inactive']);

        return redirect()->route('admin.restaurants.index')
            ->with('success', 'Restaurant has been deleted and all associated users have been deactivated.');
    } catch (\Exception $e) {
        return redirect()->route('admin.restaurants.index')
            ->with('error', 'Failed to delete restaurant: ' . $e->getMessage());
    }
}
```

**Key Changes:**
- ✅ Restaurant is soft deleted (not permanently removed)
- ✅ Users are deactivated (not deleted)
- ✅ Staff records remain intact
- ✅ All data preserved for potential restoration

---

#### **New `restore()` Method:**

```php
public function restore($id)
{
    try {
        // Find the soft-deleted restaurant
        $restaurant = \App\Models\Restaurant::withTrashed()->findOrFail($id);

        // Restore the restaurant (removes deleted_at timestamp)
        $restaurant->restore();

        // Reactivate all users in pivot table
        DB::table('restaurant_user')
            ->where('restaurant_id', (string) $restaurant->id)
            ->update([
                'is_active' => true,
                'updated_at' => now()
            ]);

        // Update restaurant status to active
        $restaurant->update(['status' => 'active']);

        return redirect()->route('admin.restaurants.index')
            ->with('success', 'Restaurant has been restored and all associated users have been reactivated.');
    } catch (\Exception $e) {
        return redirect()->route('admin.restaurants.index')
            ->with('error', 'Failed to restore restaurant: ' . $e->getMessage());
    }
}
```

**What It Does:**
- ✅ Finds soft-deleted restaurant
- ✅ Restores restaurant (removes `deleted_at`)
- ✅ Reactivates all users
- ✅ Sets restaurant status to active

---

### 3. **Routes** (`routes/web.php`)

**Added Restore Route:**
```php
Route::resource('restaurants', \App\Http\Controllers\Admin\RestaurantController::class);
Route::post('restaurants/{id}/restore', [\App\Http\Controllers\Admin\RestaurantController::class, 'restore'])
    ->name('restaurants.restore');
```

**Route Details:**
- **Method:** POST
- **URL:** `/admin/restaurants/{id}/restore`
- **Name:** `admin.restaurants.restore`
- **Controller:** `RestaurantController@restore`

---

### 4. **Frontend** (`resources/js/Pages/Admin/Restaurants/Index.vue`)

#### **Added Status Filter:**

```vue
<select v-model="statusFilter" class="...">
    <option value="">All Status</option>
    <option value="active">Active Only</option>
    <option value="deleted">Deleted Only</option>
</select>
```

**Features:**
- Filter to show all restaurants
- Filter to show only active restaurants
- Filter to show only deleted restaurants

---

#### **Updated Status Column:**

**Before:**
```vue
<span class="...">
    {{ row.status || 'Active' }}
</span>
```

**After:**
```vue
<div class="flex flex-col gap-1">
    <span class="..." :class="{
        'bg-green-100 text-green-700': row.status === 'active' && !row.deleted_at,
        'bg-red-100 text-red-700': row.deleted_at,
        // ...
    }">
        {{ row.deleted_at ? 'Deleted' : (row.status || 'Active') }}
    </span>
    <span v-if="row.deleted_at" class="text-xs text-gray-500">
        {{ new Date(row.deleted_at).toLocaleDateString() }}
    </span>
</div>
```

**Features:**
- Shows "Deleted" status for soft-deleted restaurants
- Displays deletion date
- Red badge for deleted restaurants
- Green badge for active restaurants

---

#### **Updated Actions Column:**

**Before:**
```vue
<Link :href="route('admin.restaurants.edit', row.id)">Edit</Link>
<button @click="deleteRestaurant(row)">Delete</button>
```

**After:**
```vue
<div class="flex items-center gap-2">
    <!-- Edit button (only for active restaurants) -->
    <Link v-if="!row.deleted_at" :href="route('admin.restaurants.edit', row.id)">
        Edit
    </Link>
    
    <!-- Restore button (only for deleted restaurants) -->
    <button v-if="row.deleted_at" @click="restoreRestaurant(row)">
        <svg><!-- Restore icon --></svg>
    </button>
    
    <!-- Delete button (only for active restaurants) -->
    <button v-else @click="deleteRestaurant(row)">
        <svg><!-- Delete icon --></svg>
    </button>
</div>
```

**Features:**
- Edit button only shows for active restaurants
- Restore button only shows for deleted restaurants
- Delete button only shows for active restaurants
- Clear visual distinction with icons

---

#### **Added Restore Function:**

```typescript
const restoreRestaurant = (restaurant: any) => {
    if (confirm('Are you sure you want to restore this restaurant? All associated users will be reactivated.')) {
        router.post(route('admin.restaurants.restore', restaurant.id));
    }
};
```

**Features:**
- Confirmation dialog
- Clear message about user reactivation
- POST request to restore endpoint

---

## 🔄 User Flow

### **Deleting a Restaurant:**

1. Super admin clicks "Delete" button on a restaurant
2. Confirmation dialog: "Are you sure you want to delete this restaurant? All associated users will be deactivated."
3. If confirmed:
   - Restaurant `deleted_at` timestamp is set
   - Restaurant `status` changed to "inactive"
   - All users in `restaurant_user` pivot table set to `is_active = false`
4. Success message: "Restaurant has been deleted and all associated users have been deactivated."
5. Restaurant now shows with "Deleted" badge and deletion date

---

### **Restoring a Restaurant:**

1. Super admin filters to show "Deleted Only" restaurants
2. Clicks "Restore" button (circular arrow icon) on a deleted restaurant
3. Confirmation dialog: "Are you sure you want to restore this restaurant? All associated users will be reactivated."
4. If confirmed:
   - Restaurant `deleted_at` timestamp is removed
   - Restaurant `status` changed to "active"
   - All users in `restaurant_user` pivot table set to `is_active = true`
5. Success message: "Restaurant has been restored and all associated users have been reactivated."
6. Restaurant now shows with "Active" badge

---

## 🛡️ Data Integrity

### **What's Preserved:**

When a restaurant is deleted:
- ✅ Restaurant record (soft deleted)
- ✅ User records in `users` table
- ✅ User-restaurant relationships in `restaurant_user` pivot
- ✅ Staff records
- ✅ Orders, customers, menu items, etc.
- ✅ All historical data

### **What's Changed:**

When a restaurant is deleted:
- ⚠️ `deleted_at` timestamp set on restaurant
- ⚠️ `status` changed to "inactive"
- ⚠️ `is_active` set to `false` for all users in pivot table

When a restaurant is restored:
- ✅ `deleted_at` timestamp removed
- ✅ `status` changed to "active"
- ✅ `is_active` set to `true` for all users in pivot table

---

## 🧪 Testing

### **Test Case 1: Delete Restaurant**

**Steps:**
1. Login as super admin
2. Go to Restaurants Management
3. Click delete on a restaurant
4. Confirm deletion

**Expected Result:**
- ✅ Restaurant shows "Deleted" status
- ✅ Deletion date displayed
- ✅ Edit button hidden
- ✅ Restore button visible
- ✅ Users cannot login to that restaurant

---

### **Test Case 2: Restore Restaurant**

**Steps:**
1. Login as super admin
2. Go to Restaurants Management
3. Filter by "Deleted Only"
4. Click restore on a deleted restaurant
5. Confirm restoration

**Expected Result:**
- ✅ Restaurant shows "Active" status
- ✅ Edit button visible
- ✅ Delete button visible
- ✅ Restore button hidden
- ✅ Users can login to that restaurant again

---

### **Test Case 3: Filter Restaurants**

**Steps:**
1. Login as super admin
2. Go to Restaurants Management
3. Test each filter option:
   - "All Status"
   - "Active Only"
   - "Deleted Only"

**Expected Result:**
- ✅ "All Status" shows both active and deleted
- ✅ "Active Only" shows only active restaurants
- ✅ "Deleted Only" shows only deleted restaurants

---

### **Test Case 4: User Access After Delete**

**Steps:**
1. Delete a restaurant as super admin
2. Try to login as a user of that restaurant

**Expected Result:**
- ✅ User cannot access the deleted restaurant
- ✅ User sees "No restaurant access" or similar message

---

### **Test Case 5: User Access After Restore**

**Steps:**
1. Restore a restaurant as super admin
2. Try to login as a user of that restaurant

**Expected Result:**
- ✅ User can access the restaurant again
- ✅ All data is intact
- ✅ User can perform normal operations

---

## 📊 Database Schema

### **Restaurant Collection:**

```javascript
{
    "_id": ObjectId("..."),
    "name": "Restaurant Name",
    "status": "active" | "inactive" | "suspended",
    "deleted_at": ISODate("2025-12-28T...") | null,
    // ... other fields
}
```

### **restaurant_user Pivot Collection:**

```javascript
{
    "restaurant_id": "...",
    "email": "user@example.com",
    "role": "owner" | "manager" | "staff",
    "is_active": true | false,
    "updated_at": ISODate("...")
}
```

---

## 🔑 Key Features

1. ✅ **Soft Delete** - Restaurants are not permanently removed
2. ✅ **User Deactivation** - Users are deactivated, not deleted
3. ✅ **Data Preservation** - All data is preserved
4. ✅ **Easy Restoration** - One-click restore functionality
5. ✅ **Status Filtering** - Filter by active/deleted status
6. ✅ **Visual Indicators** - Clear badges and icons
7. ✅ **Confirmation Dialogs** - Prevent accidental actions
8. ✅ **Audit Trail** - Deletion date tracked

---

## 🚀 Benefits

### **For Super Admins:**
- ✅ Can recover accidentally deleted restaurants
- ✅ Can review deleted restaurants before permanent removal
- ✅ Can temporarily disable restaurants
- ✅ Better control over restaurant lifecycle

### **For Restaurant Owners:**
- ✅ Data is not lost if restaurant is deleted
- ✅ Can request restoration from super admin
- ✅ Historical data preserved

### **For the System:**
- ✅ Data integrity maintained
- ✅ Referential integrity preserved
- ✅ Audit trail available
- ✅ Safer than permanent deletion

---

## 📝 Notes

### **Important Considerations:**

1. **Permanent Deletion:**
   - Currently not implemented
   - Could be added as a separate "Force Delete" feature
   - Would require additional confirmation

2. **User Multi-Restaurant Access:**
   - Users with access to multiple restaurants are only deactivated for the deleted restaurant
   - They can still access other restaurants

3. **Data Cleanup:**
   - Consider implementing a cleanup job to permanently delete restaurants after X days
   - Add warning before permanent deletion

4. **Permissions:**
   - Only super admins can delete/restore restaurants
   - Regular users cannot see deleted restaurants

---

## ✅ Completion Checklist

- [x] Added SoftDeletes trait to Restaurant model
- [x] Updated destroy() method to soft delete
- [x] Created restore() method
- [x] Added restore route
- [x] Updated index() to show deleted restaurants
- [x] Added status filter to frontend
- [x] Updated status column to show deleted state
- [x] Added restore button to actions
- [x] Updated delete confirmation message
- [x] Added restore confirmation dialog
- [x] Fixed TypeScript types
- [x] Tested delete functionality
- [x] Tested restore functionality
- [x] Tested filtering
- [x] Created documentation

---

**Feature Status:** ✅ **COMPLETE AND READY FOR USE**

**Super admins can now safely delete and restore restaurants with automatic user deactivation/reactivation! 🎉**

---

**Implemented by:** Antigravity AI  
**Date:** 2025-12-28 12:32 PM
