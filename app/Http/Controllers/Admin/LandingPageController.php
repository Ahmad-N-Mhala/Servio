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
        $settings = LandingSetting::all()->mapWithKeys(function ($item) {
            return [$item->key => $item->value];
        });

        $screenshots = \App\Models\LandingScreenshot::orderBy('sort_order')->get();

        return Inertia::render('Admin/Landing/Index', [
            'modules' => $modules,
            'screenshots' => $screenshots,
            'landingSettings' => $settings,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($data['settings'] as $key => $value) {
            LandingSetting::set($key, $value);
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
