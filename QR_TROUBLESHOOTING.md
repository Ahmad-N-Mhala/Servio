# 🐛 QR Menu Troubleshooting Guide

## Issue: Menu items not showing after scanning QR code

I've fixed the data serialization issue. Here's how to test and verify it's working:

---

## ✅ Quick Fix Applied

**What was fixed**:
- Menu categories and items are now properly serialized as arrays
- Added fallback values for currency and locale
- Removed unused imports

---

## 🧪 Test Steps

### Step 1: Get Your QR URL

```bash
php artisan tinker --execute="
\$table = \App\Models\Table::first();
if (\$table) {
    echo '✅ Table: ' . \$table->name . PHP_EOL;
    echo '✅ QR URL: ' . \$table->qr_code_url . PHP_EOL;
    echo PHP_EOL . '👉 Copy this URL and open it in your browser' . PHP_EOL;
} else {
    echo '❌ No tables found!' . PHP_EOL;
}
"
```

### Step 2: Check Menu Items Exist

```bash
php artisan tinker --execute="
\$restaurant = \App\Models\Restaurant::first();
\$categories = \App\Models\MenuCategory::where('restaurant_id', \$restaurant->id)
    ->where('is_active', true)
    ->with('items')
    ->get();

echo '📋 Menu Status:' . PHP_EOL;
echo 'Total Categories: ' . \$categories->count() . PHP_EOL . PHP_EOL;

foreach (\$categories as \$category) {
    echo '📁 Category: ' . (\$category->name['en'] ?? \$category->name) . PHP_EOL;
    echo '   Active: ' . (\$category->is_active ? 'Yes' : 'No') . PHP_EOL;
    echo '   Items: ' . \$category->items->count() . PHP_EOL;
    
    foreach (\$category->items as \$item) {
        \$itemName = is_array(\$item->name) ? (\$item->name['en'] ?? 'Unknown') : \$item->name;
        echo '   - ' . \$itemName . ' (AED ' . \$item->price . ')' . PHP_EOL;
        echo '     Available: ' . (\$item->is_available ? 'Yes' : 'No') . PHP_EOL;
    }
    echo PHP_EOL;
}
"
```

### Step 3: Open QR URL in Browser

1. Copy the URL from Step 1
2. Open it in your browser
3. You should now see:
   - Restaurant name
   - Table name
   - Menu categories
   - Menu items with prices
   - Cart button

### Step 4: Test Ordering

1. Click "Add to Cart" on any item
2. Cart modal should open
3. Adjust quantity if needed
4. Add special notes (optional)
5. Enter name and phone (optional)
6. Click "Place Order"
7. You should see confirmation with order number

---

## 🔍 Common Issues & Solutions

### Issue 1: "No menu items available"

**Possible causes**:
- No categories are active
- No items are available
- Items not linked to categories

**Check**:
```bash
php artisan tinker --execute="
\$items = \App\Models\MenuItem::where('is_available', true)->count();
echo 'Available items: ' . \$items . PHP_EOL;

\$categories = \App\Models\MenuCategory::where('is_active', true)->count();
echo 'Active categories: ' . \$categories . PHP_EOL;
"
```

**Fix**:
1. Go to Menu Management
2. Make sure categories are active (toggle switch)
3. Make sure items are available (toggle switch)
4. Make sure items are assigned to categories

### Issue 2: Menu shows but can't add to cart

**Check browser console**:
1. Open browser DevTools (F12)
2. Go to Console tab
3. Look for JavaScript errors
4. Share any errors you see

### Issue 3: Can't place order

**Possible causes**:
- CSRF token missing
- Network error
- Backend not running

**Check**:
1. Make sure `php artisan serve` is running
2. Check browser Network tab for failed requests
3. Look for error responses

---

## 🎯 Verify Data Structure

Run this to see exactly what data is being sent to the frontend:

```bash
php artisan tinker --execute="
\$table = \App\Models\Table::first();
\$restaurant = \$table->restaurant;

\$categories = \App\Models\MenuCategory::where('restaurant_id', \$restaurant->id)
    ->where('is_active', true)
    ->with(['items' => function (\$query) {
        \$query->where('is_available', true);
    }])
    ->get()
    ->map(function (\$category) {
        return [
            'id' => \$category->id,
            'name' => \$category->name,
            'items' => \$category->items->map(function (\$item) {
                return [
                    'id' => \$item->id,
                    'name' => \$item->name,
                    'price' => (float) \$item->price,
                    'image' => \$item->image,
                ];
            })->toArray(),
        ];
    })
    ->toArray();

echo json_encode([
    'table' => [
        'name' => \$table->name,
    ],
    'restaurant' => [
        'name' => \$restaurant->name,
        'currency' => \$restaurant->currency ?? 'AED',
    ],
    'categories' => \$categories,
], JSON_PRETTY_PRINT);
"
```

This shows you exactly what the frontend receives.

---

## 📱 Test on Mobile

### Option 1: Use Phone Browser
1. Get QR URL from Step 1
2. Type it into your phone's browser
3. Test the mobile experience

### Option 2: Scan Actual QR Code
1. Go to Tables in RestoFy admin
2. Hover on table card
3. Click purple QR icon
4. Click "Download PNG"
5. Display PNG on screen
6. Scan with phone camera
7. Menu should open!

---

## ✅ Expected Behavior

### When you open the QR URL, you should see:

**Header**:
- Restaurant name (e.g., "My Restaurant")
- Table name (e.g., "Table 1")
- Cart button with badge (shows item count)

**Menu**:
- Category names (e.g., "Appetizers", "Main Course")
- Item cards with:
  - Item image (or placeholder)
  - Item name
  - Description
  - Price
  - "Add to Cart" button

**Cart** (when clicked):
- List of added items
- Quantity controls (+/-)
- Special notes field
- Customer name field
- Customer phone field
- Subtotal, tax, total
- "Place Order" button

**After placing order**:
- Success modal
- Order number
- "Continue Browsing" button

---

## 🔧 Debug Mode

If you still have issues, enable debug mode to see detailed errors:

1. Check `.env` file
2. Make sure `APP_DEBUG=true`
3. Refresh the QR URL
4. Any errors will show on screen

---

## 📞 Still Not Working?

If menu still doesn't show:

1. **Share the output** of Step 2 (Check Menu Items)
2. **Share any browser console errors**
3. **Share the QR URL** you're trying to access
4. **Share a screenshot** of what you see

I'll help you debug further!

---

## ✅ Quick Checklist

Before reporting an issue, verify:

- [ ] `php artisan serve` is running
- [ ] `npm run dev` is running
- [ ] Tables exist in database
- [ ] Tables have QR tokens
- [ ] Menu categories exist
- [ ] Categories are active
- [ ] Menu items exist
- [ ] Items are available
- [ ] Items are linked to categories
- [ ] Browser console shows no errors

---

**The fix has been applied! Try accessing your QR URL now.** 🚀
