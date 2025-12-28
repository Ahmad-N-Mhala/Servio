# Admin View Enhancement: Restaurant & Subscription Merge
**Date:** 2025-12-28
**Feature:** Super Admin Dashboard
**Status:** ✅ COMPLETE

## Objective
Combine Restaurant management and Subscription details into a single, comprehensive table view for the Super Admin.

## Changes
### 1. `Admin/Restaurants/Index.vue`
- **Columns Consolidated:**
  - Removed separate `country` column.
  - Merged `Name`, `ID`, `Owner`, and `Location` into a single **"Restaurant & Owner"** column.
- **New Subscription Column:**
  - Added a dedicated column to show rich subscription data.
  - Displays: **Plan Name**, **Billing Cycle**, **Status Badge**, **Expiry Date**.
- **UI Improvements:**
  - Added relative expiry formatting (e.g. "Expires in 5 days").
  - Added visual tags for location (🌍, 🏙️).

## Outcome
Super Admins can now see the complete status of a restaurant (Ownership + Subscription Health) at a glance without navigating to a separate subscriptions page.
