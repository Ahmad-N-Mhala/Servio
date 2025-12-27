# ✅ Fix: Item Prices Showing as 0.00 in Bill

## 🎯 Issue Resolved
**Problem**: The "Price" and "Total" columns for items in the Bill were showing as `AED 0.00`, even though the Order Subtotal/Total was correct.
**Cause**: The `QrOrderController` was using incorrect field names (`price` and `subtotal`) when creating `OrderItem` records. The `OrderItem` model expects `unit_price` and `total_price`. Because `price` and `subtotal` were not in the `$fillable` array, they were ignored, leaving the database columns empty or zero.

## 🛠️ Solution
Updated `app/Http/Controllers/Tenant/QrOrderController.php` to map the data correctly.

**Change:**
```php
// Before (Incorrect)
OrderItem::create([
    ...
    'price' => $itemData['price'],
    'subtotal' => $itemData['subtotal'],
]);

// After (Fixed)
OrderItem::create([
    ...
    'unit_price' => $itemData['price'],
    'total_price' => $itemData['subtotal'],
]);
```

## ⚠️ Important Note
This fix applies to **newly created orders**. Existing orders created before this fix will still show 0.00 because the price data was not saved to the database item records. Please place a **new test order** to verify the fix.
