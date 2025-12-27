# ✅ Fix: Bill PDF Generation Error

## 🎯 Issue Fixed
**Error**: `htmlspecialchars(): Argument #1 ($string) must be of type string, array given`
**Location**: `resources/views/bills/order.blade.php` at line 190.
**Cause**: The `restaurant->email` field was being returned as an array (likely due to data inconsistency or translation storage in MongoDB), but Blade expected a string.

## 🛠️ Solution
Updated the Blade template to check if `email` is an array. If it is, it picks the first value; otherwise, it uses the string directly.

**Code Change:**
```php
// Before
{{ $order->restaurant->email }}

// After
{{ is_array($order->restaurant->email) ? (current($order->restaurant->email)) : $order->restaurant->email }}
```

## ✅ Status
The bill page should now load correctly without 500 errors.
