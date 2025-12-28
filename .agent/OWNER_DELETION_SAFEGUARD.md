# Restaurant Owner Deletion Safeguard
**Date:** 2025-12-28
**Feature:** Prevent Last Owner Deletion
**Status:** ✅ COMPLETE

## Overview
Enhanced the staff deletion process to ensure that a restaurant always has at least one owner. Also improved the safety of user account deletion to support multi-restaurant users.

## Changes Validation Logic

### 1. Last Owner Check
Before deleting a staff member with the `owner` role, the system now performs two checks:
- Counts active owners in the `Staff` table for the current restaurant.
- Counts active owners in the `restaurant_user` pivot table for the current restaurant.

**Condition:** If either count is **1 or less**, the deletion is **blocked** with an error message:
> "Cannot delete the only owner of the restaurant. There must be at least one owner per restaurant."

### 2. Multi-Restaurant Safety
Before permanently deleting a user account:
- The system checks if the user's email exists in the `restaurant_user` table for **any other** restaurants.
- **If associated with other restaurants:** The specific staff record is removed, but the `User` account is **preserved**.
- **If NOT associated with others:** The `User` account is fully deleted.

## Benefits
- **Prevents Orphaned Restaurants**: Guarantees administrative access is always maintained.
- **Supports Multi-Tenancy**: Users owning multiple restaurants won't lose access to all of them when removed from one.
