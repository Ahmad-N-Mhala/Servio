# Delivery Integration Guide - Live Environment

This guide details the specific credentials and configurations required for each delivery provider to work in the **Live/Production** environment, based on the updated integration code.

## 1. Deliveroo
*   **Status**: Updated to Production URL (`api.developers.deliveroo.com`).
*   **Authentication**: Basic Auth + HMAC Webhook Verification.
*   **Required Credentials**:
    *   **API Key**: Your Deliveroo API Username/Key.
    *   **API Secret**: Your Deliveroo API Password/Secret.
    *   **Webhook Secret**: The shared secret provided by Deliveroo for webhook verification.
*   **Notes**: Ensure you have obtained the `X-Deliveroo-Sequence-Guid` and `X-Deliveroo-Hmac-Sha256` capabilities on your webhook.

## 2. Uber Eats
*   **Status**: Updated to OAuth 2.0 Client Credentials Flow.
*   **Authentication**: OAuth 2.0 (Access Token fetched via Client ID/Secret).
*   **Required Credentials**:
    *   **Client ID**: Application Client ID from Uber Developer Dashboard.
    *   **Client Secret**: Application Client Secret.
    *   **Store ID**: The UUID store identifier for the specific location.
*   **Notes**: The system will automatically fetch and cache the Access Token. You do **not** need to manually enter a Bearer token in the `api_key` field anymore.

## 3. Careem Food
*   **Status**: Updated to assume OAuth 2.0 Flow (Standard for Careem Partners) and `careemnow` domain.
*   **Authentication**: OAuth 2.0 Client Credentials.
*   **Required Credentials**:
    *   **Client ID**: Your Partner Client ID.
    *   **Client Secret**: Your Partner Client Secret.
    *   **Store ID**: Your Careem Store ID.
*   **Notes**: Verification of exact auth endpoints requires access to the Careem Partner Portal (`app.careemnow.com`).

## 4. Noon Food
*   **Status**: Updated Production URL.
*   **Authentication**: Bearer Token (Long-lived API Key).
*   **Required Credentials**:
    *   **API Key**: The Authorization Token provided by Noon.
    *   **Store ID**: Your Store ID.
*   **Notes**: Uses `https://fbpi-api.noon.partners/`.

## 5. Talabat
*   **Status**: Placeholder / Restricted Access.
*   **Authentication**: Typically requires Middleware (Deliverect/Otter) or specific Enterprise Onboarding via Delivery Hero.
*   **Notes**: Direct API access is not public. Contact your Talabat Account Manager to get specific integration credentials (PGP keys, username, password).

## 6. Keeta
*   **Status**: Plausible Setup.
*   **Authentication**: App Token + Store ID.
*   **Required Credentials**:
    *   **API Key**: `X-Keeta-App-Token`.
    *   **Store ID**: `X-Keeta-Store-Id`.
*   **Notes**: Manage credentials at `developers.mykeeta.com`.

---

## Action Items for User
1.  Go to the **Integrations** page in the dashboard.
2.  For each provider, ensure the **Client ID**, **Client Secret**, and **Store ID** fields are populated with your **Live Production Credentials**.
3.  Test a "Menu Push" or wait for an incoming order to verify connectivity.
