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
        $restaurant = \App\Models\Restaurant::first();
        
        $categories = MenuCategory::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with(['items' => function ($query) {
                $query->where('is_available', true)
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Menu/Builder', [
            'categories' => $categories,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $restaurant = \App\Models\Restaurant::first();

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
            'image' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'allergens' => ['nullable', 'array'],
        ]);

        $restaurant = \App\Models\Restaurant::first();

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $validated['menu_category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'currency' => $restaurant->currency ?? 'AED',
            'image' => $validated['image'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'allergens' => $validated['allergens'] ?? null,
            'is_available' => true,
        ]);

        return redirect()->back()->with('message', __('menu.item_created'));
    }

    public function updateItem(Request $request, MenuItem $item)
    {
        $validated = $request->validate([
            'menu_category_id' => ['required', 'exists:menu_categories,id'],
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'allergens' => ['nullable', 'array'],
            'is_available' => ['boolean'],
        ]);

        $item->update($validated);

        return redirect()->back()->with('message', __('menu.item_updated'));
    }

    public function destroyItem(MenuItem $item)
    {
        $item->delete();

        return redirect()->back()->with('message', __('menu.item_deleted'));
    }
}

