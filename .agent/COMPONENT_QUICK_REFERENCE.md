# Quick Component Reference

## 🎯 When to Use Which Component

| Need | Component | Example |
|------|-----------|---------|
| Action button | `Button` | Save, Delete, Submit |
| Data grid | `Table` | Orders list, Inventory |
| Status indicator | `Badge` | Active, Pending, Sold Out |
| Dropdown selection | `Select` | Category picker, Status filter |
| Text input | `Input` | Name, Email, Phone |
| Content container | `Card` | Dashboard sections |
| Overlay dialog | `Modal` | Add Item, Confirm Delete |
| Page navigation | `Pagination` | Browse pages |
| No data message | `EmptyState` | Empty cart, No results |
| Notifications | `Toast` | Success, Error messages |

---

## ⚡ Quick Examples

### Basic Table
```vue
<Table :columns="[
    { key: 'name', label: 'Name', sortable: true },
    { key: 'price', label: 'Price', format: 'currency' }
]" :data="products" />
```

### Form Input
```vue
<Input v-model="form.name" label="Product Name" :error="form.errors.name" required />
```

### Status Badge
```vue
<Badge :variant="order.status === 'completed' ? 'success' : 'warning'">
    {{ order.status }}
</Badge>
```

### Action Button
```vue
<Button variant="primary" :loading="processing" @click="save">
    Save Changes
</Button>
```

### Selection Dropdown
```vue
<Select v-model="category" label="Category" :options="categories" placeholder="Choose..." />
```

### Empty State
```vue
<EmptyState 
    title="No orders yet" 
    description="Start by creating your first order"
    action-text="Create Order"
    @action="createOrder"
/>
```

---

## 🎨 Variant Guide

### Button Variants
- `primary` → Blue gradient, main actions
- `secondary` → Gray, cancel/back
- `danger` → Red, delete/destructive
- `success` → Green, confirm/complete
- `outline` → Borderonly, tertiary actions
- `ghost` → Transparent, minimal actions

### Badge Variants
- `success` → Green (Active, Completed, Paid)
- `warning` → Amber (Pending, Processing)
- `danger` → Red (Cancelled, Failed, Inactive)
- `info` → Blue (Preparing, Ready)
- `primary` → Brand color (Featured, New)
- `default` → Gray (Neutral status)

---

## 📋 Table Column Formats

| Format | Use For | Example |
|--------|---------|---------|
| `text` | Default text | Name, Description |
| `currency` | Money values | Price: AED 25.00 |
| `number` | Quantities | Stock: 150 |
| `date` | Dates only | Jun 15, 2024 |
| `datetime` | Date & time | Jun 15, 2024 3:30 PM |
| `badge` | Status fields | Active, Pending |

---

## 💡 Pro Tips

1. **Always import components**:
   ```vue
   import Button from '@/Components/Button.vue';
   import Table from '@/Components/Table.vue';
   ```

2. **Use v-model for forms**:
   ```vue
   <Input v-model="form.email" />
   <Select v-model="form.category" />
   ```

3. **Show loading states**:
   ```vue
   <Button :loading="form.processing">Save</Button>
   ```

4. **Display errors**:
   ```vue
   <Input :error="form.errors.name" />
   ```

5. **Custom table cells**:
   ```vue
   <template #cell-status="{ value }">
       <Badge :variant="getVariant(value)">{{ value }}</Badge>
   </template>
   ```

---

## 🚀 Common Patterns

### Form with Validation
```vue
<form @submit.prevent="submit">
    <Input v-model="form.name" label="Name" :error="form.errors.name" required />
    <Select v-model="form.category" label="Category" :options="categories" :error="form.errors.category" />
    <Button type="submit" :loading="form.processing">Save</Button>
</form>
```

### Data Table with Actions
```vue
<Table :columns="columns" :data="items">
    <template #actions="{ row }">
        <Button size="xs" @click="edit(row)">Edit</Button>
        <Button size="xs" variant="danger" @click="remove(row)">Delete</Button>
    </template>
</Table>
```

### Modal Dialog
```vue
<Modal :show="showModal" @close="showModal = false" title="Add Item">
    <form @submit.prevent="save">
        <Input v-model="form.name" label="Name" />
        <!-- ... more fields ... -->
    </form>
    <template #footer>
        <Button variant="secondary" @click="showModal = false">Cancel</Button>
        <Button @click="save">Save</Button>
    </template>
</Modal>
```

---

## 📐 Sizing Guide

### Button Sizes
- `xs` → Very small, for tight spaces
- `sm` → Small, secondary actions
- `md` → Default, most common
- `lg` → Large, primary CTAs
- `xl` → Extra large, hero actions

### Badge Sizes
- `xs` → Inline text, minimal
- `sm` → Default for tables
- `md` → Standalone badges
- `lg` → Prominent statuses

---

## ✅ Accessibility Checklist

- [ ] All inputs have `label` prop
- [ ] Required fields marked with `required`
- [ ] Error messages displayed with `:error`
- [ ] Buttons have meaningful text (not just icons)
- [ ] Tables have proper column headers
- [ ] Forms have submit buttons with `type="submit"`
- [ ] Modals can be dismissed with ESC key

---

## 🔗 Related Files

- Components: `/resources/js/Components/`
- Full Docs: `.agent/DESIGN_SYSTEM.md`
- Examples: Check existing pages in `/resources/js/Pages/`
