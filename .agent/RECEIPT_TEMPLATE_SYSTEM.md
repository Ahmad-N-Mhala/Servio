# POS Receipt Template Customization System

## Overview
Implemented a comprehensive receipt template customization system that allows restaurant owners to fully customize their POS receipt layout, content, and appearance with live preview functionality.

## Features Implemented

### 1. **Permission System**
- Added `customize_receipt_template` permission to `config/permissions.php`
- Permission is part of the "System Settings" group
- Only authorized users can access and modify receipt templates

### 2. **Receipt Template Settings Page** (`resources/js/Pages/Settings/ReceiptTemplate.vue`)

#### Header Section
- ✅ Show/Hide Restaurant Logo
- ✅ Show/Hide Restaurant Name
- ✅ Custom Header Text
- ✅ Header Alignment (Left/Center/Right)

#### Order Information Section
- ✅ Show/Hide Order Number
- ✅ Show/Hide Date & Time
- ✅ Show/Hide Table Number
- ✅ Show/Hide Customer Name
- ✅ Show/Hide Server Name

#### Items Display Section
- ✅ Show/Hide Item Notes
- ✅ Adjustable Item Name Width (50%/60%/70%)

#### Totals & Payment Section
- ✅ Show/Hide Subtotal
- ✅ Show/Hide Tax
- ✅ Show/Hide Discount (when applied)
- ✅ Show/Hide Payment Method

#### Footer Section
- ✅ Custom Footer Message
- ✅ Contact Information
- ✅ Show/Hide QR Code (for feedback/reviews)
- ✅ Footer Alignment (Left/Center/Right)

#### Receipt Settings
- ✅ Paper Width Selection (58mm/80mm)
- ✅ Font Size Selection (Small/Medium/Large)

### 3. **Live Preview Component** (`resources/js/Components/ReceiptPreview.vue`)
- Real-time preview of receipt as settings change
- Shows sample data with all selected fields
- Responsive to paper width and font size changes
- Monospace font for authentic receipt appearance
- Proper alignment and formatting

### 4. **Backend Controller** (`app/Http/Controllers/Tenant/ReceiptTemplateController.php`)

#### Features:
- **Index Method**: Loads existing template or default settings
- **Store Method**: Validates and saves template settings
- **Default Template**: Provides sensible defaults for new restaurants
- **Comprehensive Validation**: All fields properly validated

#### Validation Rules:
```php
- Boolean fields for show/hide options
- String fields with max lengths for text content
- Enum validation for alignment, paper width, and font size
- Nullable fields for optional content
```

### 5. **Routes** (`routes/web.php`)
```php
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/receipt-template', [ReceiptTemplateController::class, 'index'])
        ->name('receipt-template.index')
        ->middleware('permission:customize_receipt_template');
    Route::post('/receipt-template', [ReceiptTemplateController::class, 'store'])
        ->name('receipt-template.store')
        ->middleware('permission:customize_receipt_template');
});
```

## Data Structure

### Receipt Template Schema (stored in `restaurant.receipt_template`)
```json
{
  "show_logo": true,
  "show_restaurant_name": true,
  "header_text": "",
  "header_alignment": "center",
  "show_order_number": true,
  "show_date_time": true,
  "show_table_number": true,
  "show_customer_name": true,
  "show_server_name": false,
  "show_item_notes": true,
  "item_name_width": "60",
  "show_subtotal": true,
  "show_tax": true,
  "show_discount": true,
  "show_payment_method": true,
  "footer_text": "Thank you for your visit!",
  "contact_info": "",
  "show_qr_code": false,
  "footer_alignment": "center",
  "paper_width": "80",
  "font_size": "medium"
}
```

## User Interface

### Layout
- **Two-Column Design**:
  - Left: Settings form with organized sections
  - Right: Sticky live preview panel

### Visual Design
- Glass-card styling for each section
- Color-coded section headers with icons
- Responsive grid layout
- Smooth transitions and hover effects
- Clear visual hierarchy

### User Experience
- **Live Preview**: Changes reflect immediately
- **Reset to Default**: One-click reset with confirmation
- **Test Print**: Print preview functionality
- **Organized Sections**: Logical grouping of related settings
- **Clear Labels**: Descriptive text for all options

## Integration Points

### For POS Printing
The saved template can be accessed via:
```php
$restaurant = Restaurant::find(session('active_restaurant_id'));
$template = $restaurant->receipt_template;
```

### Usage in Receipt Generation
```php
// Example usage in bill generation
if ($template['show_logo']) {
    // Include logo
}
if ($template['show_order_number']) {
    // Display order number
}
// ... etc
```

## Next Steps for Full Implementation

### 1. **Actual Receipt Printing**
Create a receipt printing service that uses the template:
```php
// app/Services/ReceiptPrinterService.php
class ReceiptPrinterService {
    public function generateReceipt(Order $order, array $template) {
        // Generate receipt HTML/ESC-POS commands based on template
    }
}
```

### 2. **Integrate with POS**
Update the POS settle function to use the template:
```php
// In POSController@settle
if ($printBill) {
    $template = $restaurant->receipt_template;
    $receiptService->print($order, $template);
}
```

### 3. **ESC/POS Printer Support**
- Add ESC/POS command generation
- Support for thermal printers
- Network printer configuration

### 4. **Additional Features** (Future Enhancements)
- Multiple template presets
- Template import/export
- Custom logo upload
- Barcode/QR code customization
- Multi-language support for receipt text
- Receipt email templates

## Testing Checklist

- [ ] Access page with proper permission
- [ ] Access denied without permission
- [ ] All checkboxes toggle correctly
- [ ] All dropdowns work properly
- [ ] Text inputs save correctly
- [ ] Live preview updates in real-time
- [ ] Paper width changes preview size
- [ ] Font size changes preview text
- [ ] Save template persists data
- [ ] Reset to default works
- [ ] Test print opens print dialog
- [ ] Template loads on page refresh
- [ ] Validation prevents invalid data

## Files Created/Modified

### Created:
1. `/resources/js/Pages/Settings/ReceiptTemplate.vue` - Main settings page
2. `/resources/js/Components/ReceiptPreview.vue` - Live preview component
3. `/app/Http/Controllers/Tenant/ReceiptTemplateController.php` - Backend controller
4. `/.agent/RECEIPT_TEMPLATE_SYSTEM.md` - This documentation

### Modified:
1. `/config/permissions.php` - Added permission
2. `/routes/web.php` - Added routes

## Benefits

1. **Customization**: Full control over receipt appearance
2. **Branding**: Maintain brand consistency
3. **Flexibility**: Different paper sizes and fonts
4. **User-Friendly**: Live preview and intuitive interface
5. **Professional**: Clean, organized settings page
6. **Scalable**: Easy to add more options in the future

## Security

- Permission-based access control
- Input validation on backend
- XSS protection through Vue.js
- CSRF protection via Inertia.js
- Restaurant context isolation

## Performance

- Minimal database queries
- Efficient template storage (JSON in MongoDB)
- Client-side preview (no server requests)
- Optimized component rendering
