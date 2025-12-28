# Global Waiter Notifications
**Date:** 2025-12-28
**Feature:** Waiter Delivery Alerts
**Status:** ✅ COMPLETE

## Change Overview
Moved the **Vibration Alert** logic from the local Delivery Page to the **Global Main Layout**. This ensures waiters are notified of new ready orders regardless of which page they are currently viewing (e.g., while taking orders on POS).

## Security & Privacy
- **Strict Permission Guard:** The global polling logic runs `if (hasPermission('deliver_orders'))`.
- **Backend Protection:** The `checkNewOrders` API returns empty results if the user lacks permission.
- **Outcome:** Kitchen staff, Admins, or unauthorized users will **NEVER** receive these notifications.

## Technical Implementation
1. **API Endpoint:** `GET /service/delivery/check`
   - Returns a lightweight list of Order IDs that are `ready`.
   - Protected by permission check.

2. **MainLayout Logic:**
   - Polls the API every 3 seconds.
   - Uses `localStorage` (`known_ready_orders`) to persist state across page navigation.
   - Compares current IDs vs known IDs.
   - **Trigger:** If a *new* ID appears -> Vibrate pattern `[200, 100, 200, 100, 200]` + Toast Notification.

3. **Cleanup:**
   - Removed duplicate polling/vibration from `Waiter/Delivery.vue` to prevent "Double Buzz".
   - `Delivery.vue` stays strictly for visual list management (refreshes every 2s).
