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
            border-bottom: 2px solid #4f46e5;
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

        .badge {
            display: inline-block;
            background: #4f46e5;
            color: #fff;
            font-size: 10px;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 4px;
            letter-spacing: 0.5px;
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
            margin-top: 4px;
        }

        .stat-sub span {
            display: inline-block;
            background: #e8e8ff;
            color: #4f46e5;
            border-radius: 3px;
            padding: 1px 5px;
            margin: 1px;
            font-size: 9px;
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
            border-bottom: 2px solid #e8e8ff;
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
            padding: 7px 6px;
            border-bottom: 1px solid #f0f0f0;
        }

        table.data-table th {
            background-color: #f0f0ff;
            font-weight: bold;
            color: #4f46e5;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        table.data-table tr:nth-child(even) td {
            background-color: #fafafa;
        }

        /* Visual Bars */
        .bar-container {
            width: 100%;
            background: #f0f0f0;
            height: 5px;
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

        .color-indigo {
            background-color: #4f46e5;
        }

        .text-green {
            color: #10b981;
        }

        .text-red {
            color: #ef4444;
        }

        .rank-badge {
            display: inline-block;
            background: #4f46e5;
            color: #fff;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            text-align: center;
            line-height: 16px;
            font-size: 9px;
            font-weight: bold;
            margin-right: 4px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 2px solid #e8e8ff;
            padding-top: 15px;
        }

        .page-break {
            page-break-before: always;
        }

        .no-data {
            text-align: center;
            color: #bbb;
            font-style: italic;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <!-- ===== HEADER ===== -->
    <div class="header">
        <div class="title">{{ $restaurant->name }}</div>
        <div class="meta">
            {{ __('reports.dashboard_report') }}<br>
            {{ $startDate->format('M d, Y') }} &mdash; {{ $endDate->format('M d, Y') }}
        </div>
        <div class="badge">{{ __('reports.restaurant') }}: {{ $restaurant->name }}</div>
    </div>

    <!-- ===== SECTION 1: HIGHLIGHTS ===== -->
    <div class="section-title" style="margin-top: 10px;">
        {{ __('dashboard.highlights') }}
        <span style="font-size:11px; font-weight:normal; color:#888;">({{ __('reports.date_range') }})</span>
    </div>
    <div class="stats-grid">
        <div class="stat-row">
            <!-- Sales -->
            <div class="stat-box">
                <div class="stat-label">{{ __('dashboard.sales') }}</div>
                <div class="stat-value">{{ $currency }} {{ number_format($stats['revenue'], 2) }}</div>
            </div>
            <!-- Total Orders -->
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('reports.total_orders') }}</div>
                <div class="stat-value">{{ $stats['total_orders'] }}</div>
            </div>
            <!-- Customers -->
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('dashboard.customers') }}</div>
                <div class="stat-value">{{ $stats['total_unique_customers'] }}</div>
                <div class="stat-sub">
                    <span>{{ __('dashboard.new_customers') }}: {{ $stats['new_customers'] }}</span>
                    <span>{{ __('dashboard.repeat_customers') }}: {{ $stats['repeat_customers'] }}</span>
                </div>
            </div>
            <!-- Rewards Redeemed -->
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('dashboard.rewards_redeemed') }}</div>
                <div class="stat-value">{{ $stats['rewards_redeemed'] }}</div>
            </div>
        </div>
    </div>

    <!-- ===== SECTION 2: KEY METRICS - ROW 1 ===== -->
    <div class="section-title" style="margin-top: 20px;">{{ __('reports.key_metrics') }}</div>
    <div class="stats-grid">
        <div class="stat-row">
            <!-- Net Profit -->
            <div class="stat-box">
                <div class="stat-label">{{ __('reports.net_profit') }}</div>
                <div class="stat-value {{ $stats['net_profit'] >= 0 ? 'text-green' : 'text-red' }}">
                    {{ $currency }} {{ number_format($stats['net_profit'], 2) }}
                </div>
                <div class="stat-sub">{{ __('reports.after_expenses_waste') }}</div>
            </div>
            <!-- Total Waste -->
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('reports.total_waste') }}</div>
                <div class="stat-value text-red">
                    {{ $currency }} {{ number_format($stats['total_waste'], 2) }}
                </div>
            </div>
            <!-- Inventory Value -->
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('reports.inventory_value') }}</div>
                <div class="stat-value">{{ $currency }} {{ number_format($stats['inventory_value'], 2) }}</div>
            </div>
            <!-- Active Staff -->
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('dashboard.active_staff') }}</div>
                <div class="stat-value">{{ $stats['active_staff'] }}</div>
            </div>
        </div>
    </div>

    <!-- ===== SECTION 2: KEY METRICS - ROW 2 ===== -->
    <div class="stats-grid" style="margin-top:-21px;">
        <div class="stat-row">
            <!-- Low Stock -->
            <div class="stat-box">
                <div class="stat-label">{{ __('dashboard.low_stock_items') }}</div>
                <div class="stat-value text-red">{{ $stats['low_stock_count'] }}</div>
                <div class="stat-sub">{{ __('reports.items_to_reorder') }}</div>
            </div>
            <!-- Avg Order Value -->
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('dashboard.avg_order_value') }}</div>
                <div class="stat-value">{{ $currency }}
                    {{ number_format(($stats['revenue'] / max(1, $stats['total_orders'])), 2) }}
                </div>
            </div>
            <!-- Monthly Expenses -->
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('reports.monthly_expenses') }}</div>
                <div class="stat-value">{{ $currency }} {{ number_format($stats['monthly_expenses'], 2) }}</div>
            </div>
            <!-- Generation Time -->
            <div class="stat-box" style="border-left:none;">
                <div class="stat-label">{{ __('reports.generated_at') }}</div>
                <div class="stat-value" style="font-size: 11px;">{{ now()->format('d M Y, H:i') }}</div>
            </div>
        </div>
    </div>

    <!-- ===== CHARTS ROW 1: Revenue Trend + Order Status ===== -->
    <div class="chart-section">
        <!-- Revenue Trend -->
        <div class="chart-col">
            <div class="section-title">{{ __('reports.revenue_trend') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('reports.date') }}</th>
                        <th style="text-align:right;">{{ __('reports.revenue') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($revenueChart as $day)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}</td>
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
                            <td colspan="2" class="no-data">{{ __('reports.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Order Status Distribution -->
        <div class="chart-col" style="padding-left: 2%; padding-right:0;">
            <div class="section-title">{{ __('reports.order_status_distribution') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('reports.status') }}</th>
                        <th style="text-align:right;">{{ __('reports.count') }}</th>
                        <th style="text-align:right;">%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statusDistribution as $status)
                        <tr>
                            <td>{{ ucfirst($status['status']) }}</td>
                            <td style="text-align:right;"><strong>{{ $status['count'] }}</strong></td>
                            <td style="text-align:right;">
                                @php
                                    $total = $statusDistribution->sum('count') ?: 1;
                                    $width = ($status['count'] / $total) * 100;
                                    $color = match ($status['status']) {
                                        'completed' => 'color-green',
                                        'cancelled' => 'color-red',
                                        default => 'color-blue'
                                    };
                                @endphp
                                {{ round($width, 1) }}%
                                <div class="bar-container">
                                    <div class="bar-fill {{ $color }}" style="width: {{ $width }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="no-data">{{ __('reports.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== CHARTS ROW 2: Peak Hours + Top Menu Items ===== -->
    <div class="chart-section">
        <!-- Peak Hours -->
        <div class="chart-col">
            <div class="section-title">{{ __('dashboard.peak_hours') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('reports.hour') }}</th>
                        <th style="text-align:right;">{{ __('dashboard.orders') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peakHours as $hour)
                        <tr>
                            <td>{{ sprintf('%02d:00 - %02d:59', $hour['hour'], $hour['hour']) }}</td>
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
                            <td colspan="2" class="no-data">{{ __('reports.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Top Menu Items -->
        <div class="chart-col" style="padding-left: 2%; padding-right:0;">
            <div class="section-title">{{ __('reports.top_menu_items') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('reports.item_name') }}</th>
                        <th style="text-align:right;">{{ __('reports.quantity_sold') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topMenuItems as $index => $item)
                        <tr>
                            <td><span class="rank-badge">{{ $index + 1 }}</span></td>
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
                            <td colspan="3" class="no-data">{{ __('reports.no_sales_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== CHARTS ROW 3: Avg Completion Time + Waste Trend ===== -->
    <div class="chart-section">
        <!-- Avg Completion Time -->
        <div class="chart-col">
            <div class="section-title">{{ __('reports.avg_completion_time') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('reports.date') }}</th>
                        <th style="text-align:right;">{{ __('reports.avg_minutes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($avgCompletionTime as $day)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}</td>
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
                            <td colspan="2" class="no-data">{{ __('reports.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Waste Trend -->
        <div class="chart-col" style="padding-left: 2%; padding-right:0;">
            <div class="section-title">{{ __('reports.waste_trend') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('reports.date') }}</th>
                        <th style="text-align:right;">{{ __('reports.loss') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wasteChart as $day)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}</td>
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
                            <td colspan="2" class="no-data">{{ __('reports.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== CHARTS ROW 4: Top Categories + Top Customers ===== -->
    <div class="chart-section">
        <!-- Top Categories -->
        <div class="chart-col">
            <div class="section-title">{{ __('reports.top_categories') }} ({{ __('reports.revenue') }})</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('reports.category') }}</th>
                        <th style="text-align:right;">{{ __('reports.sales_value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCategories as $index => $cat)
                        <tr>
                            <td><span class="rank-badge">{{ $index + 1 }}</span></td>
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
                            <td colspan="3" class="no-data">{{ __('reports.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Top Customers -->
        <div class="chart-col" style="padding-left: 2%; padding-right:0;">
            <div class="section-title">{{ __('reports.top_customers') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('reports.customer_name') }}</th>
                        <th style="text-align:right;">{{ __('reports.order') }}</th>
                        <th style="text-align:right;">{{ __('reports.revenue') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCustomers as $index => $customer)
                        <tr>
                            <td><span class="rank-badge">{{ $index + 1 }}</span></td>
                            <td>{{ $customer['name'] ?: __('reports.guest') }}</td>
                            <td style="text-align:right;">{{ $customer['count'] }}</td>
                            <td style="text-align:right;">
                                {{ $currency }} {{ number_format($customer['total'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="no-data">{{ __('reports.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== CUSTOMER RETENTION ===== -->
    <div class="section">
        <div class="section-title">{{ __('reports.customer_retention') }}</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('reports.milestone') }}</th>
                    <th style="text-align:right;">{{ __('reports.count') }}</th>
                    <th style="text-align:right;">{{ __('reports.percentage') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($retentionStats as $stat)
                    <tr>
                        <td>{{ $stat['label'] }}</td>
                        <td style="text-align:right;">{{ $stat['count'] }} {{ __('reports.customers_label') }}</td>
                        <td style="text-align:right;">
                            {{ $stat['percentage'] }}%
                            <div class="bar-container">
                                <div class="bar-fill color-green" style="width: {{ $stat['percentage'] }}%"></div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="no-data">{{ __('reports.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        Generated by <strong>Servio</strong> &mdash; {{ now()->format('d M Y, H:i:s') }}
    </div>
</body>

</html>