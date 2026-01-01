# Transaction Sequencing & Order Numbers

## Overview
Implemented a custom starting point for order numbers (Transaction IDs), allowing the system to continue counting from a user-defined number (e.g., matching a previous POS system).

## Changes Made

### 1. **Restaurant Model**
- Added `next_order_number` field to the restaurant configuration.
- Enabled mass-assignment for this field.

### 2. **Receipt Settings** (`ReceiptTemplate.vue`)
- Added a new input field: **"Start Transaction/Order Number From"**.
- This allows you to set the starting number (e.g., 1000). The next order created will use this number.

### 3. **Backend Logic** (`ReceiptTemplateController.php`)
- Updated settings save logic to store the `next_order_number` directly on the restaurant record.

### 4. **Order Generation** (`OrderController.php`)
- Replaced the random order number generator (e.g., `ORD-XY123`) with a meaningful sequential logic.
- **New Logic**:
    1.  Get the `next_order_number` from settings (Defaults to 1).
    2.  Use it as the Order Number.
    3.  Increment the counter in the database for the next order.

### 5. **Orders Page**
- The **"Order Number"** column in the orders list will now display these sequential numbers (e.g., `1001`, `1002`, `1003`) matching your "Transaction" settings.
- You can search for these numbers using the main search bar.

## How to Configure
1.  Go to **settings/receipt-template**.
2.  Find **"Start Transaction/Order Number From"**.
3.  Enter your last receipt number + 1 (e.g., if your last receipt was 5000, enter 5001).
4.  Save.
5.  Create a new order. It will be **#5001**.
6.  The next will be **#5002**, and so on.
