# Receipt Logo Fix

## Problem
The restaurant logo was not displaying in the receipt preview because the actual logo URL and restaurant name were not being passed from the backend to the frontend component, which was using a static placeholder.

## Changes Made

### 1. **Controller Update** (`app/Http/Controllers/Tenant/ReceiptTemplateController.php`)
Updated the `index` method to pass the resolved logo URL and restaurant name to the view.
```php
return Inertia::render('Settings/ReceiptTemplate', [
    'template' => $restaurant->receipt_template ?? $this->getDefaultTemplate(),
    'restaurant_logo' => $restaurant->logo ? asset('storage/' . $restaurant->logo) : null,
    'restaurant_name' => $restaurant->name,
]);
```

### 2. **Settings Page Update** (`resources/js/Pages/Settings/ReceiptTemplate.vue`)
Updated to accept the new props and pass them to the preview component.
```typescript
const props = defineProps<{
    template?: any;
    restaurant_logo?: string | null;
    restaurant_name?: string;
}>();
```
```html
<ReceiptPreview 
    :template="form" 
    :logo="restaurant_logo"
    :restaurant-name="restaurant_name"
/>
```

### 3. **Preview Component Update** (`resources/js/Components/ReceiptPreview.vue`)
Updated to render the actual images.
- Added `logo` and `restaurantName` props
- Replaced SVG placeholder with `<img>` tag when logo exists
- Added `grayscale` class to simulate thermal receipt printing style

## Verification
1. Ensure your restaurant has a logo uploaded (via Edit Restaurant page).
2. Go to **Receipt Template** settings.
3. The logo should now appear at the top of the receipt preview.
4. If the logo is still invalid/broken, ensure the storage link exists:
   ```bash
   php artisan storage:link
   ```
