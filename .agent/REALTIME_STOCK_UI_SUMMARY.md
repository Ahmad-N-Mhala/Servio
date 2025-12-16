# Real-Time Stock Availability UI - Implementation Summary

## Overview
Enhanced the order creation interface to show real-time stock availability and prevent users from adding items to their cart when stock is insufficient. This provides immediate feedback at the point of interaction (the "+" button).

## Key Features

### 1. **Backend Stock Calculation**
**Location**: `OrderController::create()` method (lines 224-257)

For each menu item on the menu:
- Calculates maximum servings based on ALL required ingredients
- Uses FIFO batch availability (only counts batches with qty > 0)
- Finds the limiting ingredient (bottleneck)
- Returns max quantity that can be ordered

**Algorithm**:
```php
foreach (menu items) {
    maxServings = INFINITE
    
    foreach (ingredients in item) {
        availableStock = sum(all batch quantities)
        possibleServings = floor(availableStock / requiredPerServing)
        maxServings = min(maxServings, possibleServings)
    }
    
    return maxServings
}
```

**Example**:
```
Pizza Recipe:
- 500g Cheese (Available: 2000g) → 4 servings
- 300g Flour (Available: 900g) → 3 servings
- 100g Sauce (Available: 200g) → 2 servings

Max Servings = min(4, 3, 2) = 2 pizzas
```

### 2. **Frontend Stock Tracking**
**Location**: `Orders/Create.vue` (lines 548-580)

**Helper Functions**:

#### `canAddItem(itemId)`
```typescript
const canAddItem = (itemId: number): boolean => {
    const stockInfo = props.stockAvailability?.[itemId];
    const currentQty = getQty(itemId);
    return currentQty < stockInfo.max_quantity;
};
```
Returns: `true` if user can add one more of this item

#### `getStockMessage(itemId)`
```typescript
const getStockMessage = (itemId: number): string => {
    const remaining = stockInfo.max_quantity - currentQty;
    
    if (max === 0) return 'Out of stock';
    if (remaining === 0) return 'Maximum quantity reached';
    if (remaining <= 3) return `Only ${remaining} left`;
    return `${max} available`;
};
```
Returns: Human-readable stock status message

#### `getMaxAvailable(itemId)`
```typescript
const getMaxAvailable = (itemId: number): number => {
    return props.stockAvailability?.[itemId]?.max_quantity ?? 999;
};
```
Returns: Maximum quantity that can be ordered

### 3. **UI Components**

#### Stock Status Display
Shows under each menu item:
- **Out of stock**: Red text
- **Low stock (≤3)**: Amber/orange text
- **In stock (>3)**: Gray text
- **No ingredients**: No message shown

#### Add Button ("+") States

**Enabled** (Stock Available):
- Green background (`bg-primary`)
- White icon
- Hover effect
- Clickable

**Disabled** (Stock Unavailable):
- Gray background (`bg-gray-300`)
- Gray icon
- `cursor-not-allowed`
- Tooltip on hover showing reason

#### Tooltip on Disabled Button
```vue
<span class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-900 text-white">
    {{ getStockMessage(item.id) }}
</span>
```
- Appears above button on hover
- Shows specific reason (e.g., "Out of stock", "Maximum quantity reached")
- Arrow pointing to button
- Dark background, white text

#### Item Dimming
When item is **completely** out of stock (and qty in cart = 0):
```vue
:class="{'opacity-60': !canAddItem(item.id) && getQty(item.id) === 0}"
```
Makes the entire item card slightly transparent

### 4. **User Experience Flow**

#### Scenario A: Stock Available
1. User sees item with price
2. Stock message: "15 available" (gray text)
3. "+" button is green and enabled
4. User clicks "+" → item added to cart
5. Counter increments
6. Stock message updates: "14 available"

#### Scenario B: Low Stock
1. User sees item
2. Stock message: "Only 2 left" (amber text)
3. User adds 1 → Counter shows "1"
4. Stock message: "Only 1 left"
5. User adds 1 more → Counter shows "2"
6. Stock message: "Maximum quantity reached" (red)
7. "+" button becomes gray/disabled
8. Hover shows tooltip: "Maximum quantity reached"

#### Scenario C: Out of Stock
1. User sees faded/dimmed item
2. Stock message: "Out of stock" (red text)
3. "+" button is gray and disabled
4. Hover shows tooltip: "Out of stock"
5. User cannot add to cart

#### Scenario D: Item Already in Cart
1. Item in cart with qty = 10
2. Max available = 10 (limiting ingredient)
3. Stock message: "Maximum quantity reached"
4. "+" button disabled
5. "-" button still works to reduce quantity
6. Reducing qty re-enables "+" button

### 5. **Visual Indicators**

**Color Coding**:
- 🔴 Red: Out of stock or max reached
- 🟠 Amber: Low stock (≤3 remaining)
- ⚫ Gray: Normal stock (>3 available)
- 🟢 Green: Button enabled (can add)
- ⚪ Gray: Button disabled (cannot add)

**Dynamic Updates**:
- Stock messages update as cart changes
- Button states update reactively
- Tooltip content matches current state

### 6. **Data Structure**

**Backend sends**:
```typescript
stockAvailability: {
    [menu_item_id]: {
        max_quantity: number,  // Maximum servings based on ingredients
        available: boolean     // true if max_quantity > 0
    }
}
```

**Example**:
```json
{
    "123": { "max_quantity": 5, "available": true },
    "124": { "max_quantity": 0, "available": false },
    "125": { "max_quantity": 15, "available": true }
}
```

### 7. **Edge Cases Handled**

✅ **Item with no ingredients**: Allows 999 (unlimited)
✅ **Zero stock**: Button disabled immediately
✅ **Partial stock**: Shows remaining count
✅ **Cart quantity = max**: Disables button, allows removal
✅ **Multiple limiting ingredients**: Uses most restrictive
✅ **Batch depletion**: Calculated across all active batches

### 8. **Performance**

**Backend**:
- Single query per menu item to calculate max servings
- Cached in page load (not real-time polling)
- Efficient batch aggregation using `sum()`

**Frontend**:
- Computed properties for reactive updates
- No API calls on button click
- Instant visual feedback

**Limitation**:
- Stock availability is calculated on page load
- If another user places order, stock won't update until page refresh
- For high-volume restaurants, consider WebSocket updates

## Benefits

1. **Prevents User Frustration**: No failed orders at checkout
2. **Clear Communication**: Users know exactly what's available
3. **Guided Experience**: Visual cues guide decision-making
4. **Scarcity Signaling**: "Only 2 left" creates urgency
5. **Operational Efficiency**: Kitchen won't receive impossible orders

## Integration

✅ Works with batch-based inventory
✅ Compatible with FIFO deduction
✅ Integrates with existing cart system
✅ Maintains existing order validation (double-check at submit)

## Testing Checklist

- [ ] Out of stock item shows red message and disabled button
- [ ] Low stock (≤3) shows amber warning
- [ ] Normal stock shows gray availability count
- [ ] Button disables when cart qty = max available
- [ ] Tooltip appears on hover over disabled button
- [ ] Removing items re-enables button
- [ ] Multiple ingredients correctly calculate minimum
- [ ] Items without ingredients allow unlimited qty
- [ ] Dimming effect works for zero-stock items
