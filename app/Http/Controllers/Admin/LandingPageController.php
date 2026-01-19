<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingModule;
use App\Models\LandingSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LandingPageController extends Controller
{
    public function index()
    {
        $modules = LandingModule::orderBy('sort_order')->get();

        // Fetch existing DB settings
        $dbSettings = LandingSetting::all()->mapWithKeys(function ($item) {
            return [$item->key => $item->value];
        });

        // Define defaults from lang files
        $defaults = [
            'features_title' => ['en' => __('landing.features', [], 'en'), 'ar' => __('landing.features', [], 'ar')],
            'features_desc' => ['en' => __('landing.modules_description', [], 'en'), 'ar' => __('landing.modules_description', [], 'ar')],

            'feature_pos_title' => ['en' => __('landing.feature_pos_title', [], 'en'), 'ar' => __('landing.feature_pos_title', [], 'ar')],
            'feature_pos_desc' => ['en' => __('landing.feature_pos_desc', [], 'en'), 'ar' => __('landing.feature_pos_desc', [], 'ar')],

            'feature_kds_title' => ['en' => __('landing.feature_kds_title', [], 'en'), 'ar' => __('landing.feature_kds_title', [], 'ar')],
            'feature_kds_desc' => ['en' => __('landing.feature_kds_desc', [], 'en'), 'ar' => __('landing.feature_kds_desc', [], 'ar')],

            'feature_inventory_title' => ['en' => __('landing.feature_inventory_title', [], 'en'), 'ar' => __('landing.feature_inventory_title', [], 'ar')],
            'feature_inventory_desc' => ['en' => __('landing.feature_inventory_desc', [], 'en'), 'ar' => __('landing.feature_inventory_desc', [], 'ar')],

            'feature_loyalty_title' => ['en' => __('landing.feature_loyalty_title', [], 'en'), 'ar' => __('landing.feature_loyalty_title', [], 'ar')],
            'feature_loyalty_desc' => ['en' => __('landing.feature_loyalty_desc', [], 'en'), 'ar' => __('landing.feature_loyalty_desc', [], 'ar')],

            'inventory_bullet_1' => ['en' => __('landing.inventory_bullet_1', [], 'en'), 'ar' => __('landing.inventory_bullet_1', [], 'ar')],
            'inventory_bullet_2' => ['en' => __('landing.inventory_bullet_2', [], 'en'), 'ar' => __('landing.inventory_bullet_2', [], 'ar')],
            'loyalty_bullet_1' => ['en' => __('landing.loyalty_bullet_1', [], 'en'), 'ar' => __('landing.loyalty_bullet_1', [], 'ar')],
            'loyalty_bullet_2' => ['en' => __('landing.loyalty_bullet_2', [], 'en'), 'ar' => __('landing.loyalty_bullet_2', [], 'ar')],

            'how_it_works_title' => ['en' => __('landing.how_it_works_title', [], 'en'), 'ar' => __('landing.how_it_works_title', [], 'ar')],
            'step_1_title' => ['en' => __('landing.step_1_title', [], 'en'), 'ar' => __('landing.step_1_title', [], 'ar')],
            'step_1_desc' => ['en' => __('landing.step_1_desc', [], 'en'), 'ar' => __('landing.step_1_desc', [], 'ar')],
            'step_2_title' => ['en' => __('landing.step_2_title', [], 'en'), 'ar' => __('landing.step_2_title', [], 'ar')],
            'step_2_desc' => ['en' => __('landing.step_2_desc', [], 'en'), 'ar' => __('landing.step_2_desc', [], 'ar')],
            'step_3_title' => ['en' => __('landing.step_3_title', [], 'en'), 'ar' => __('landing.step_3_title', [], 'ar')],
            'step_3_desc' => ['en' => __('landing.step_3_desc', [], 'en'), 'ar' => __('landing.step_3_desc', [], 'ar')],

            'about_us_title' => ['en' => __('landing.about_title_default', [], 'en'), 'ar' => __('landing.about_title_default', [], 'ar')],
            'about_us_description' => ['en' => __('landing.about_us_description_default', [], 'en'), 'ar' => __('landing.about_us_description_default', [], 'ar')],

            'contact_email' => __('landing.connect_via_email', [], 'en'),
        ];

        // Merge defaults into settings ONLY if key doesn't exist
        $settings = $dbSettings->toArray();
        foreach ($defaults as $key => $defaultVal) {
            if (!isset($settings[$key])) {
                $settings[$key] = $defaultVal;
            }
        }

        $screenshots = \App\Models\LandingScreenshot::orderBy('sort_order')->get();

        return Inertia::render('Admin/Landing/Index', [
            'modules' => $modules,
            'screenshots' => $screenshots,
            'landingSettings' => $settings,
        ]);
    }

    public function updateSettings(Request $request)
    {
        // Allow files in validation
        $request->validate([
            'settings' => 'required|array',
        ]);

        $settings = $request->input('settings', []);

        // Handle basic values and arrays
        foreach ($settings as $key => $value) {
            // Skip files here, they are handled below
            if ($request->hasFile("settings.$key")) {
                continue;
            }
            LandingSetting::set($key, $value);
        }

        // Handle File Uploads
        if ($request->allFiles()) {
            foreach ($request->allFiles()['settings'] ?? [] as $key => $fileOrFiles) {
                // If array of files (multiple)
                if (is_array($fileOrFiles)) {
                    $newPaths = [];
                    foreach ($fileOrFiles as $file) {
                        $newPaths[] = '/storage/' . $file->store('landing/images', 'public');
                    }

                    // Check for _new suffix to merge with existing
                    if (str_ends_with($key, '_new')) {
                        $baseKey = substr($key, 0, -4); // remove _new
                        $existing = LandingSetting::get($baseKey, []);
                        if (!is_array($existing))
                            $existing = $existing ? [$existing] : []; // Ensure array

                        $merged = array_merge($existing, $newPaths);
                        LandingSetting::set($baseKey, $merged);
                    } else {
                        // Standard array replace
                        LandingSetting::set($key, $newPaths);
                    }
                } else {
                    // Single File
                    $path = $fileOrFiles->store('landing/images', 'public');
                    LandingSetting::set($key, '/storage/' . $path);
                }
            }
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    public function storeModule(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|array',
            'title.en' => 'required|string',
            'title.ar' => 'required|string',
            'description' => 'required|array',
            'description.en' => 'required|string',
            'description.ar' => 'required|string',
            'icon' => 'nullable|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        LandingModule::create($validated);

        return back()->with('success', 'Module created successfully.');
    }

    public function updateModule(Request $request, LandingModule $landingModule) // Using implicit binding
    {
        $validated = $request->validate([
            'title' => 'required|array',
            'title.en' => 'required|string',
            'title.ar' => 'required|string',
            'description' => 'required|array',
            'description.en' => 'required|string',
            'description.ar' => 'required|string',
            'icon' => 'nullable|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $landingModule->update($validated);

        return back()->with('success', 'Module updated successfully.');
    }

    public function destroyModule($id)
    {
        $module = LandingModule::findOrFail($id);
        $module->delete();

        return back()->with('success', 'Module deleted successfully.');
    }

    public function storeScreenshot(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // 2MB Max
            'sort_order' => 'integer',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('landing/screenshots', 'public');

            \App\Models\LandingScreenshot::create([
                'image_path' => '/storage/' . $path,
                'sort_order' => $request->integer('sort_order', 0),
                'is_active' => true,
            ]);

            return back()->with('success', 'Screenshot uploaded successfully.');
        }

        return back()->with('error', 'No image file provided.');
    }

    public function destroyScreenshot($id)
    {
        $screenshot = \App\Models\LandingScreenshot::findOrFail($id);

        // Optionally delete file from storage
        if ($screenshot->image_path) {
            $relativePath = str_replace('/storage/', '', $screenshot->image_path);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($relativePath);
        }

        $screenshot->delete();

        return back()->with('success', 'Screenshot deleted successfully.');
    }
}
