# PROPOSAL: Rebranding to Servio by Kenildock

## 1. Executive Summary
We will restructure the application's branding to establish **Kenildock** as the parent company and **Servio** as the core product/system. The technical foundation will move to the `kenildock.com` domain.

## 2. Domain & URL Structure
The application will be hosted as follows:

- **Production Domain:** `https://kenildock.com`
- **Application Logic:** The entire current "RestoFy" system will run at the root of `kenildock.com`.
- **Tenant Subdomains:** If enabled, `restaurant-name.kenildock.com`.

### Environment Configuration (.env)
```ini
APP_NAME="Servio"
APP_URL="https://kenildock.com"
COMPANY_NAME="Kenildock"
SYSTEM_NAME="Servio"
MAIL_FROM_ADDRESS="support@kenildock.com"
MAIL_FROM_NAME="Servio Support"
```

## 3. Visual Branding Preview ("How it will look")

### A. Landing Page (`kenildock.com`)
*   **Browser Tab Title:** `Servio | Restaurant Management by Kenildock`
*   **Navigation Bar:**
    *   **Left**: [Logo Icon] **Servio**
    *   **Right**: Login / Get Started
*   **Hero Section:** "Revolutionize Your Restaurant with **Servio**"
*   **Footer:**
    *   Left: "Servio - The all-in-one restaurant OS."
    *   Bottom Center: `© 2026 Kenildock. All rights reserved.`
    *   Links: Privacy Policy | Terms (Hosted on Kenildock)

### B. Login & Auth Screens
*   **Logo Area:** Big "Servio" Logo.
*   **Subtext:** "Sign in to your dashboard."
*   **Footer:** "A product by **Kenildock**".

### C. Admin Dashboard (Sidebar & Footer)
*   **Sidebar Header:** **Servio** (clean, large font).
*   **Footer (Bottom of page):** 
    *   `v1.0.0 | © 2026 Kenildock`
    *   Link to "Kenildock Support".

### D. Emails & Notifications
*   **Sender:** `Servio System <no-reply@kenildock.com>`
*   **Email Footer:**
    > You are receiving this email because you use Servio.
    > Kenildock Inc. [Address]

## 4. Implementation Plan

### Step 1: Configuration
- Update `.env` file with new `APP_URL` and `APP_NAME`.
- Update `config/app.php` to reference environment variables.

### Step 2: Localization & Strings
- Modify `lang/en/common.php`, `lang/en/landing.php`, `lang/en/auth.php`.
- Replace instances of "RestoFy" with "Servio".
- Add "Kenildock" to copyright strings.

### Step 3: Frontend Templates
- **`Welcome.vue`**: Update Footer copyright to hardcoded Kenildock or dynamic configuration.
- **`Layouts/GuestLayout.vue`**: Add "Product by Kenildock" small print.
- **`Layouts/AuthenticatedLayout.vue`**: Update footer branding.

## 5. Request for Assets
To proceed, please provide (or I can use placeholders):
1.  **Servio Logo** (SVG/PNG)
2.  **Kenildock Logo** (Optional, for "Powered by" badges)

---
**Status:** Awaiting Approval to Execute.
