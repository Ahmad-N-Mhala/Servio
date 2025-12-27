# ✅ Search Logic Fixed in OrderController

## 🎯 Issue Resolved
The search functionality in the Orders page was failing because it used SQL-specific syntax (`ilike` and `whereRaw`) which is not compatible with the MongoDB database driver.

## 🛠️ Changes Made
Modified `app/Http/Controllers/Tenant/OrderController.php` in two places:
1. **`index` method**: Updated search logic.
2. **`export` method**: Updated search logic.

### 🔄 Logic Update
**Before (SQL-specific):**
```php
$q->where('order_number', 'ilike', "%{$search}%")
    // ...
    ->orWhereRaw('CAST(total AS TEXT) ilike ?', ["%{$search}%"]);
```

**After (MongoDB Compatible):**
```php
$q->where('order_number', 'like', "%{$search}%")
    // ...
    ->orWhere('status', 'like', "%{$search}%");

if (is_numeric($search)) {
    $q->orWhere('total', (float) $search);
}
```

## 🧪 Verification
- `like` operator works correctly with Laravel MongoDB driver (maps to regex).
- Numeric search for `total` is now handled explicitly.
- `ilike` and `whereRaw` are completely removed.

## ✅ Result
Searching in the Orders tab will now work correctly without throwing database errors.
