<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Dashboard Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 13px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #1a1a1a;
            text-transform: uppercase;
        }

        .meta {
            color: #666;
            margin-top: 5px;
            font-size: 12px;
        }

        /* Stats Grid */
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        .stat-row {
            display: table-row;
        }

        .stat-box {
            display: table-cell;
            background: #f9f9f9;
            padding: 15px;
            text-align: center;
            border: 1px solid #eee;
            width: 23%;
            /* 4 columns roughly */
            vertical-align: middle;
        }

        .stat-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
        }

        .stat-sub {
            font-size: 9px;
            color: #888;
            margin-top: 2px;
        }

        /* Charts */
        .chart-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .chart-col {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding-right: 2%;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 10px;
        }

        table.data-table th,
        table.data-table td {
            text-align: left;
            padding: 6px;
            border-bottom: 1px solid #f0f0f0;
        }

        table.data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #555;
        }

        /* Visual Bars */
        .bar-container {
            width: 100%;
            background: #f0f0f0;
            height: 6px;
            border-radius: 3px;
            margin-top: 4px;
        }

        .bar-fill {
            height: 100%;
            border-radius: 3px;
        }

        .color-blue {
            background-color: #3b82f6;
        }

        .color-green {
            background-color: #10b981;
        }

        .color-red {
            background-color: #ef4444;
        }

        .color-purple {
            background-color: #a855f7;
        }

        .color-orange {
            background-color: #f97316;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">{{ $restaurant->name }}</div>
        <div class="meta">
            Dashboard Report<br>
            {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
        </div>
    </div>

    <!-- Stats Row 1 -->
    <div class="stats-grid">
        <div class="stat-row">
            <div class="stat-box">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value">{{ $stats['total_orders'] }}</div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">Revenue</div>
                <div class="stat-value">{{ $currency }} {{ number_format($stats['revenue'], 2) }}</div>
                <div class="stat-sub">Total Revenue</div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">Net Profit</div>
                <div class="stat-value" style="color: {{ $stats['net_profit'] >= 0 ? '#10b981' : '#ef4444' }}">
                    {{ $currency }} {{ number_format($stats['net_profit'], 2) }}
                </div>
                <div class="stat-sub">After Expenses & Waste</div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">Active Staff</div>
                <div class="stat-value">{{ $stats['active_staff'] }}</div>
            </div>
        </div>
    </div>

    <!-- Stats Row 2 -->
    <div class="stats-grid" style="margin-top:-21px;">
        <div class="stat-row">
            <div class="stat-box">
                <div class="stat-label">Low Stock</div>
                <div class="stat-value" style="color:#ef4444">{{ $stats['low_stock_count'] }}</div>
                <div class="stat-sub">Items to Reorder</div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">Avg Dining Time</div>
                <div class="stat-value">{{ $stats['avg_dining_time'] }} min</div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">Inventory Value</div>
                <div class="stat-value">{{ $currency }} {{ number_format($stats['inventory_value'], 2) }}</div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">Total Waste</div>
                <div class="stat-value" style="color:#ef4444">{{ $currency }}
                    {{ number_format($stats['total_waste'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="chart-section">
        <!-- Revenue Trend -->
        <div class="chart-col">
            <div class="section-title">Revenue Trend</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th style="text-align:right;">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($revenueChart as $day)
                        <tr>
                            <td>{{ $day['date'] }}</td>
                            <td style="text-align:right;">
                                {{ $currency }} {{ number_format($day['revenue'], 2) }}
                                @php
                                    $maxRev = $revenueChart->max('revenue') ?: 1;
                                    $width = ($day['revenue'] / $maxRev) * 100;
                                @endphp
                                <div class="bar-container">
                                    <div class="bar-fill color-orange" style="width: {{ $width }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align:center;color:#999;">No data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Order Status -->
        <div class="chart-col" style="padding-left: 2%; padding-right:0;">
            <div class="section-title">Order Status Distribution</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th style="text-align:right;">Count</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statusDistribution as $status)
                        <tr>
                            <td>{{ ucfirst($status['status']) }}</td>
                            <td style="text-align:right;">
                                <strong>{{ $status['count'] }}</strong>
                                @php
                                    $total = $statusDistribution->sum('count') ?: 1;
                                    $width = ($status['count'] / $total) * 100;
                                    $color = match ($status['status']) {
                                        'completed' => 'color-green',
                                        'cancelled' => 'color-red',
                                        default => 'color-blue'
                                    };
                                @endphp
                                <div class="bar-container">
                                    <div class="bar-fill {{ $color }}" style="width: {{ $width }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align:center;color:#999;">No data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="chart-section">
        <!-- Peak Hours -->
        <div class="chart-col">
            <div class="section-title">Peak Hours</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Hour</th>
                        <th style="text-align:right;">Orders</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peakHours as $hour)
                        <tr>
                            <td>{{ sprintf('%02d:00', $hour['hour']) }}</td>
                            <td style="text-align:right;">
                                {{ $hour['count'] }}
                                @php
                                    $maxPeak = $peakHours->max('count') ?: 1;
                                    $width = ($hour['count'] / $maxPeak) * 100;
                                @endphp
                                <div class="bar-container">
                                    <div class="bar-fill color-blue" style="width: {{ $width }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align:center;color:#999;">No data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Top Items -->
        <div class="chart-col" style="padding-left: 2%; padding-right:0;">
            <div class="section-title">Top Menu Items</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style="text-align:right;">Qty Sold</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topMenuItems as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td style="text-align:right;">
                                {{ $item['quantity'] }}
                                @php
                                    $maxQty = collect($topMenuItems)->max('quantity') ?: 1;
                                    $width = ($item['quantity'] / $maxQty) * 100;
                                @endphp
                                <div class="bar-container">
                                    <div class="bar-fill color-purple" style="width: {{ $width }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align:center;color:#999;">No sales data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Charts Row 3 -->
    <div class="chart-section">
        <!-- Avg Completion Time -->
        <div class="chart-col">
            <div class="section-title">Avg Completion Time (Minutes)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th style="text-align:right;">Avg Minutes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($avgCompletionTime as $day)
                        <tr>
                            <td>{{ $day['date'] }}</td>
                            <td style="text-align:right;">
                                {{ $day['minutes'] }} min
                                @php
                                    $maxMin = $avgCompletionTime->max('minutes') ?: 1;
                                    $width = ($day['minutes'] / $maxMin) * 100;
                                @endphp
                                <div class="bar-container">
                                    <div class="bar-fill color-blue" style="width: {{ $width }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align:center;color:#999;">No data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Waste Trend -->
        <div class="chart-col" style="padding-left: 2%; padding-right:0;">
            <div class="section-title">Waste Trend</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th style="text-align:right;">Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wasteChart as $day)
                        <tr>
                            <td>{{ $day['date'] }}</td>
                            <td style="text-align:right;">
                                {{ $currency }} {{ number_format($day['loss'], 2) }}
                                @php
                                    $maxLoss = $wasteChart->max('loss') ?: 1;
                                    $width = ($day['loss'] / $maxLoss) * 100;
                                @endphp
                                <div class="bar-container">
                                    <div class="bar-fill color-red" style="width: {{ $width }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align:center;color:#999;">No data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="section">
        <div class="section-title">Recent Orders</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td>{{ $order['order_number'] }}</td>
                        <td>{{ $order['customer_name'] }}</td>
                        <td>
                            <span
                                style="font-size:10px; padding:2px 6px; border-radius:4px; 
                                    background: {{ match ($order['status']) { 'completed' => '#dcfce7', 'cancelled' => '#fee2e2', default => '#fef9c3'} }};
                                    color: {{ match ($order['status']) { 'completed' => '#166534', 'cancelled' => '#991b1b', default => '#854d0e'} }};">
                                {{ ucfirst($order['status']) }}
                            </span>
                        </td>
                        <td>{{ $order['created_at'] }}</td>
                        <td style="text-align:right;">{{ $currency }} {{ number_format($order['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#999;">No recent orders</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        Confidential Report - Generated by RestaurFy
    </div>
</body>

</html>