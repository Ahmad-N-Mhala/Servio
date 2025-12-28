# Real-Time Refresh Update
**Date:** 2025-12-28
**Feature:** Kitchen & Delivery Real-Time Updates
**Status:** ✅ COMPLETE

## Change
Decreased the auto-refresh interval for both Kitchen and Waiter views.

### Settings
- **Old Interval:** 5s (Kitchen), 10s (Delivery)
- **New Interval:** **2s** (Both)

## Impact Analysis
### 1. User Experience
- **Pros:** Feels "Instant". Kitchen marks ready -> Waiter sees it almost immediately.
- **Cons:** UI might "flicker" slightly more often (though Inertia is good at handling this).

### 2. Performance
- **Server Load:** Increases significanty. If you have 10 waiters + 2 kitchen screens:
  - 12 clients * 30 requests/minute = **360 requests/minute**.
  - This is ~6 requests per second.
- **Recommendation:** usage of `light` queries (we only fetch necessary props).
- **Scalability:** For a single restaurant, this is **FINE**. If scaling to 100s of tenants on one server, consider WebSockets (Pusher/Reverb) instead of polling.
