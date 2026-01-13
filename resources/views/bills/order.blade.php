<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Bill #{{ $order->order_number }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
            /* Thermal paper width */
        }

        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 5mm;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .header {
            margin-bottom: 5mm;
            text-align: center;
        }

        .logo {
            max-width: 40mm;
            max-height: 20mm;
            margin: 0 auto 2mm auto;
            display: block;
        }

        .restaurant-name {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .restaurant-info {
            font-size: 10px;
            color: #444;
        }

        .divider {
            border-bottom: 1px dashed #000;
            margin: 3mm 0;
            width: 100%;
        }

        .order-info {
            margin-bottom: 4mm;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }

        /* Table simulation for PDF compatibility */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding-bottom: 1mm;
            font-size: 10px;
            text-transform: uppercase;
        }

        td {
            padding: 2px 0;
            vertical-align: top;
        }

        .item-row td {
            padding-top: 1mm;
        }

        .item-name {
            font-weight: 600;
        }

        .extras {
            font-size: 9px;
            color: #555;
            padding-left: 2mm;
        }

        .totals-section {
            margin-top: 3mm;
            border-top: 1px dashed #000;
            padding-top: 2mm;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .grand-total {
            font-size: 14px;
            font-weight: 800;
            border-top: 2px solid #000;
            margin-top: 2mm;
            padding-top: 2mm;
        }

        .footer {
            margin-top: 8mm;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>

<body>
    @php
        // Helper specifically for this view
        $getLocalized = function ($input) {
            if (!is_string($input))
                return $input;
            if (str_starts_with($input, '{')) {
                $decoded = json_decode($input, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $locale = app()->getLocale();
                    return $decoded[$locale] ?? $decoded['en'] ?? $input;
                }
            }
            return $input;
        };

        // Logo Logic
        $logoData = null;
        if ($order->restaurant->logo) {
            $logoPath = storage_path('app/public/' . $order->restaurant->logo);
            if (!file_exists($logoPath)) {
                $logoPath = public_path('storage/' . $order->restaurant->logo);
            }
            if (file_exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = file_get_contents($logoPath);
                $logoData = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
    @endphp

    <div class="header">
        @if($logoData)
            <img src="{{ $logoData }}" class="logo" alt="Logo">
        @endif
        <div class="restaurant-name">{{ $order->restaurant->name ?? 'Restaurant' }}</div>
        <div class="restaurant-info">
            {{ $order->restaurant->address ?? '' }}<br>
            {{ $order->restaurant->phone ?? '' }}
        </div>
    </div>

    <div class="divider"></div>

    <div class="order-info">
        <table style="width: 100%">
            <tr>
                <td><strong>Order #:</strong> {{ $order->order_number }}</td>
                <td class="text-right">{{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Type:</strong> {{ ucfirst($order->type) }}</td>
                <td class="text-right">
                    @if($order->table) Table: {{ $order->table->name ?? $order->table->table_number }} @endif
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>Customer:</strong> {{ $order->customer_name ?: 'Guest' }}
                    @if($order->customer_phone) <br>{{ $order->customer_phone }} @endif
                </td>
            </tr>
            @if($order->delivery_provider)
                <tr>
                    <td colspan="2"><strong>Delivery:</strong> {{ ucfirst($order->delivery_provider) }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="divider"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="10%">Qty</th>
                <th width="60%">Item</th>
                <th width="30%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                @php
                    // Determine name (Source of truth: Current Snapshot Name)
                    $name = $getLocalized($item->name);

                    // Extras string logic
                    $extrasOutput = [];
                    if (!empty($item->extras) && is_array($item->extras)) {
                        foreach ($item->extras as $extra) {
                            $exName = $getLocalized($extra['name'] ?? 'Extra');
                            $exPrice = (float) ($extra['price'] ?? 0);
                            $exStr = $exName;
                            if ($exPrice > 0) {
                                $exStr .= ' (' . number_format($exPrice, 2) . ')';
                            }
                            $extrasOutput[] = $exStr;
                        }
                    }
                @endphp
                <tr class="item-row">
                    <td class="text-center" style="vertical-align: top;">{{ $item->quantity }}</td>
                    <td>
                        <div class="item-name">{{ $name }}</div>
                        @if(!empty($extrasOutput))
                            <div class="extras">+ {{ implode(', ', $extrasOutput) }}</div>
                        @endif
                        @if(!empty($item->notes))
                            <div class="extras" style="font-style: italic;">Note: {{ $item->notes }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <table style="width: 100%">
            <tr>
                <td class="text-right" width="60%">Subtotal</td>
                <td class="text-right" width="40%">{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            @if($order->tax > 0)
                <tr>
                    <td class="text-right">Tax</td>
                    <td class="text-right">{{ number_format($order->tax, 2) }}</td>
                </tr>
            @endif
            @if($order->discount_amount > 0)
                <tr>
                    <td class="text-right">Discount</td>
                    <td class="text-right">-{{ number_format($order->discount_amount, 2) }}</td>
                </tr>
            @endif
            @if($order->additional_charge > 0)
                <tr>
                    <td class="text-right">Service Charge</td>
                    <td class="text-right">{{ number_format($order->additional_charge, 2) }}</td>
                </tr>
            @endif
        </table>

        <div class="grand-total">
            <table style="width: 100%">
                <tr>
                    <td class="text-left" style="font-size: 11px;">TOTAL ({{ $order->restaurant->currency ?? 'AED' }})
                    </td>
                    <td class="text-right">{{ number_format($order->total, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if($order->points_earned > 0)
        <div style="margin-top: 3mm; text-align: center; border: 1px dashed #444; padding: 2mm;">
            <div style="font-size: 10px; font-weight: bold;">Loyalty Points Earned: {{ $order->points_earned }}</div>
            <div style="font-size: 9px;">Balance: {{ optional($order->customer)->loyalty_points ?? 0 }}</div>
        </div>
    @endif

    <div class="footer">
        <div>Thank you for your visit!</div>
        @if($order->restaurant->website)
            <div>{{ $order->restaurant->website }}</div>
        @endif
        <div style="margin-top: 2mm; font-size: 8px; color: #888;">Powered by Servio</div>
    </div>
</body>

</html>