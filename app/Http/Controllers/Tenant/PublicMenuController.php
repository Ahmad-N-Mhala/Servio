<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicMenuController extends Controller
{
    public function show(Request $request, ?string $locale = null): JsonResponse
    {
        if ($locale) {
            app()->setLocale($locale);
        }

        $restaurant = \App\Models\Restaurant::first();

        if (!$restaurant) {
            return response()->json(['error' => 'Restaurant not found'], 404);
        }

        $categories = MenuCategory::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with([
                'items' => function ($query) {
                    $query->where('is_available', true)
                        ->with('extras')
                        ->orderBy('sort_order');
                }
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) use ($locale) {
                return [
                    'id' => $category->id,
                    'name' => $this->getTranslatedName($category->name, $locale),
                    'description' => $category->description,
                    'items' => $category->items->map(function ($item) use ($locale) {
                        return [
                            'id' => $item->id,
                            'name' => $this->getTranslatedName($item->name, $locale),
                            'description' => $item->description,
                            'price' => (float) $item->price,
                            'currency' => $item->currency,
                            'image' => $item->image,
                            'allergens' => $item->allergens,
                            'extras' => $item->extras,
                        ];
                    }),
                ];
            });

        return response()->json([
            'restaurant' => [
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
                'currency' => $restaurant->currency,
                'locale' => $restaurant->locale,
            ],
            'categories' => $categories,
        ]);
    }

    protected function getTranslatedName(array $name, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        if (isset($name[$locale])) {
            return $name[$locale];
        }

        if (isset($name['en'])) {
            return $name['en'];
        }

        return reset($name) ?: '';
    }
}

