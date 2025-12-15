<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bill #{{ $order->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4f46e5;
        }

        .header h1 {
            font-size: 32px;
            color: #4f46e5;
            margin-bottom: 10px;
        }

        .header .subtitle {
            font-size: 16px;
            color: #666;
        }

        .bill-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .bill-info-left,
        .bill-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .bill-info-right {
            text-align: right;
        }

        .info-group {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 5px;
        }

        .info-value {
            color: #555;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        .items-table thead {
            background-color: #4f46e5;
            color: white;
        }

        .items-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .items-table td {
            padding: 12px;
        }

        .totals {
            margin-top: 30px;
            text-align: right;
        }

        .total-row {
            padding: 8px 0;
            display: flex;
            justify-content: flex-end;
            gap: 100px;
        }

        .total-label {
            font-weight: 600;
            min-width: 150px;
            text-align: right;
        }

        .total-value {
            min-width: 100px;
            text-align: right;
        }

        .grand-total {
            border-top: 2px solid #4f46e5;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #4f46e5;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        .points-earned {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-completed {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-processing {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ $tenant->name ?? 'RestaurFy' }}</h1>
            <div class="subtitle">Order Bill</div>
        </div>

        <div class="bill-info">
            <div class="bill-info-left">
                <div class="info-group">
                    <div class="info-label">Bill #</div>
                    <div class="info-value">{{ $order->id }}</div>
                </div>

                @if($order->customer)
                    <div class="info-group">
                        <div class="info-label">Customer</div>
                        <div class="info-value">
                            {{ $order->customer->name }}<br>
                            {{ $order->customer->phone }}
                        </div>
                    </div>
                @endif

                @if($order->table)
                    <div class="info-group">
                        <div class="info-label">Table</div>
                        <div class="info-value">{{ $order->table->table_number }}</div>
                    </div>
                @endif
            </div>

            <div class="bill-info-right">
                <div class="info-group">
                    <div class="info-label">Date</div>
                    <div class="info-value">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                </div>

                @if($order->completed_at)
                    <div class="info-group">
                        <div class="info-label">Completed At</div>
                        <div class="info-value">{{ $order->completed_at->format('d M Y, h:i A') }}</div>
                    </div>
                @endif

                <div class="info-group">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $order->status }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align: center;">Quantity</th>
                    <th style="text-align: right;">Price</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->menuItem->name ?? 'Item' }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">${{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span class="total-value">${{ number_format($order->subtotal, 2) }}</span>
            </div>

            @if($order->tax > 0)
                <div class="total-row">
                    <span class="total-label">Tax:</span>
                    <span class="total-value">${{ number_format($order->tax, 2) }}</span>
                </div>
            @endif

            @if($order->discount_amount > 0)
                <div class="total-row">
                    <span class="total-label">Discount:</span>
                    <span class="total-value">-${{ number_format($order->discount_amount, 2) }}</span>
                </div>
            @endif

            <div class="total-row grand-total">
                <span class="total-label">Total:</span>
                <span class="total-value">${{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        @if($order->points_earned > 0)
            <div class="points-earned">
                🎉 You earned {{ $order->points_earned }} loyalty points with this order!
            </div>
        @endif

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>{{ $tenant->name ?? 'RestaurFy' }}</p>
        </div>
    </div>
</body>

</html>