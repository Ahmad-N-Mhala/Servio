# Admin Restaurant Management Enhancements
**Date:** 2025-12-28
**Feature:** Sync Create/Edit with Onboarding Fields
**Status:** ✅ COMPLETE

## Enhancements
We have synchronized the **Create Restaurant** and **Edit Restaurant** pages with the fields found in the **Onboarding** flow.

### 1. Restaurant Create Page
- **Complete Overhaul:** The page now matches the Onboarding capability.
- **New Section: Owner Account:**
  - Create the Owner User instantly.
  - Fields: Name, Email, Phone, Password.
- **New Section: Location:**
  - Added Google Map Location (URL).
  - Added Country, State, City, Zip Code.
- **Backend:** `RestaurantController@store` now wraps creation in a transaction to create both the Restaurant and the Owner User, linking them immediately.

### 2. Restaurant Edit Page
- **New Fields:**
  - **Google Map Location**: Added to Location section.
  - **Owner Management**: Added ability to update Owner Name and Owner Phone (previously only Email/Password were mutable).
- **Backend:** `RestaurantController@update` updated to process these new fields.

### 3. Database & Logic
- Validated that `Restaurant` model supports `google_map_location`.
- Ensured consistent validation rules across Store and Update methods.
