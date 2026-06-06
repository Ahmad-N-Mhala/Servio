<?php

namespace App\Imports;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MenuItemsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $restaurantId = session('active_restaurant_id');
        $currency = \App\Models\Restaurant::find($restaurantId)->currency ?? 'AED';

        foreach ($rows as $row) {
            // Basic Validation logic within the loop
            // 'category_name_en' and 'item_name_en' and 'price' are required.
            if (empty($row['category_name_en']) || empty($row['item_name_en']) || ! isset($row['price'])) {
                continue;
            }

            // Clean data
            $categoryEn = trim($row['category_name_en']);
            $categoryAr = isset($row['category_name_ar']) ? trim($row['category_name_ar']) : $categoryEn;

            $itemEn = trim($row['item_name_en']);
            $itemAr = isset($row['item_name_ar']) ? trim($row['item_name_ar']) : $itemEn;

            $description = $row['description_en'] ?? ($row['description'] ?? null); // Handle both formats if template changes

            $price = (float) $row['price'];
            $type = isset($row['type']) && strtolower($row['type']) === 'meal' ? 'meal' : 'item';
            $isAvailable = isset($row['is_available']) ? (bool) $row['is_available'] : true;
            $sortOrder = isset($row['sort_order']) ? (int) $row['sort_order'] : 0;

            // Find or Create Category
            // MongoDB - spatie/translatable stores as JSON string. We can try exact match on Name->en or Name->ar
            // Ideally we search by EN name first.

            // Note: MongoDB searching JSON fields directly can be tricky with Eloquent depending on the driver version.
            // But usually `where('name->en', $categoryEn)` works.

            $category = MenuCategory::where('restaurant_id', $restaurantId)
                ->where('name', 'like', '%"en":"'.$categoryEn.'"%') // Fallback flexible search
                ->first();

            if (! $category) {
                $category = MenuCategory::create([
                    'restaurant_id' => $restaurantId,
                    'name' => ['en' => $categoryEn, 'ar' => $categoryAr],
                    'is_active' => true,
                    'sort_order' => 0,
                ]);
            }

            // Create Item
            MenuItem::create([
                'restaurant_id' => $restaurantId,
                'menu_category_id' => $category->id,
                'name' => ['en' => $itemEn, 'ar' => $itemAr],
                'description' => $description,
                'price' => $price,
                'currency' => $currency,
                'type' => $type,
                'is_available' => $isAvailable,
                'sort_order' => $sortOrder,
                'images' => [],
                'allergens' => [],
            ]);
        }
    }
}
