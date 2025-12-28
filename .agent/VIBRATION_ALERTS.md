# Haptic Notifications (Vibration)
**Date:** 2025-12-28
**Feature:** Waiter Delivery Alerts
**Status:** ✅ COMPLETE

## Feature Description
The application now sends a **Vibration Alert** to the waiter's device whenever a new "Ready" order appears on the screen. This allows staff to perform other tasks without constantly watching the screen.

## Implementation Details
### 1. New Logic in `Waiter/Delivery.vue`
- **Known ID Tracking:** Uses `Set<string>` to store IDs of orders currently on screen.
- **Watcher:** Monitors the `orders` prop. When the prop updates (every 2s):
  - Checks if any incoming Order ID is **NOT** in the known set.
  - If a new ID is found, triggers `triggerVibration()`.
  - Updates the known ID set.

### 2. Vibration Pattern
- **Pattern:** `[500, 200, 500]`
  - 500ms Vibration
  - 200ms Pause
  - 500ms Vibration
- **API:** uses `navigator.vibrate()`.

## Browser Support
- **Android:** Supported in Chrome, Firefox, Edge.
- **iOS (iPhone):** **NOT Supported** by Safari/Webkit (Apple restriction). Consider Sound alerts as backup for iOS in future.
- **Desktop:** Hardware dependent.

## Usage
- No setup required.
- **Requirement:** User must have interacted with the page at least once (tapped/clicked) for the browser to allow vibration.
