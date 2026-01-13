# Communication Automation UI Enhancement

## Changes Made

### **Enhanced Automation Rule Modal Form** ✅

Redesigned the "New Automation Rule" modal with better UI/UX and additional fields for comprehensive email and SMS configuration.

---

## New Features Added

### 1. **Organized Sections with Visual Separation**

Each configuration group now has its own color-coded section:

- **🔵 Rule Information** (Blue gradient)
  - Rule name
  - Trigger event with emojis
  - Channel selection with icons

- **🔵 SMS Settings** (Blue background, conditional)
  - Sender name (optional)
  - SMS message with character counter (160 max)
  - Mono-spaced font for better readability

- **🟣 Email Settings** (Purple background, conditional)
  - Email subject (required)
  - Email header (optional)
  - Email body (required)
  - Email footer (optional)

- **🟢 Timing Setup** (Green background)
  - When to send (Immediately/Before/After)
  - Days and time configuration
  - Visual explanation of timing

- **🟡 Conditions** (Amber background)
  - Min order amount
  - Min orders count
  - Days since last order (for churn)

### 2. **SMS Configuration Fields**

```typescript
sms_sender_name: ''  // Custom sender name
sms_content: ''      // SMS message (160 chars max)
```

**Features**:
- Character counter (live update)
- Warning when exceeding 160 characters
- Monospace font for better text preview
- Conditional rendering (only shows when SMS channel selected)

### 3. **Email Configuration Fields**

```typescript
email_header: ''     // Large heading at top of email
email_footer: ''     // Signature/closing message
```

**Features**:
- Subject field (required when email selected)
- Header for visual impact
- Main body for detailed content
- Footer for signature/branding
- Conditional rendering (only shows when email selected)

### 4. **Improved UI Elements**

#### Channel Selection
**Before**: Plain checkboxes
**After**: Visual cards with icons and hover effects

```vue
<div class="flex gap-4 p-3 bg-white rounded-lg border border-gray-200">
  <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
    <!-- SMS icon, checkbox, label -->
  </label>
  <label class="flex items-center gap-2 cursor-pointer hover:text-purple-600">
    <!-- Email icon, checkbox, label -->
  </label>
</div>
```

#### Trigger Event
Added emojis for better visual identification:
- 📝 New Customer Registration
- 🛒 Order Placed
- ✅ Order Completed
- ❌ Order Cancelled
- 🎂 Customer Birthday
- ⚠️ Churn Risk

#### Timing Configuration
Enhanced explanation box:
```
┌──────────────────────────────────────────┐
│ ℹ️ Instant delivery: Message will be    │
│    sent immediately when event occurs.   │
└──────────────────────────────────────────┘
```

#### Active Toggle
Visual checkbox with dynamic icon:
- ✅ Green when active
- ⚪ Gray when inactive

---

## Form Structure

### Complete Form Fields

```typescript
{
  // Basic Info
  name: string                    // Rule name
  channels: string[]              // ['sms', 'email']
  trigger_event: string           // Event type
  
  // SMS Fields
  sms_sender_name: string         // ⭐ NEW
  sms_content: string             // ⭐ NEW
  
  // Email Fields
  subject: string                 // Email subject
  email_header: string            // ⭐ NEW
  content: string                 // Email body
  email_footer: string            // ⭐ NEW
  
  // Timing
  timing_type: string             // immediately/before/after
  timing_days: number             // Days offset
  timing_time: string             // Time of day
  
  // Conditions
  conditions: {
    min_order_amount: number | null
    min_orders_count: number | null
    days_since_last_order: number | null
    loyalty_tier: string
  }
  
  // Status
  is_active: boolean
}
```

---

## Visual Design Improvements

### 1. **Section Headers**
Each section has an icon + title:
```vue
<h3 class="text-sm font-bold flex items-center gap-2">
  <svg class="w-5 h-5 text-blue-600">...</svg>
  SMS Settings
</h3>
```

### 2. **Color Coding**
- **Blue**: SMS-related content
- **Purple**: Email-related content
- **Green**: Timing configuration
- **Amber**: Conditional logic
- **Gray**: General settings

### 3. **Responsive Grid**
- Desktop: 2-3 columns
- Mobile: 1 column (stacks vertically)

### 4. **Help Text**
Every field has contextual help:
- "Keep it short and clear (160 characters max)"
- "Large heading at the top of the email"
- "Signature or closing message"

### 5. **Visual Feedback**
- Character counter for SMS (0/160)
- Color changes (red when over limit)
- Required field indicators (*)
- Hover effects on interactive elements

---

## Conditional Rendering

### SMS Section
Shows only when SMS channel is selected:
```vue
<div v-if="templateForm.channels.includes('sms')">
  <!-- SMS configuration -->
</div>
```

### Email Section
Shows only when Email channel is selected:
```vue
<div v-if="templateForm.channels.includes('email')">
  <!-- Email configuration -->
</div>
```

### Churn Risk Condition
Shows only for churn_risk trigger:
```vue
<Input 
  v-if="templateForm.trigger_event === 'churn_risk'"
  v-model="templateForm.conditions.days_since_last_order"
  label="Days Since Last Order"
/>
```

---

## Modal Size

Upgraded from `size="lg"` to `size="2xl"` for better readability and more comfortable form filling.

---

## User Experience Enhancements

### 1. **Progressive Disclosure**
Only show relevant fields based on selections:
- SMS fields appear when SMS is selected
- Email fields appear when Email is selected
- Timing fields expand when not "immediate"

### 2. **Visual Hierarchy**
- Important fields are larger
- Optional fields clearly marked
- Required fields have (*)
- Sections have clear boundaries

### 3. **Guidance**
- Placeholders show examples
- Help text explains purpose
- Icons indicate field type
- Color coding shows relationships

### 4. **Validation Feedback**
- Real-time character counting
- Visual warnings (red text)
- Error messages inline
- Required field indicators

---

## Benefits

### For Users:
✅ **Clearer Organization**: Easy to find and fill fields
✅ **Better Guidance**: Examples and hints for every field
✅ **Visual Feedback**: Know immediately if something is wrong
✅ **Responsive**: Works great on all screen sizes
✅ **Professional**: Premium, modern UI

### For Restaurant Owners:
✅ **More Control**: Separate SMS and email content
✅ **Better Emails**: Header and footer for branding
✅ **SMS Optimization**: Character counter prevents truncation
✅ **Flexible Timing**: Precise scheduling options
✅ **Smart Conditions**: Target specific customer segments

---

## Examples

### SMS Configuration
```
Sender Name: Servio
Message: "Hi! Your order #12345 is ready for pickup. 
          Thanks for choosing us! 🎉"
Character Count: 67/160
```

### Email Configuration
```
Subject: Your Order is Ready! 🎉
Header: Thank You for Your Order
Body: Hi there! We're excited to let you know that 
      your order #12345 is ready for pickup...
Footer: Best regards, The Servio Team
```

### Timing Example
```
Trigger: Order Completed
Timing: After Event
Days: 1
Time: 10:00 AM

Result: "Scheduled: Message will be sent 1 day(s) 
         after the event at 10:00."
```

---

## Technical Notes

### Form Initialization
All new fields initialized in `templateForm`:
- Default to empty strings
- Populated from template when editing
- Reset on cancel/close

### Validation
- SMS content required when SMS selected
- Email subject required when Email selected
- Character limit enforced (160 for SMS)
- At least one channel must be selected

### Data Flow
```
Frontend Form
    ↓
Template Form (useForm)
    ↓
API Request (POST/PUT)
    ↓
Backend Validation
    ↓
Database Storage
```

---

## Future Enhancements

Potential improvements:
- [ ] Rich text editor for email body
- [ ] Email template preview
- [ ] Variable placeholders ({{customer_name}})
- [ ] SMS preview on mobile device
- [ ] A/B testing support
- [ ] Email template library
- [ ] Emoji picker for SMS
- [ ] Scheduled send testing

---

## Related Files

- **Frontend**: `/resources/js/Pages/Communication/Index.vue`
- **Components Used**:
  - `Modal.vue` (size: 2xl)
  - `Input.vue` (form fields)
  - `Button.vue` (actions)
  
---

## Testing Checklist

- [ ] SMS section appears when SMS selected
- [ ] Email section appears when Email selected
- [ ] Character counter updates in real-time
- [ ] Required fields validated
- [ ] Form submits with all fields
- [ ] Edit mode pre-fills all fields
- [ ] Cancel clears the form
- [ ] Mobile responsive
- [ ] Help text visible
- [ ] Icons display correctly
