<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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

        $category->update($validated);

        return redirect()->back()->with('message', __('menu.category_updated'));
    }

    public function destroyCategory(MenuCategory $category)
    {
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
            'images.*' => ['image', 'max:5120'], // Multiple images
            'sort_order' => ['nullable', 'integer'],
            'allergens' => ['nullable', 'array'],
            'ingredients' => ['nullable', 'array'],
        ]);

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

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
            $syncData = [];
            foreach ($request->ingredients as $ing) {
                if (isset($ing['id']) && isset($ing['quantity'])) {
                    $syncData[$ing['id']] = ['quantity' => $ing['quantity']];
                }
            }
            $item->ingredients()->sync($syncData);
        }

        return redirect()->back()->with('message', __('menu.item_created'));
    }

    public function updateItem(Request $request, MenuItem $item)
    {
        $validated = $request->validate([
            'menu_category_id' => ['required', 'exists:menu_categories,id'],
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'new_images' => ['nullable', 'array'],
            'new_images.*' => ['image', 'max:5120'],
            'kept_images' => ['nullable', 'array'],
            'kept_images.*' => ['string'],
            'sort_order' => ['nullable', 'integer'],
            'allergens' => ['nullable', 'array'],
            'is_available' => ['boolean'],
            'ingredients' => ['nullable', 'array'],
        ]);

        $itemData = collect($validated)->except(['new_images', 'kept_images'])->toArray();

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

        $item->update($itemData);

        if ($request->has('ingredients')) {
            $syncData = [];
            foreach ($request->ingredients as $ing) {
                if (isset($ing['id']) && isset($ing['quantity'])) {
                    $syncData[$ing['id']] = ['quantity' => $ing['quantity']];
                }
            }
            $item->ingredients()->sync($syncData);
        }

        return redirect()->back()->with('message', __('menu.item_updated'));
    }

    public function destroyItem(MenuItem $item)
    {
        $item->delete();

        return redirect()->back()->with('message', __('menu.item_deleted'));
    }
}

