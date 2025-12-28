# Fix 403 Forbidden on "Mark as Served"
**Date:** 2025-12-28
**Feature:** Waiter Delivery
**Status:** ✅ COMPLETE

## Issue
Users encountered a **403 Forbidden** error when attempting to mark an order as "Served".

## Debugging Results
The error was caused by a faulty authorization check in `OrderDeliveryController::markAsServed`:

```php
// BEFORE (Buggy)
if ($order->restaurant_id !== $request->user()->restaurant_id) { ... }
```

The `User` model does **not** have a `restaurant_id` property directly associated with it in the context of the running application logic (it's dynamically determined). Thus, `$request->user()->restaurant_id` was returning `null`, causing the check to fail even for valid owners.

## Fix
Updated the controller to retrieve the **Current Active Restaurant** correctly:

```php
// AFTER (Fixed)
$currentRestaurant = $request->user()->currentRestaurant();
if (!$currentRestaurant || $order->restaurant_id !== $currentRestaurant->id) { ... }
```

## Verification
- **Scenario:** Waiter tries to serve order #123 (Restaurant A).
- **Check:** Is Waiter currently logged into Restaurant A?
- **Result:** Matches IDs correctly -> Success.
