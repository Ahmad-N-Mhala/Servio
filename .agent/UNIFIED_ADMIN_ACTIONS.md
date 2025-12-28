# Super Admin: Unified Restaurant & Subscription Management
**Date:** 2025-12-28
**Feature:** Action Grouping & Subscription Management
**Status:** ✅ COMPLETE

## Overview
We have unified the Restaurant Management and Subscription Management into a single view (`Admin/Restaurants/Index.vue`).
Instead of cluttered action buttons, we now have a streamlined **Manage** button that groups all operational actions.

## Key Changes
1. **Action Modal:**
   - Click "Manage" to open a row-specific modal.
   - **Operations:**
     - ✏️ **Edit Details:** Direct link to edit page.
     - 💳 **Manage Subscription:** Opens the subscription editor.
     - 🗑️ **Delete / Restore:** Toggle restaurant active status.

2. **Subscription Management Integration:**
   - Moved Subscription Modal logic directly into the Restaurant page.
   - Supports: Assigning Plans, Upgrading/Downgrading, Extending Dates, Cancelling.
   - Status updates are reflected instantly in the table.

3. **Backend Updates:**
   - `RestaurantController` now passes `plans` to the view to support the subscription dropdown.

## Benefits
- **One-Stop Shop:** Super Admins can handle *everything* about a tenant from one table.
- **Cleaner UI:** Removed 3-4 separate buttons from the table row, replacing them with a single "Manage" action.
- **Mobile Friendly:** The Action Modal works perfectly on mobile, unlike crowded table cells.
