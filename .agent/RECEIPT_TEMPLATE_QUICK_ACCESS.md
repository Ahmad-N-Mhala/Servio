# Receipt Template Quick Access

## Overview
Added a "Receipt Template" button to each restaurant card on the restaurant selection page (`/select-restaurant`). This allows restaurant owners to quickly access and customize their receipt settings for a specific restaurant directly from the dashboard.

## Changes Made

### 1. **MultiRestaurant Index Controller** (`app/Http/Controllers/MultiRestaurantController.php`)
Updated the `switch` method to accept a `redirect_to` parameter.
```php
public function switch(Request $request, Restaurant $restaurant)
{
    // ... verification logic ...
    
    session(['active_restaurant_id' => $restaurant->id]);

    // Handle optional redirect
    if ($request->has('redirect_to') && $request->filled('redirect_to')) {
        return redirect($request->input('redirect_to'));
    }

    return redirect($request->user()->getLandingRoute());
}
```

### 2. **Restaurant Selection Page** (`resources/js/Pages/MultiRestaurant/Index.vue`)
Added a new receipt icon button to the restaurant card footer.

**New Function:**
```typescript
const openReceiptTemplate = (restaurant: Restaurant) => {
    router.post(route('restaurants.switch', restaurant.id), {
        redirect_to: route('settings.receipt-template.index')
    });
};
```

**Button UI:**
- Located next to the "Edit" button
- Visible only to owners or admins
- Uses a sleek paper/receipt icon
- Purple hover effect to distinguish from edit action

## How It Works

1. User clicks the receipt icon on a restaurant card.
2. App sends a POST request to switch the active restaurant context.
3. The request includes the receipt template page URL as the `redirect_to` destination.
4. Server updates the session with the new restaurant ID.
5. Server redirects the user directly to the Receipt Template Settings page for that restaurant.

## Testing

1. Go to `/select-restaurant`.
2. Find a restaurant where you are the Owner.
3. Click the new receipt icon (paper with lines) next to the pencil icon.
4. You should be taken directly to `/settings/receipt-template`.
5. Verify that the correct restaurant's settings are loaded.
