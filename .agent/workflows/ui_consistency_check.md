---
description: Ensure UI consistency across the application by using standardized components.
---

# UI Consistency Checklist

When adding new pages or features, follow this checklist to ensure a consistent, premium UI:

1.  **Use Core Components**:
    *   **Tables**: Always use `@/Components/Table.vue`. Do not create manual `<table>` tags.
    *   **Buttons**: Always use `@/Components/Button.vue`. Avoid raw `class="px-4 py-2 bg-..."`.
    *   **Cards**: Use `@/Components/Card.vue`, `StatsCard.vue`, or `ChartCard.vue`.
    *   **Inputs**: Use `@/Components/Input.vue`, `Select.vue`, etc.

2.  **Design Tokens (Tailwind)**:
    *   Use `text-primary` and `bg-primary` for main actions.
    *   Use `text-gray-900` (dark: `text-white`) for headings.
    *   Use `text-gray-500` (dark: `text-gray-400`) for secondary text.
    *   Use `rounded-xl` for containers to match the "glass" aesthetic.

3.  **Page Layout**:
    *   Wrap all pages in `<MainLayout>`.
    *   Use a consistent header structure:
        ```vue
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold ...">Title</h1>
            <div class="actions">...buttons...</div>
        </div>
        ```

4.  **Glassmorphism**:
    *   Use `glass-card` class for containers that need to stand out on the gradient background.
    *   Ensure `dark:glass-card` variants are working (usually handled by `app.css`).

5.  **Responsiveness**:
    *   Always test tables with `overflow-x-auto`.
    *   Ensure Grid layouts use `grid-cols-1 md:grid-cols-2 lg:grid-cols-4`.
