# Delivery Page Table Number Display
**Date:** 2025-12-28
**Feature:** Waiter Delivery Interface
**Status:** ✅ COMPLETE

## Issue
Users reported that the table number was not showing on the delivery page.

## Investigation
- Verified `OrderDeliveryController` loads the `table` relationship: `Order::with(['items.menuItem', 'table'])`.
- Verified `Order` model has `table()` relationship: `belongsTo(Table::class)`.
- Verified `Wait/Delivery.vue` attempts to render `order.table.name`.

## Possible Causes
1. **No Table Assigned:** The order might be a "Takeaway" or "Delivery" order, or a Dine-in order where no table was selected.
2. **Missing Data:** Old orders might reference deleted tables.

## Fix
Updated `Waiter/Delivery.vue` to handle missing table data more gracefully by showing the **Order Type** as a fallback.

**New Display Logic:**
```javascript
order.table?.name || (order.type === 'dine_in' ? 'Dine In (No Table)' : order.type?.toUpperCase() || 'No Table')
```

- **If Table Exists:** Shows "Table 5" (or whatever the name is).
- **If Takeaway:** Shows "TAKEAWAY".
- **If Delivery:** Shows "DELIVERY".
- **If Dine In (No Table):** Shows "Dine In (No Table)".

This ensures the header is never empty or misleading.
