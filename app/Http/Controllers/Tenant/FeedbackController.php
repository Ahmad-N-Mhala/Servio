<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $restaurantId = session('active_restaurant_id');
        $restaurant = \App\Models\Restaurant::find($restaurantId);

        $query = \App\Models\Feedback::where('restaurant_id', $restaurantId)
            ->with(['order', 'customer']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_number', 'like', "%{$search}%");
                    });
            });
        }

        // Rating filter
        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $feedback = $query->orderBy('created_at', 'desc')->paginate(20);

        return \Inertia\Inertia::render('Feedback/Index', [
            'feedback' => $feedback,
            'settings' => $restaurant->settings ?? [],
            'restaurant' => $restaurant ? $restaurant->only(['id', 'name', 'slug', 'logo']) : null,
            'filters' => [
                'search' => $request->input('search', ''),
                'rating' => $request->input('rating', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    public function updateSettings(Request $request)
    {
        \Log::info('updateSettings called', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'path' => $request->path(),
        ]);

        $validated = $request->validate([
            'page_title' => ['nullable', 'string', 'max:50'],
            'welcome_message' => ['nullable', 'string', 'max:100'],
            'rating_label' => ['nullable', 'string', 'max:100'],
            'theme_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'text_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'header_logo' => ['nullable', 'image', 'max:2048'],
            'background_image' => ['nullable', 'image', 'max:5120'],
            'reset_logo' => ['nullable', 'boolean'],
        ]);

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404);

        // Ensure settings is an array
        $currentSettings = $restaurant->settings;
        if (is_string($currentSettings)) {
            $currentSettings = json_decode($currentSettings, true) ?? [];
        } elseif (!is_array($currentSettings)) {
            $currentSettings = [];
        }

        $feedbackDesign = $currentSettings['feedback_design'] ?? [];

        // Handle logo reset
        if ($request->input('reset_logo') === true || $request->input('reset_logo') === 'true') {
            unset($feedbackDesign['header_logo']); // Remove custom logo, will fall back to restaurant logo
        }
        // Handle File Uploads
        elseif ($request->hasFile('header_logo')) {
            $path = $request->file('header_logo')->store('feedback/logos', 'public');
            $feedbackDesign['header_logo'] = '/storage/' . $path;
        }

        if ($request->hasFile('background_image')) {
            $path = $request->file('background_image')->store('feedback/backgrounds', 'public');
            $feedbackDesign['background_image'] = '/storage/' . $path;
        }

        // Update Fields
        $fields = ['page_title', 'welcome_message', 'rating_label', 'theme_color', 'text_color'];
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
