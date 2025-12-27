<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bill #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 30px;
        }

        .restaurant-name {
            font-size: 32px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .restaurant-info {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        .bill-details {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }

        .col-left,
        .col-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .col-right {
            text-align: right;
        }

        .label {
            font-weight: 700;
            color: #888;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .value {
            margin-bottom: 15px;
            font-size: 15px;
            color: #222;
        }

        .value strong {
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th {
            text-align: left;
            background-color: #f8f9fa;
            color: #555;
            font-weight: 700;
            padding: 15px 12px;
            border-bottom: 2px solid #eee;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px 12px;
            border-bottom: 1px solid #f5f5f5;
            color: #333;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            width: 100%;
            display: table;
            margin-top: 20px;
        }

        .totals-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }

        .totals-right {
            display: table-cell;
            width: 50%;
            text-align: right;
        }

        .total-row {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .total-row span {
            display: inline-block;
            min-width: 100px;
        }

        .grand-total {
            font-size: 22px;
            font-weight: 800;
            color: #000;
            border-top: 2px solid #000;
            padding-top: 15px;
            margin-top: 15px;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #f0f0f0;
            padding-top: 20px;
        }

        .status-badge {
            background: #eee;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .notes-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="restaurant-name">{{ $order->restaurant->name ?? $tenant->name ?? 'RestaurFy' }}</div>
            <div class="restaurant-info">
                @if($order->restaurant->address) {{ $order->restaurant->address }} @endif
                @if($order->restaurant->city) , {{ $order->restaurant->city }} @endif
                @if($order->restaurant->country) , {{ $order->restaurant->country }} @endif
                @if($order->restaurant->phone) <br> Tel: {{ $order->restaurant->phone }} @endif
                @if($order->restaurant->email) <br>
                    {{ is_array($order->restaurant->email) ? (current($order->restaurant->email)) : $order->restaurant->email }}
                @endif
            </div>
        </div>

        <div class="bill-details">
            <div class="col-left">
                <div class="label">Bill To</div>
                <div class="value">
                    @if($order->customer)
                        <strong>{{ $order->customer->name }}</strong><br>
                        {{ $order->customer->phone }}
                    @else
                        Guest
                    @endif
                </div>

                @if($order->table)
                    <div class="label">Table</div>
                    <div class="value">#{{ $order->table->table_number }} <span
                            style="color:#888; font-size:12px">({{ $order->table->location ?? 'Main' }})</span></div>
                @endif
            </div>
            <div class="col-right">
                <div class="label">Order Details</div>
                <div class="value">
                    Order #: <strong>{{ $order->order_number }}</strong><br>
                    Date: {{ $order->created_at->format('M d, Y h:i A') }}<br>
                    Status: {{ ucfirst($order->status) }}
                    @if(isset($order->waiter))
                        <br>Waiter: {{ $order->waiter->name }}
                    @endif
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="50%">Item</th>
                    <th width="15%" class="text-center">Qty</th>
                    <th width="15%" class="text-right">Price</th>
                    <th width="20%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <strong style="font-weight:600; color:#333;">{{ $item->menuItem->name ?? 'Item' }}</strong>
                            @if(isset($item->notes))
                                <br><small
                                    style="color: #999; font-size: 11px; margin-top:2px; display:block;">{{ $item->notes }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ $order->currency ?? 'AED' }}
                            {{ number_format($item->unit_price > 0 ? $item->unit_price : ($item->menuItem->price ?? 0), 2) }}
                        </td>
                        <td class="text-right">{{ $order->currency ?? 'AED' }}
                            {{ number_format($item->total_price > 0 ? $item->total_price : ($item->quantity * ($item->menuItem->price ?? 0)), 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <div class="totals-left">
                @if($order->notes)
                    <div class="notes-box">
                        <strong>Note:</strong><br>
                        {{ $order->notes }}
                    </div>
                @endif

                @if($order->points_earned > 0)
                    <div style="margin-top: 15px; color: #4f46e5; font-size: 13px;">
                        You earned <strong>{{ $order->points_earned }}</strong> loyalty points with this order!
                    </div>
                @endif
            </div>
            <div class="totals-right">
                <table style="width: 100%; margin-bottom: 0;">
                    <tr>
                        <td style="text-align: right; padding: 5px 0; border: none; color: #666;">Subtotal</td>
                        <td style="text-align: right; padding: 5px 0; border: none; width: 120px;">
                            {{ $order->currency ?? 'AED' }} {{ number_format($order->subtotal, 2) }}
                        </td>
                    </tr>
                    @if($order->tax > 0)
                        <tr>
                            <td style="text-align: right; padding: 5px 0; border: none; color: #666;">Tax
                                ({{ $order->subtotal > 0 ? round(($order->tax / $order->subtotal) * 100) : 0 }}%)</td>
                            <td style="text-align: right; padding: 5px 0; border: none;">
                                {{ $order->currency ?? 'AED' }} {{ number_format($order->tax, 2) }}
                            </td>
                        </tr>
                    @endif
                    @if($order->discount_amount > 0)
                        <tr>
                            <td style="text-align: right; padding: 5px 0; border: none; color: #e53e3e;">
                                Discount
                                @if($order->discount_type === 'percent')
                                    ({{ (float) $order->discount_value }}%)
                                @endif
                            </td>
                            <td style="text-align: right; padding: 5px 0; border: none; color: #e53e3e;">
                                -{{ $order->currency ?? 'AED' }} {{ number_format($order->discount_amount, 2) }}
                            </td>
                        </tr>
                    @endif
                    @if($order->additional_charge > 0)
                        <tr>
                            <td style="text-align: right; padding: 5px 0; border: none; color: #4f46e5;">
                                Extra Charge
                                @if($order->additional_charge_type === 'percent')
                                    ({{ (float) $order->additional_charge_value }}%)
                                @endif
                            </td>
                            <td style="text-align: right; padding: 5px 0; border: none; color: #4f46e5;">
                                +{{ $order->currency ?? 'AED' }} {{ number_format($order->additional_charge, 2) }}
                            </td>
                        </tr>
                    @endif
                </table>
                <div class="grand-total" style="text-align: right;">
                    <span style="font-size: 14px; font-weight: normal; color: #666; margin-right: 15px;">Total</span>
                    {{ $order->currency ?? 'AED' }} {{ number_format($order->total, 2) }}
                </div>
            </div>
        </div>

        <div class="footer">
            Thank you for dining with
            <strong>{{ $order->restaurant->name ?? $tenant->name ?? 'RestaurFy' }}</strong>!<br>
            <span style="opacity: 0.6; font-size: 10px;">Powered by RestaurFy</span>
        </div>
    </div>
</body>

</html>