<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        $restaurantId = session('active_restaurant_id');
        $restaurant = \App\Models\Restaurant::find($restaurantId);

        $feedback = \App\Models\Feedback::where('restaurant_id', $restaurantId)
            ->with(['order', 'customer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return \Inertia\Inertia::render('Feedback/Index', [
            'feedback' => $feedback,
            'settings' => $restaurant->settings ?? []
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'page_title' => ['nullable', 'string', 'max:50'],
            'welcome_message' => ['nullable', 'string', 'max:100'],
            'theme_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'text_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'header_logo' => ['nullable', 'image', 'max:2048'],
            'background_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404);

        $currentSettings = $restaurant->settings ?? [];
        $feedbackDesign = $currentSettings['feedback_design'] ?? [];

        // Handle File Uploads
        if ($request->hasFile('header_logo')) {
            $path = $request->file('header_logo')->store('feedback/logos', 'public');
            $feedbackDesign['header_logo'] = '/storage/' . $path;
        }

        if ($request->hasFile('background_image')) {
            $path = $request->file('background_image')->store('feedback/backgrounds', 'public');
            $feedbackDesign['background_image'] = '/storage/' . $path;
        }

        // Update Fields
        $fields = ['page_title', 'welcome_message', 'theme_color', 'text_color'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $feedbackDesign[$field] = $validated[$field];
            }
        }

        $currentSettings['feedback_design'] = $feedbackDesign;
        $restaurant->settings = $currentSettings;
        $restaurant->save();

        return redirect()->back()->with('success', 'Feedback page design updated.');
    }
}
