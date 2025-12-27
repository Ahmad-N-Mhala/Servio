# ✅ Feature Added: POS Order Adjustments

## 🎯 Objective
Allow POS users to edit an order by applying a **Discount** or adding an **Extra Charge**, and ensure these changes are reflected in the printable bill.

## 🛠️ Changes Implemented

### 1. Backend
- **`app/Models/Order.php`**: Added `additional_charge` to fillable attributes and casts.
- **`app/Http/Controllers/Tenant/POSController.php`**: Added `update` method to handle updating `discount_amount` and `additional_charge`.
- **`routes/web.php`**: Registered `PUT /pos/{order}` route.

### 2. Frontend (POS UI)
- **`resources/js/Pages/POS/Index.vue`**: 
    - Added "Discount" and "Extra Charge" input fields in the bill details section.
    - Added logic to sync these fields with the selected order.
    - Added "Apply Changes" button to update the backend.
    - Updated Totals display to show applied discount and extra charges.

### 3. Bill Template
- **`resources/views/bills/order.blade.php`**: Added a row to display "Extra Charge" in the bill totals section.

## 🚀 How to Test
1. Go to POS.
2. Select an active order.
3. In the Bill Details panel, enter a value for "Discount" or "Extra Charge".
4. Click "Apply Changes".
5. Observe the Total Amount update.
6. Settle the order.
7. Go to Orders page and print the bill to see the changes.
