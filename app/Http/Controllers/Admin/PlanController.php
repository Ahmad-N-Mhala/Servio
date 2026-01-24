<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $query = Plan::query();

        if ($request->input('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%')
                ->orWhere('slug', 'like', '%' . $request->input('search') . '%');
        }

        $plans = $query->orderBy('price_monthly')->paginate(20)->withQueryString();

        return inertia('Admin/Plans/Index', [
            'plans' => $plans,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('Admin/Plans/Create', [
            'availableFeatures' => config('features'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'enabled_features' => 'nullable|array',
            'max_restaurants' => 'nullable|integer|min:1',
            'max_users' => 'nullable|integer|min:1',
            'max_orders_per_month' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Key generation
        $slug = $validated['slug'];
        $validated['name'] = "plans.{$slug}_name";
        $validated['description'] = "plans.{$slug}_desc";

        // Save Translations
        $this->updateTranslations($slug, $validated['name_en'], $validated['name_ar'], $validated['description_en'], $validated['description_ar']);

        Plan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan created successfully');
    }

    public function edit(Plan $plan)
    {
        // Retrieve current translations
        // We use the slug to guess the key, or use the stored name if it looks like a key
        $slug = $plan->slug;

        // Fallback or explicit check
        $nameKey = "plans.{$slug}_name";
        $descKey = "plans.{$slug}_desc";

        return inertia('Admin/Plans/Edit', [
            'plan' => $plan,
            'availableFeatures' => config('features'),
            'translations' => [
                'name_en' => trans($nameKey, [], 'en') === $nameKey ? $plan->name : trans($nameKey, [], 'en'),
                'name_ar' => trans($nameKey, [], 'ar') === $nameKey ? $plan->name : trans($nameKey, [], 'ar'),
                'description_en' => trans($descKey, [], 'en') === $descKey ? $plan->description : trans($descKey, [], 'en'),
                'description_ar' => trans($descKey, [], 'ar') === $descKey ? $plan->description : trans($descKey, [], 'ar'),
            ]
        ]);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,' . $plan->id,
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'enabled_features' => 'nullable|array',
            'max_restaurants' => 'nullable|integer|min:1',
            'max_users' => 'nullable|integer|min:1',
            'max_orders_per_month' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $slug = $validated['slug'];
        $validated['name'] = "plans.{$slug}_name";
        $validated['description'] = "plans.{$slug}_desc";

        // Save Translations
        $this->updateTranslations($slug, $validated['name_en'], $validated['name_ar'], $validated['description_en'], $validated['description_ar']);

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully');
    }

    public function destroy(Plan $plan)
    {
        // Check if plan has active subscriptions
        $activeSubscriptions = $plan->restaurantSubscriptions()->where('status', 'active')->count();

        if ($activeSubscriptions > 0) {
            return back()->withErrors(['error' => 'Cannot delete plan with active subscriptions']);
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully');
    }

    private function updateTranslations($slug, $nameEn, $nameAr, $descEn, $descAr)
    {
        $this->saveTranslation('en', 'plans', "{$slug}_name", $nameEn);
        $this->saveTranslation('ar', 'plans', "{$slug}_name", $nameAr);
        $this->saveTranslation('en', 'plans', "{$slug}_desc", $descEn);
        $this->saveTranslation('ar', 'plans', "{$slug}_desc", $descAr);
    }

    private function saveTranslation($lang, $file, $key, $value)
    {
        $path = lang_path($lang . '/' . $file . '.php');

        if (!\Illuminate\Support\Facades\File::exists(dirname($path))) {
            \Illuminate\Support\Facades\File::makeDirectory(dirname($path), 0755, true);
        }

        $data = \Illuminate\Support\Facades\File::exists($path) ? include $path : [];
        if (!is_array($data))
            $data = [];

        $data[$key] = $value;

        $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";
        \Illuminate\Support\Facades\File::put($path, $content);

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path, true);
        }
    }
}
