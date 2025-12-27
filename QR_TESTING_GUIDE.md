# 🧪 QR Code Ordering - Quick Test Guide

## ✅ System is Ready to Test!

Follow these steps to test the complete QR code ordering system.

---

## 🚀 Quick Test (5 Minutes)

### Step 1: Get a Table's QR URL

```bash
php artisan tinker --execute="
\$table = \App\Models\Table::first();
if (\$table) {
    echo 'Table: ' . \$table->name . PHP_EOL;
    echo 'QR URL: ' . \$table->qr_code_url . PHP_EOL;
    echo PHP_EOL . 'Copy this URL and open it in your browser!' . PHP_EOL;
} else {
    echo 'No tables found. Create one first!' . PHP_EOL;
}
"
```

### Step 2: Open the URL

1. Copy the QR URL from above
2. Open it in your browser
3. You should see the menu page with:
   - Restaurant name
   - Table name
   - Menu categories
   - Menu items
   - Cart button

### Step 3: Test Ordering

1. **Browse Menu**
   - Scroll through categories
   - See item images and prices

2. **Add to Cart**
   - Click "Add to Cart" on any item
   - Cart icon shows item count
   - Cart modal opens

3. **Adjust Order**
   - Change quantities with +/- buttons
   - Add special notes
   - Remove items if needed

4. **Enter Info** (optional)
   - Enter your name
   - Enter phone number

5. **Place Order**
   - Review total
   - Click "Place Order"
   - See confirmation with order number

### Step 4: Verify Order

```bash
php artisan tinker --execute="
\$order = \App\Models\Order::where('source', 'qr_code')->latest()->first();
if (\$order) {
    echo 'Order Number: ' . \$order->order_number . PHP_EOL;
    echo 'Table: ' . \$order->table->name . PHP_EOL;
    echo 'Total: ' . \$order->total . PHP_EOL;
    echo 'Status: ' . \$order->status . PHP_EOL;
    echo 'Items: ' . \$order->items->count() . PHP_EOL;
} else {
    echo 'No QR orders found yet.' . PHP_EOL;
}
"
```

---

## 📱 Test on Mobile

### Option 1: Use Your Phone

1. Get the QR URL from Step 1
2. Open on your phone's browser
3. Test the mobile experience

### Option 2: Scan Actual QR Code

1. Go to Tables page in RestoFy
2. Hover on a table
3. Click purple QR icon
4. Click "Download PNG"
5. Display the PNG on your computer screen
6. Scan with your phone
7. Menu opens on phone!

---

## 🧪 Detailed Testing

### Test 1: Menu Display

**Expected**:
- ✅ Restaurant name shows
- ✅ Table name shows
- ✅ Categories display
- ✅ Items show with images
- ✅ Prices display correctly
- ✅ Cart button visible

**Test**:
```
Open QR URL → Verify all elements present
```

### Test 2: Add to Cart

**Expected**:
- ✅ Click "Add to Cart"
- ✅ Cart modal opens
- ✅ Item appears in cart
- ✅ Quantity is 1
- ✅ Price is correct

**Test**:
```
Click "Add to Cart" on item → Cart opens → Item listed
```

### Test 3: Quantity Controls

**Expected**:
- ✅ Click + increases quantity
- ✅ Click - decreases quantity
- ✅ Total updates automatically
- ✅ Can't go below 1

**Test**:
```
In cart → Click + → Quantity increases
In cart → Click - → Quantity decreases
```

### Test 4: Special Notes

**Expected**:
- ✅ Can type notes
- ✅ Notes save with item
- ✅ Notes sent with order

**Test**:
```
In cart → Type "No onions" → Place order → Check order in DB
```

### Test 5: Remove Item

**Expected**:
- ✅ Click trash icon
- ✅ Item removed from cart
- ✅ Total updates
- ✅ If last item, cart shows empty

**Test**:
```
In cart → Click trash icon → Item disappears
```

### Test 6: Customer Info

**Expected**:
- ✅ Can enter name
- ✅ Can enter phone
- ✅ Both optional
- ✅ Info saves with order

**Test**:
```
Enter name → Enter phone → Place order → Check order in DB
```

### Test 7: Order Placement

**Expected**:
- ✅ Click "Place Order"
- ✅ Loading state shows
- ✅ Order created in DB
- ✅ Confirmation modal appears
- ✅ Order number displayed

**Test**:
```
Place order → Wait → Confirmation shows → Order number visible
```

### Test 8: Order in System

**Expected**:
- ✅ Order in database
- ✅ Linked to correct table
- ✅ Status is "pending"
- ✅ Source is "qr_code"
- ✅ Items are correct
- ✅ Total is correct

**Test**:
```bash
php artisan tinker --execute="
\$order = \App\Models\Order::latest()->first();
echo json_encode([
    'order_number' => \$order->order_number,
    'table' => \$order->table->name,
    'source' => \$order->source,
    'status' => \$order->status,
    'total' => \$order->total,
    'items_count' => \$order->items->count(),
], JSON_PRETTY_PRINT);
"
```

### Test 9: Multiple Items

**Expected**:
- ✅ Can add multiple different items
- ✅ Each item has own quantity
- ✅ Each item has own notes
- ✅ Total calculates correctly

**Test**:
```
Add item 1 → Add item 2 → Add item 3 → Check cart → Verify all present
```

### Test 10: Empty Cart

**Expected**:
- ✅ Remove all items
- ✅ Cart shows "empty" message
- ✅ Can't place order
- ✅ Can close cart

**Test**:
```
Remove all items → See empty message → "Place Order" disabled
```

---

## 🎯 Success Criteria

### ✅ All Tests Pass If:

1. Menu displays correctly
2. Can add items to cart
3. Can adjust quantities
4. Can add special notes
5. Can remove items
6. Can enter customer info
7. Can place order
8. Order confirmation shows
9. Order appears in database
10. Order linked to correct table

---

## 🐛 Common Issues & Solutions

### Issue: Menu doesn't load
**Solution**: 
- Check if table has QR token
- Check if menu items exist
- Check browser console for errors

### Issue: Can't add to cart
**Solution**:
- Check browser console
- Verify JavaScript is enabled
- Try different browser

### Issue: Order fails to place
**Solution**:
- Check CSRF token
- Check network tab for errors
- Verify backend is running
- Check database connection

### Issue: Order not in database
**Solution**:
- Check if order placement succeeded
- Check error logs
- Verify database connection

---

## 📊 Test Results Template

```
Date: ___________
Tester: ___________

[ ] Menu Display - Pass/Fail
[ ] Add to Cart - Pass/Fail
[ ] Quantity Controls - Pass/Fail
[ ] Special Notes - Pass/Fail
[ ] Remove Item - Pass/Fail
[ ] Customer Info - Pass/Fail
[ ] Order Placement - Pass/Fail
[ ] Confirmation - Pass/Fail
[ ] Database Entry - Pass/Fail
[ ] Table Link - Pass/Fail

Overall: Pass/Fail

Notes:
_______________________
_______________________
_______________________
```

---

## 🎉 Quick Verification

### One-Command Test:

```bash
# Create test order
php artisan tinker --execute="
echo '🧪 Testing QR Ordering System...' . PHP_EOL . PHP_EOL;

// Get table
\$table = \App\Models\Table::first();
if (!\$table) {
    echo '❌ No tables found!' . PHP_EOL;
    exit;
}
echo '✅ Table found: ' . \$table->name . PHP_EOL;

// Check QR token
if (!\$table->qr_code_token) {
    echo '❌ No QR token!' . PHP_EOL;
    exit;
}
echo '✅ QR token exists' . PHP_EOL;

// Get menu items
\$items = \App\Models\MenuItem::where('is_available', true)->take(2)->get();
if (\$items->count() === 0) {
    echo '❌ No menu items!' . PHP_EOL;
    exit;
}
echo '✅ Menu items found: ' . \$items->count() . PHP_EOL;

// Show test URL
echo PHP_EOL . '🔗 Test URL:' . PHP_EOL;
echo \$table->qr_code_url . PHP_EOL . PHP_EOL;

echo '📋 Next steps:' . PHP_EOL;
echo '1. Open the URL above in your browser' . PHP_EOL;
echo '2. Add items to cart' . PHP_EOL;
echo '3. Place an order' . PHP_EOL;
echo '4. Check for order in database' . PHP_EOL;
"
```

---

## ✅ Ready to Test!

**Everything is set up and ready for testing!**

1. Run the quick test above
2. Open the QR URL
3. Place a test order
4. Verify it works

**Good luck!** 🚀
