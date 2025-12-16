# RestoFy Design System - UI Components

## Overview
This document describes all reusable UI components in the RestoFy application. Each component follows consistent design patterns, theming, and styling to ensure a premium, cohesive user experience across the entire platform.

---

## 🎨 Design Principles

### Core Values
1. **Consistency**: Components look and behave the same across all pages
2. **Accessibility**: Keyboard navigation, screen reader support, semantic HTML
3. **Responsiveness**: Works beautifully on all screen sizes
4. **Performance**: Optimized rendering, minimal re-renders
5. **Premium Feel**: Gradients, shadows, animations, glassmorphism

### Color System
```css
--primary: #3B82F6 (Blue)
--primary-hover: #2563EB
--secondary: #6B7280 (Gray)
--success: #10B981 (Green)
--warning: #F59E0B (Amber)
--danger: #EF4444 (Red)
--info: #06B6D4 (Cyan)
```

---

## 📦 Component Library

### 1. **Button Component**
**Location**: `/resources/js/Components/Button.vue`

#### Usage
```vue
<Button variant="primary" size="md" @click="handleClick">
    Click Me
</Button>

<Button variant="danger" :loading="isLoading">
    <template #icon>
        <TrashIcon />
    </template>
    Delete
</Button>
```

#### Props
| Prop | Type | Default | Options |
|------|------|---------|---------|
| `type` | string | `'button'` | `button`, `submit`, `reset` |
| `variant` | string | `'primary'` | `primary`, `secondary`, `danger`, `outline`, `ghost`, `success` |
| `size` | string | `'md'` | `xs`, `sm`, `md`, `lg`, `xl` |
| `disabled` | boolean | `false` | |
| `loading` | boolean | `false` | |
| `block` | boolean | `false` | |

#### Slots
- **default**: Button text content
- **icon**: Icon element (rendered before text)

#### Features
- ✅ Loading spinner animation
- ✅ Gradient backgrounds
- ✅ Hover glow effects
- ✅ Shimmer animation on primary variant
- ✅ Disabled state
- ✅ Full-width option

---

### 2. **Table Component** ⭐ NEW
**Location**: `/resources/js/Components/Table.vue`

#### Usage
```vue
<Table
    :columns="tableColumns"
    :data="items"
    title="Inventory Items"
    empty-message="No items found"
    currency="AED"
    @sort="handleSort"
>
    <!-- Custom cell rendering -->
    <template #cell-status="{ row, value }">
        <Badge :variant="value === 'active' ? 'success' : 'danger'">
            {{ value }}
        </Badge>
    </template>

    <!-- Actions column -->
    <template #actions="{ row }">
        <Button size="xs" variant="outline" @click="edit(row)">Edit</Button>
        <Button size="xs" variant="danger" @click="delete(row)">Delete</Button>
    </template>

    <!-- Footer for pagination -->
    <template #footer>
        <Pagination :data="paginationData" />
    </template>
</Table>
```

#### Props
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `columns` | Column[] | **required** | Array of column definitions |
| `data` | any[] | **required** | Array of data rows |
| `title` | string | `''` | Optional table title |
| `emptyMessage` | string | `'No data available'` | Message when data is empty |
| `rowKey` | string | `'id'` | Unique key for each row |
| `currency` | string | `'AED'` | Currency symbol for formatting |
| `highlightRow` | function | `undefined` | Function to determine if row should be highlighted |

#### Column Interface
```typescript
interface Column {
    key: string;              // Property name in data object
    label: string;            // Column header text
    sortable?: boolean;       // Enable sorting
    format?: 'text' | 'currency' | 'number' | 'date' | 'datetime' | 'badge';
    align?: 'left' | 'center' | 'right';
}
```

#### Example Column Definition
```typescript
const columns = [
    { key: 'name', label: 'Item Name', sortable: true },
    { key: 'current_stock', label: 'Stock', format: 'number', align: 'right', sortable: true },
    { key: 'cost', label: 'Cost', format: 'currency', align: 'right' },
    { key: 'status', label: 'Status', format: 'badge' },
    { key: 'created_at', label: 'Date Added', format: 'datetime', sortable: true }
];
```

#### Slots
- **header**: Custom header content (replaces title)
- **cell-{key}**: Custom rendering for specific column
- **actions**: Actions column (edit, delete, etc.)
- **footer**: Footer content (pagination, totals, etc.)

#### Features
- ✅ Automatic sorting (client-side)
- ✅ Custom cell rendering via slots
- ✅ Built-in format support (currency, date, badges)
- ✅ Empty state with icon
- ✅ Row highlighting
- ✅ Responsive design
- ✅ Hover effects
- ✅ Premium glassmorphism styling

---

### 3. **Badge Component** ⭐ NEW
**Location**: `/resources/js/Components/Badge.vue`

#### Usage
```vue
<!-- Simple badge -->
<Badge variant="success">Active</Badge>

<!-- With status dot -->
<Badge variant="warning" dot-position="left">Pending</Badge>

<!-- With icon -->
<Badge variant="danger">
    <template #icon>
        <XIcon class="w-3 h-3" />
    </template>
    Closed
</Badge>

<!-- Removable badge -->
<Badge variant="primary" removable @remove="handleRemove">
    Selected Item
</Badge>
```

#### Props
| Prop | Type | Default | Options |
|------|------|---------|---------|
| `variant` | string | `'default'` | `success`, `warning`, `danger`, `info`, `primary`, `secondary`, `default` |
| `size` | string | `'md'` | `xs`, `sm`, `md`, `lg` |
| `dot` | boolean | `false` | |
| `dotPosition` | string | `'none'` | `left`, `right`, `none` |
| `removable` | boolean | `false` | |

#### Slots
- **default**: Badge content
- **icon**: Icon element

#### Events
- `@remove`: Emitted when remove button clicked

#### Features
- ✅ Status dot with pulse animation
- ✅ Icon support
- ✅ Removable option
- ✅ Multiple sizes
- ✅ Semantic color variants

#### Use Cases
- Order status indicators
- Category tags
- Filter chips
- Status labels
- Notification counts

---

### 4. **Select Component** ⭐ NEW
**Location**: `/resources/js/Components/Select.vue`

#### Usage
```vue
<!-- With options array -->
<Select
    v-model="selectedStatus"
    label="Order Status"
    :options="statusOptions"
    placeholder="Choose status..."
    required
/>

<!-- With slot options -->
<Select v-model="selectedTable" label="Table">
    <option value="1">Table 1</option>
    <option value="2">Table 2</option>
    <option value="3" disabled>Table 3 [OCCUPIED]</option>
</Select>

<!-- With icon and error -->
<Select
    v-model="searchCategory"
    icon="search"
    :error="errors.category"
    hint="Select a category to filter results"
    :options="categories"
/>
```

#### Props
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | string\|number\|null | **required** | Selected value |
| `label` | string | `''` | Field label |
| `id` | string | `''` | Element ID |
| `placeholder` | string | `''` | Placeholder text |
| `options` | array | `[]` | Array of options |
| `error` | string | `''` | Error message |
| `hint` | string | `''` | Help text |
| `disabled` | boolean | `false` | |
| `required` | boolean | `false` | |
| `size` | string | `'md'` | `sm`, `md`, `lg` |
| `icon` | string | `null` | `search`, `dropdown` |

#### Option Format
```typescript
// Simple array
const options = ['Option 1', 'Option 2', 'Option 3'];

// OR object array
const options = [
    { value: 'opt1', label: 'Option 1' },
    { value: 'opt2', label: 'Option 2', disabled: true }
];
```

#### Features
- ✅ v-model support
- ✅ Error validation states
- ✅ Icon support
- ✅ Disabled options
- ✅ Required field indicator
- ✅ Help text
- ✅ Consistent styling with Input component

---

### 5. **Input Component**
**Location**: `/resources/js/Components/Input.vue`

#### Usage
```vue
<Input
    v-model="form.email"
    label="Email Address"
    type="email"
    placeholder="your@email.com"
    :error="form.errors.email"
    required
/>
```

#### Props
| Prop | Type | Default |
|------|------|---------|
| `modelValue` | string | `''` |
| `label` | string | `''` |
| `type` | string | `'text'` |
| `placeholder` | string | `''` |
| `error` | string | `''` |
| `disabled` | boolean | `false` |
| `required` | boolean | `false` |

---

### 6. **Card Component**
**Location**: `/resources/js/Components/Card.vue`

#### Usage
```vue
<Card title="Dashboard Stats">
    <template #action>
        <Button size="sm">View Details</Button>
    </template>
    
    <!-- Card content -->
    <div class="stats">...</div>
</Card>
```

---

### 7. **Modal Component**
**Location**: `/resources/js/Components/Modal.vue`

#### Usage
```vue
<Modal :show="showModal" @close="showModal = false" title="Add Item">
    <!-- Modal content -->
    <form @submit.prevent="submitForm">
        ...
    </form>

    <template #footer>
        <Button variant="secondary" @click="showModal = false">Cancel</Button>
        <Button @click="submitForm">Save</Button>
    </template>
</Modal>
```

---

### 8. **Pagination Component**
**Location**: `/resources/js/Components/Pagination.vue`

#### Usage
```vue
<Pagination :data="paginatedData" />
```

---

### 9. **Toast Component**
**Location**: `/resources/js/Components/Toast.vue`

For notifications and alerts.

---

### 10. **StatsCard Component**
**Location**: `/resources/js/Components/StatsCard.vue`

For dashboard statistics display.

---

## 🎯 Best Practices

### 1. **Always Use Components**
❌ **Don't:**
```vue
<button class="px-4 py-2 bg-blue-500 text-white rounded">
    Click Me
</button>
```

✅ **Do:**
```vue
<Button variant="primary">Click Me</Button>
```

### 2. **Consistent Variants**
Use semantic variants consistently:
- `primary`: Main actions (Save, Submit, Create)
- `secondary`: Secondary actions (Cancel, Back)
- `danger`: Destructive actions (Delete, Remove)
- `success`: Positive confirmations
- `outline`: Tertiary actions

### 3. **Error Handling**
Always show validation errors:
```vue
<Select
    v-model="form.category"
    :error="form.errors.category"
    label="Category"
/>
```

### 4. **Loading States**
Show loading state during async operations:
```vue
<Button :loading="form.processing" @click="submit">
    Save Changes
</Button>
```

### 5. **Accessibility**
- Always provide `label` for form elements
- Use `required` prop when needed
- Provide meaningful `placeholder` text
- Use semantic HTML

---

## 🔄 Migration Guide

### Converting Existing Tables
**Before:**
```vue
<table class="min-w-full">
    <thead>
        <tr>
            <th>Name</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="item in items">
            <td>{{ item.name }}</td>
            <td>{{ item.stock }}</td>
            <td>
                <button @click="edit(item)">Edit</button>
            </td>
        </tr>
    </tbody>
</table>
```

**After:**
```vue
<Table :columns="columns" :data="items">
    <template #actions="{ row }">
        <Button size="xs" @click="edit(row)">Edit</Button>
    </template>
</Table>

<script setup>
const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'stock', label: 'Stock', format: 'number', align: 'right' }
];
</script>
```

### Converting Status Indicators
**Before:**
```vue
<span class="px-2 py-1 bg-green-100 text-green-800 rounded">
    Active
</span>
```

**After:**
```vue
<Badge variant="success">Active</Badge>
```

---

## 📐 Layout Components

### Glass Card
Consistent card styling using `glass-card` class:
```vue
<div class="glass-card rounded-2xl p-6">
    <!-- Content -->
</div>
```

### Page Container
```vue
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page content -->
</div>
```

---

## 🎨 Utility Classes

### Gradients
```css
bg-gradient-to-r from-primary to-primary-hover
bg-gradient-to-r from-gray-50 to-white
```

### Shadows
```css
shadow-lg
shadow-xl
hover:shadow-2xl
```

### Animations
```css
transition-all duration-300
hover:scale-105
animate-pulse
```

---

## 📝 Component Checklist

When creating a new page, ensure you use:
- [ ] `Button` for all clickable actions
- [ ] `Table` for data display
- [ ] `Badge` for status indicators
- [ ] `Select` for dropdowns
- [ ] `Input` for form fields
- [ ] `Modal` for dialogs
- [ ] Consistent spacing (p-6, gap-4, etc.)
- [ ] Glass card styling
- [ ] Responsive design

---

## 🚀 Future Components

Planned additions:
- [ ] **Tabs Component**: Tab navigation
- [ ] **Accordion Component**: Collapsible content
- [ ] **Dropdown Menu**: Context menus
- [ ] **File Upload**: Drag & drop file input
- [ ] **DatePicker**: Enhanced date selection
- [ ] **Toggle Switch**: On/off switches
- [ ] **Progress Bar**: Loading indicators
- [ ] **Skeleton Loader**: Content placeholders

---

## 💡 Tips

1. **Import Once**: Import components in your page setup
2. **TypeScript**: Use proper type definitions
3. **Slots**: Leverage slots for flexibility
4. **Events**: Emit events for parent communication
5. **Props**: Keep props simple and typed

---

## 📞 Support

For questions about component usage:
1. Check this documentation
2. Review existing implementations in `/resources/js/Pages`
3. Inspect component source code in `/resources/js/Components`

Happy coding! 🎉
