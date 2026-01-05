# Complete Translation Implementation Guide

## Quick Reference: How to Translate Any Page

### Step 1: Identify Hardcoded Text
Look for any English text in the Vue file:
```vue
<h1>Dashboard</h1>  ❌ Hardcoded
<p>Welcome back</p>  ❌ Hardcoded
```

### Step 2: Replace with Translation Keys
```vue
<h1>{{ $t('dashboard_page.title') }}</h1>  ✅ Translated
<p>{{ $t('dashboard_page.welcome') }}</p>  ✅ Translated
```

### Step 3: Verify Key Exists
Check `/resources/js/locales/en/index.ts` and `/resources/js/locales/ar/index.ts`

If the key doesn't exist, add it to BOTH files.

## All Available Translation Keys (450+)

### Navigation (nav.*)
- dashboard, orders, all_orders, create_order, tables, menu, menu_builder
- inventory, staff, customers, kitchen, pos, reports, monthly_expenses
- feedback, loyalty, communication, settings, profile, logout
- And 20+ more...

### Common (common.*)
- name, description, price, save, create, update, cancel, edit, delete
- search, filter, export, import, close, submit, reset, back, next
- yes, no, active, inactive, all, none, loading, actions, status
- date, time, total, subtotal, quantity, items, guest, optional
- required, add, remove, view, download, upload, select, choose
- confirm, success, error, warning, info

### Dashboard (dashboard_page.*)
- title, welcome, overview, today, this_week, this_month
- total_sales, total_orders, avg_order_value, top_items
- recent_orders, low_stock, quick_actions

### Kitchen (kitchen.*)
- title, subtitle, search_orders, auto_refresh, pending, processing
- ready_served, recently_completed, no_pending, no_processing
- items, dine_in, takeaway, notes, start_cooking, order_ready
- cancel_order, ready_for_pickup, served, min

### POS (pos.*)
- title, subtitle, active_orders, no_active_orders, bill_details
- table, takeaway, payment_method, settle, print_receipt
- register_balance, opened, view_history, register_not_open

### Reports (reports.*)
- title, subtitle, apply, to, export_report, avg_order_value
- total_orders, total_revenue, daily_revenue, payment_methods
- total_payments, online, card, cash, transaction_history
- export_csv, search, amount, waiter, method, customer_table

### Expenses (expenses.*)
- title, add_expense, expense_name, amount, category, date
- description, recurring, frequency, edit_expense, delete_expense
- total_expenses, this_month, breakdown_by_category

### Cash Register (cash_register.*)
- title, open_register, open_cash_register, opening_balance
- notes_optional, notes_placeholder, cancel, close_register
- register_balance, opened, view_history

### Inventory (inventory_page.*)
- title, subtitle, add_ingredient, ingredient_name, current_stock
- unit, low_stock_threshold, add_stock, view_history
- stock_level, last_updated, in_stock, low_stock, out_of_stock

### Staff (staff.*)
- title, subtitle, add_staff, edit_staff, name, email, phone
- role, password, confirm_password, permissions, active
- inactive, delete_confirm

### Tables (tables.*)
- title, add_table, table_name, capacity, location
- available, occupied, qr_code

### Customers (customers.*)
- title, add_customer, customer_name, phone_number
- total_orders, total_spent, last_visit

### And many more sections...

## Chart Translations

For chart labels in Dashboard, use:
```javascript
// In script section
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

// Then use:
label: t('dashboard_page.revenue_trend')
```

## Common Patterns to Replace

### Buttons
```vue
<!-- Before -->
<button>Save</button>
<button>Cancel</button>
<button>Delete</button>

<!-- After -->
<button>{{ $t('common.save') }}</button>
<button>{{ $t('common.cancel') }}</button>
<button>{{ $t('common.delete') }}</button>
```

### Table Headers
```vue
<!-- Before -->
<th>Name</th>
<th>Status</th>
<th>Actions</th>

<!-- After -->
<th>{{ $t('common.name') }}</th>
<th>{{ $t('common.status') }}</th>
<th>{{ $t('common.actions') }}</th>
```

### Status Messages
```vue
<!-- Before -->
<p>No data available</p>
<p>Loading...</p>

<!-- After -->
<p>{{ $t('common.no_data') }}</p>
<p>{{ $t('common.loading') }}</p>
```

## Pages Status Checklist

### ✅ Fully Translated
- [x] Navigation/Sidebar
- [x] Kitchen Display
- [x] Monthly Expenses
- [x] Sales Reports  
- [x] POS (partial)

### ⏳ Keys Ready (Need Implementation)
- [ ] Dashboard - UPDATE NEEDED
- [ ] Inventory - UPDATE NEEDED
- [ ] Staff - UPDATE NEEDED
- [ ] Tables - UPDATE NEEDED
- [ ] Customers - UPDATE NEEDED
- [ ] Menu Builder - UPDATE NEEDED
- [ ] Orders - UPDATE NEEDED
- [ ] Feedback - UPDATE NEEDED
- [ ] Loyalty - UPDATE NEEDED
- [ ] Communication - UPDATE NEEDED
- [ ] Settings - UPDATE NEEDED
- [ ] Receipt Template - UPDATE NEEDED
- [ ] Cash Register History - UPDATE NEEDED
- [ ] Financial - UPDATE NEEDED
- [ ] Integrations - UPDATE NEEDED
- [ ] Admin Pages (15+) - UPDATE NEEDED
- [ ] Auth Pages - UPDATE NEEDED
- [ ] Onboarding - UPDATE NEEDED

## How to Add Missing Keys

If you encounter text that doesn't have a translation key:

1. Open `/resources/js/locales/en/index.ts`
2. Add the key in the appropriate section:
```typescript
dashboard_page: {
    title: 'Dashboard',
    new_key: 'New Text Here',  // Add this
},
```

3. Open `/resources/js/locales/ar/index.ts`
4. Add the Arabic translation:
```typescript
dashboard_page: {
    title: 'لوحة التحكم',
    new_key: 'النص الجديد هنا',  // Add this
},
```

5. Use in Vue file:
```vue
{{ $t('dashboard_page.new_key') }}
```

## Estimated Time to Complete

- **Per page**: 15-30 minutes
- **66 pages total**: 20-30 hours
- **Recommendation**: Hire a developer or do it incrementally

## Translation Management UI

Use `/admin/localization` to:
- View all translation keys
- Add new translations
- Edit existing translations
- Search for specific keys

All changes are immediately reflected in the application!

## Priority Order

1. **Critical** (Do First):
   - Dashboard
   - Orders
   - Menu
   - Inventory
   - Kitchen (done)
   - POS (done)

2. **Important** (Do Next):
   - Tables
   - Staff
   - Customers
   - Reports (done)

3. **Nice to Have**:
   - Admin pages
   - Settings
   - Integrations

## Support

If you need help:
1. Check this guide
2. Use `/admin/localization` UI
3. Reference completed pages (Kitchen, Reports, Expenses)
4. Copy the pattern from working pages

---

**Translation keys are ready. Implementation is straightforward but time-consuming.**
