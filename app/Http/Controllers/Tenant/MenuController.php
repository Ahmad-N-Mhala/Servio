<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;

class MenuController extends Controller
{
    public function index(): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('menu_management');

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        $categories = MenuCategory::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with([
                'items' => function ($query) {
                    $query->where('is_available', true)
                        ->with('ingredients')
                        ->orderBy('sort_order');
                }
            ])
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        // Force append for Inertia
        $categories->each(function ($category) {
            $category->items->each(function ($item) {
                $item->append('inventory_status');
            });
        });

        $ingredients = \App\Models\Ingredient::where('restaurant_id', $restaurant->id)->get();

        return Inertia::render('Menu/Builder', [
            'categories' => $categories,
            'ingredients' => $ingredients,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        // Check for duplicate names (EN and AR)
        $duplicate = MenuCategory::where('restaurant_id', $restaurant->id)
            ->where(function ($query) use ($validated) {
                $query->where('name.en', $validated['name']['en'])
                    ->orWhere('name.ar', $validated['name']['ar']);
            })->exists();

        if ($duplicate) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => [__('menu.duplicate_name')]
            ]);
        }

        MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        return redirect()->back()->with('message', __('menu.category_created'));
    }

    public function updateCategory(Request $request, MenuCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        // Check for duplicate names (EN and AR)
        $duplicate = MenuCategory::where('restaurant_id', $category->restaurant_id)
            ->where('_id', '!=', $category->id)
            ->where(function ($query) use ($validated) {
                $query->where('name.en', $validated['name']['en'])
                    ->orWhere('name.ar', $validated['name']['ar']);
            })->exists();

        if ($duplicate) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => [__('menu.duplicate_name')]
            ]);
        }

        $category->update($validated);

        return redirect()->back()->with('message', __('menu.category_updated'));
    }

    public function destroyCategory(MenuCategory $category)
    {
        // Find all items in this category
        $items = MenuItem::where('menu_category_id', $category->id)->get();

        foreach ($items as $item) {
            // Delete pivot associations
            \App\Models\MenuItemIngredient::where('menu_item_id', $item->id)->delete();
            // Delete the item
            $item->delete();
        }

        $category->delete();

        return redirect()->back()->with('message', __('menu.category_deleted'));
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'menu_category_id' => ['required', 'exists:menu_categories,id'],
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'images' => ['nullable', 'array'],
            'images.*' => ['mimes:jpeg,png,jpg,gif,svg,webp,avif,heic,heif', 'max:10240'], // Multiple images, increased size limit to 10MB
            'sort_order' => ['nullable', 'integer'],
            'allergens' => ['nullable', 'array'],
            'ingredients' => ['nullable', 'array'],
        ]);

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        // Check for duplicate names (EN and AR) within the same category
        $duplicate = MenuItem::where('restaurant_id', $restaurant->id)
            ->where('menu_category_id', $validated['menu_category_id'])
            ->where(function ($query) use ($validated) {
                $query->where('name.en', $validated['name']['en'])
                    ->orWhere('name.ar', $validated['name']['ar']);
            })->exists();

        if ($duplicate) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => [__('menu.duplicate_name_item')]
            ]);
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('menu-items', 'public');
            }
        }

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $validated['menu_category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'currency' => $restaurant->currency ?? 'AED',
            'images' => $imagePaths,
            'image' => $imagePaths[0] ?? null, // Primary image fallback
            'sort_order' => $validated['sort_order'] ?? 0,
            'allergens' => $validated['allergens'] ?? null,
            'is_available' => true,
        ]);

        if ($request->has('ingredients')) {
            $rawIngredients = $request->input('ingredients');
            $ingredients = is_string($rawIngredients) ? json_decode($rawIngredients, true) : $rawIngredients;

            $recipe = [];
            if (is_array($ingredients)) {
                foreach ($ingredients as $ing) {
                    if (isset($ing['id']) && isset($ing['quantity'])) {
                        $recipe[] = [
                            'ingredient_id' => $ing['id'],
                            'quantity' => (float) $ing['quantity']
                        ];

                        // Sync Pivot
                        \App\Models\MenuItemIngredient::create([
                            'menu_item_id' => new ObjectId((string) $item->id),
                            'ingredient_id' => new ObjectId((string) $ing['id']),
                            'quantity' => (float) $ing['quantity']
                        ]);
                    }
                }
            }
            $item->recipe = $recipe;
            $item->save();
        }

        return redirect()->back()->with('message', __('menu.item_created'));
    }

    public function updateItem(Request $request, MenuItem $item)
    {
        \Illuminate\Support\Facades\Log::info('Update Item Request Data:', $request->all());

        $validated = $request->validate([
            'menu_category_id' => ['required', 'exists:menu_categories,id'],
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'new_images' => ['nullable', 'array'],
            'new_images.*' => ['mimes:jpeg,png,jpg,gif,svg,webp,avif,heic,heif', 'max:10240'],
            'kept_images' => ['nullable', 'array'],
            'kept_images.*' => ['string'],
            'sort_order' => ['nullable', 'integer'],
            'allergens' => ['nullable', 'array'],
            'is_available' => ['boolean'],
            'ingredients' => ['nullable'], // Relaxed for manual processing
        ]);

        $itemData = collect($validated)->except(['new_images', 'kept_images'])->toArray();

        // Check for duplicate names (EN and AR) within the same category
        $duplicate = MenuItem::where('restaurant_id', $item->restaurant_id)
            ->where('_id', '!=', $item->id)
            ->where('menu_category_id', $validated['menu_category_id'])
            ->where(function ($query) use ($validated) {
                $query->where('name.en', $validated['name']['en'])
                    ->orWhere('name.ar', $validated['name']['ar']);
            })->exists();

        if ($duplicate) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => [__('menu.duplicate_name_item')]
            ]);
        }

        // Handle Images
        $finalImages = $request->input('kept_images', []);

        // Add new uploads
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $file) {
                $finalImages[] = $file->store('menu-items', 'public');
            }
        }

        // Ensure we always have an array
        $itemData['images'] = $finalImages;
        // Update primary image (first one)
        $itemData['image'] = $finalImages[0] ?? null;

        // Process Ingredients into 'recipe' field
        if ($request->has('ingredients')) {
            $rawIngredients = $request->input('ingredients');
            $ingredients = is_string($rawIngredients) ? json_decode($rawIngredients, true) : $rawIngredients;

            $recipe = [];

            // Sync Pivot: Clear existing first
            \App\Models\MenuItemIngredient::where('menu_item_id', $item->id)->delete();

            if (is_array($ingredients)) {
                foreach ($ingredients as $ing) {
                    if (isset($ing['id']) && isset($ing['quantity'])) {
                        // Build Recipe
                        $recipe[] = [
                            'ingredient_id' => $ing['id'],
                            'quantity' => (float) $ing['quantity']
                        ];

                        // Sync Pivot (Required for Eager Loading / Relationships)
                        \App\Models\MenuItemIngredient::create([
                            'menu_item_id' => new ObjectId((string) $item->id),
                            'ingredient_id' => new ObjectId((string) $ing['id']),
                            'quantity' => (float) $ing['quantity']
                        ]);
                    }
                }
            }
            $itemData['recipe'] = $recipe;
        }

        $item->update($itemData);
        // Deprecated: MenuItemIngredient pivot handling removal

        return redirect()->back()->with('message', __('menu.item_updated'));
    }

    public function destroyItem(MenuItem $item)
    {
        // Delete pivot associations
        \App\Models\MenuItemIngredient::where('menu_item_id', $item->id)->delete();

        $item->delete();

        return redirect()->back()->with('message', __('menu.item_deleted'));
    }
}

