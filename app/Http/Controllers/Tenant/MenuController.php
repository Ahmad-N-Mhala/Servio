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
            'image' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'allergens' => ['nullable', 'array'],
            'ingredients' => ['nullable', 'array'],
        ]);

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        $item = MenuItem::create([
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
            'image' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'allergens' => ['nullable', 'array'],
            'is_available' => ['boolean'],
            'ingredients' => ['nullable', 'array'],
        ]);

        $item->update($validated);

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

