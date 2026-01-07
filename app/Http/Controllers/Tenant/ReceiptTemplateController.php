<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReceiptTemplateController extends Controller
{
    public function index(): Response
    {
        $restaurant = Restaurant::find(session('active_restaurant_id'));

        if (!$restaurant) {
            abort(404, 'Restaurant not found');
        }

        $logoUrl = null;
        if ($restaurant->logo) {
            $logoUrl = filter_var($restaurant->logo, FILTER_VALIDATE_URL)
                ? $restaurant->logo
                : asset('storage/' . $restaurant->logo);
        }

        return Inertia::render('Settings/ReceiptTemplate', [
            'template' => $restaurant->receipt_template ?? $this->getDefaultTemplate(),
            'restaurant_logo' => $logoUrl,
            'restaurant_name' => $restaurant->name,
            'next_order_number' => $restaurant->next_order_number,
            'google_map_location' => $restaurant->google_map_location,
        ]);
    }

    public function store(Request $request)
    {
        $restaurant = Restaurant::find(session('active_restaurant_id'));

        if (!$restaurant) {
            abort(404, 'Restaurant not found');
        }

        $validated = $request->validate([
            // Header
            'receipt_language' => ['sometimes', 'string', 'in:en,ar'],
            'show_logo' => ['required', 'boolean'],
            'show_restaurant_name' => ['required', 'boolean'],
            'header_text' => ['nullable', 'string', 'max:255'],
            'header_alignment' => ['required', 'string', 'in:left,center,right'],

            // Order Info
            'show_order_number' => ['required', 'boolean'],
            'show_date_time' => ['required', 'boolean'],
            'show_table_number' => ['required', 'boolean'],
            'show_customer_name' => ['required', 'boolean'],
            'show_server_name' => ['required', 'boolean'],

            // Items
            'show_item_notes' => ['required', 'boolean'],
            'item_name_width' => ['required', 'string', 'in:50,60,70'],

            // Totals
            'show_subtotal' => ['required', 'boolean'],
            'show_tax' => ['required', 'boolean'],
            'show_discount' => ['required', 'boolean'],
            'show_payment_method' => ['required', 'boolean'],

            // Footer
            'footer_text' => ['nullable', 'string', 'max:500'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'show_qr_code' => ['required', 'boolean'],
            'footer_alignment' => ['required', 'string', 'in:left,center,right'],

            // Settings
            'paper_width' => ['required', 'string', 'in:58,80'],
            'font_size' => ['required', 'string', 'in:small,medium,large'],
            // Transaction Sequence
            'next_order_number' => ['nullable', 'integer', 'min:1'],
        ]);

        $nextOrderNumber = $validated['next_order_number'] ?? null;
        unset($validated['next_order_number']);

        $restaurant->update([
            'receipt_template' => $validated,
        ]);

        if ($nextOrderNumber) {
            $restaurant->update(['next_order_number' => $nextOrderNumber]);
        }

        return redirect()->back()->with('message', 'Receipt template saved successfully!');
    }

    private function getDefaultTemplate(): array
    {
        return [
            // Header
            'receipt_language' => 'en',
            'show_logo' => true,
            'show_restaurant_name' => true,
            'header_text' => '',
            'header_alignment' => 'center',

            // Order Info
            'show_order_number' => true,
            'show_date_time' => true,
            'show_table_number' => true,
            'show_customer_name' => true,
            'show_server_name' => false,

            // Items
            'show_item_notes' => true,
            'item_name_width' => '60',

            // Totals
            'show_subtotal' => true,
            'show_tax' => true,
            'show_discount' => true,
            'show_payment_method' => true,

            // Footer
            'footer_text' => 'Thank you for your visit!',
            'contact_info' => '',
            'show_qr_code' => false,
            'footer_alignment' => 'center',

            // Settings
            'paper_width' => '80',
            'font_size' => 'medium',
        ];
    }
}
