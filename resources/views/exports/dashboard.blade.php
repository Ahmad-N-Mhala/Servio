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
            {{ __('reports.dashboard_report') }}<br>
            {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
        </div>
    </div>

    <!-- New Highlights Row (Matched to UI) -->
    <div class="section-title" style="margin-top: 10px;">{{ __('dashboard.highlights') }}
        ({{ __('reports.date_range') }})</div>
    <div class="stats-grid">
        <div class="stat-row">
            <div class="stat-box">
                <div class="stat-label">{{ __('dashboard.sales') }}</div>
                <div class="stat-value">{{ $currency }} {{ number_format($stats['revenue'], 2) }}</div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('reports.total_orders') }}</div>
                <div class="stat-value">{{ $stats['total_orders'] }}</div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('dashboard.customers') }}</div>
                <div class="stat-value">{{ $stats['total_unique_customers'] }}</div>
                <div class="stat-sub">{{ $stats['new_customers'] }} {{ __('dashboard.new_customers') }} /
                    {{ $stats['repeat_customers'] }} {{ __('dashboard.repeat_customers') }}</div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('dashboard.rewards_redeemed') }}</div>
                <div class="stat-value">{{ $stats['rewards_redeemed'] }}</div>
            </div>
        </div>
    </div>

    <!-- Stats Row 1 -->
    <div class="section-title" style="margin-top: 20px;">{{ __('reports.key_metrics') }}</div>
    <div class="stats-grid">
        <div class="stat-row">
            <div class="stat-box">
                <div class="stat-label">{{ __('reports.net_profit') }}</div>
                <div class="stat-value" style="color: {{ $stats['net_profit'] >= 0 ? '#10b981' : '#ef4444' }}">
                    {{ $currency }} {{ number_format($stats['net_profit'], 2) }}
                </div>
                <div class="stat-sub">After Expenses & Waste</div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('reports.total_waste') }}</div>
                <div class="stat-value" style="color:#ef4444">{{ $currency }}
                    {{ number_format($stats['total_waste'], 2) }}
                </div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('reports.inventory_value') }}</div>
                <div class="stat-value">{{ $currency }} {{ number_format($stats['inventory_value'], 2) }}</div>
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
                <div class="stat-label">{{ __('dashboard.avg_order_value') }}</div>
                <div class="stat-value">{{ $currency }}
                    {{ number_format(($stats['revenue'] / max(1, $stats['total_orders'])), 2) }}</div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">Monthly Expenses</div>
                <div class="stat-value">{{ $currency }} {{ number_format($stats['monthly_expenses'], 2) }}</div>
            </div>
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">Gen. Time</div>
                <div class="stat-value" style="font-size: 10px;">{{ now()->format('H:i') }}</div>
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

    <!-- Charts Row 4 -->
    <div class="chart-section">
        <!-- Top Categories -->
        <div class="chart-col">
            <div class="section-title">Top Categories (Sales)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th style="text-align:right;">Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCategories as $cat)
                        <tr>
                            <td>{{ $cat['name'] }}</td>
                            <td style="text-align:right;">
                                {{ $currency }} {{ number_format($cat['value'], 2) }}
                                @php
                                    $maxCat = collect($topCategories)->max('value') ?: 1;
                                    $width = ($cat['value'] / $maxCat) * 100;
                                @endphp
                                <div class="bar-container">
                                    <div class="bar-fill color-purple" style="width: {{ $width }}%"></div>
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

        <!-- Top Customers -->
        <div class="chart-col" style="padding-left: 2%; padding-right:0;">
            <div class="section-title">Top Customers</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Orders</th>
                        <th style="text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCustomers as $customer)
                        <tr>
                            <td>{{ $customer['name'] }}</td>
                            <td>{{ $customer['count'] }}</td>
                            <td style="text-align:right;">
                                {{ $currency }} {{ number_format($customer['total'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align:center;color:#999;">No data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Customer Retention -->
    <div class="section">
        <div class="section-title">Customer Retention (Visit Funnel)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Milestone</th>
                    <th style="text-align:right;">Count</th>
                    <th style="text-align:right;">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($retentionStats as $stat)
                    <tr>
                        <td>{{ $stat['label'] }}</td>
                        <td style="text-align:right;">{{ $stat['count'] }} customers</td>
                        <td style="text-align:right;">
                            {{ $stat['percentage'] }}%
                            <div class="bar-container">
                                <div class="bar-fill color-green" style="width: {{ $stat['percentage'] }}%"></div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align:center;color:#999;">No data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        Generated by Servio - {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>

</html>