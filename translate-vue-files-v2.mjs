#!/usr/bin/env node

/**
 * Automated Translation Replacement Script v2
 * 
 * More aggressive replacement for Vue files.
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const replacements = [
    // Page: Kitchen
    [/>Kitchen Display</g, ">{{ $t('kitchen.title') }}<"],
    [/>Manage active orders</g, ">{{ $t('kitchen.subtitle') }}<"],
    [/placeholder="Search orders\.\.\."/g, ':placeholder="$t(\'kitchen.search_orders\')\"'],
    [/>Auto-refresh active</g, ">{{ $t('kitchen.auto_refresh') }}<"],
    [/>No pending orders</g, ">{{ $t('kitchen.no_pending') }}<"],
    [/>No orders in progress</g, ">{{ $t('kitchen.no_processing') }}<"],
    [/>No completed orders</g, ">{{ $t('kitchen.no_completed') }}<"],
    [/>Start Cooking</g, ">{{ $t('kitchen.start_cooking') }}<"],
    [/>Order Ready</g, ">{{ $t('kitchen.order_ready') }}<"],
    [/>Cancel Order</g, ">{{ $t('kitchen.cancel_order') }}<"],
    [/>Ready for Pickup</g, ">{{ $t('kitchen.ready_for_pickup') }}<"],
    [/>Served</g, ">{{ $t('kitchen.served') }}<"],
    [/>Recently Completed</g, ">{{ $t('kitchen.recently_completed') }}<"],
    [/>Ready \/ Served</g, ">{{ $t('kitchen.ready_served') }}<"],
    [/>min</g, ">{{ $t('kitchen.min') }}<"],
    [/>Cancel Order #/g, ">{{ $t('kitchen.cancel_title') }} #"],
    [/>Please provide a reason for cancelling this order so the waiter can inform the customer\.</g, ">{{ $t('kitchen.cancel_reason_prompt') }}<"],
    [/>Reason for Cancellation</g, ">{{ $t('kitchen.reason_for_cancellation') }}<"],
    [/placeholder="e\.g\. Out of stock, Customer request\.\.\."/g, ':placeholder="$t(\'kitchen.reason_placeholder\')\"'],
    [/>Keep Order</g, ">{{ $t('kitchen.keep_order') }}<"],
    [/>Confirm Cancellation</g, ">{{ $t('kitchen.confirm_cancellation') }}<"],

    // Page: Dashboard
    [/>Welcome back</g, ">{{ $t('dashboard_page.welcome') }}<"],
    [/>Analytics overview/g, ">{{ $t('reports.subtitle') }}"], // Reusing reports subtitle
    [/>Net Profit</g, ">{{ $t('dashboard_page.net_profit') }}<"],
    [/>Low Stock/g, ">{{ $t('dashboard_page.low_stock') }}"],
    [/>Avg Dining Time/g, ">{{ $t('dashboard_page.avg_dining_time') }}"],
    [/>Revenue Trend/g, ">{{ $t('charts.revenue_trend') }}"],
    [/>Order Status/g, ">{{ $t('charts.order_status') }}"],
    [/>Payment Methods/g, ">{{ $t('charts.payment_methods') }}"],
    [/>Top Menu Items/g, ">{{ $t('charts.top_menu_items') }}"],
    [/>Peak Hours/g, ">{{ $t('charts.peak_hours') }}"],
    [/>Waste Trend \(Money\)/g, ">{{ $t('charts.waste_trend') }}"],
    [/>Top Categories \(Sales\)/g, ">{{ $t('charts.top_categories') }}"],
    [/>Top Customers/g, ">{{ $t('charts.top_customers') }}"],
    [/>Customer Retention \(Visit Funnel\)/g, ">{{ $t('charts.customer_retention') }}"],
    [/>No data available/g, ">{{ $t('charts.no_data') }}"],

    // Common
    [/>Save</g, ">{{ $t('common.save') }}<"],
    [/>Cancel</g, ">{{ $t('common.cancel') }}<"],
    [/>Delete</g, ">{{ $t('common.delete') }}<"],
    [/>Edit</g, ">{{ $t('common.edit') }}<"],
    [/>Create</g, ">{{ $t('common.create') }}<"],
    [/>Update</g, ">{{ $t('common.update') }}<"],
    [/>Close</g, ">{{ $t('common.close') }}<"],
    [/>Submit</g, ">{{ $t('common.submit') }}<"],
    [/>Add</g, ">{{ $t('common.add') }}<"],
    [/>View</g, ">{{ $t('common.view') }}<"],
    [/>Export</g, ">{{ $t('common.export') }}<"],
    [/>Search/g, ">{{ $t('common.search') }}"],
    [/>Filter/g, ">{{ $t('common.filter') }}"],
    [/>Actions/g, ">{{ $t('common.actions') }}"],
    [/>Status/g, ">{{ $t('common.status') }}"],
    [/>Date/g, ">{{ $t('common.date') }}"],
    [/>Total/g, ">{{ $t('common.total') }}"],
    [/>Quantity/g, ">{{ $t('common.quantity') }}"],
    [/>Active</g, ">{{ $t('common.active') }}<"],
    [/>Inactive</g, ">{{ $t('common.inactive') }}<"],
    [/>Dine In</g, ">{{ $t('kitchen.dine_in') }}<"],
    [/>Takeaway</g, ">{{ $t('kitchen.takeaway') }}<"],
    [/>Guest</g, ">{{ $t('common.guest') }}<"],
    [/>Items</g, ">{{ $t('common.items') }}<"],

    // Dynamic names replacement (be careful)
    [/\.name\.en/g, ".name[$i18n.locale]"],
    [/\['en'\]/g, "[$i18n.locale]"],
];

const pagesDir = path.join(__dirname, 'resources', 'js', 'Pages');

function processFile(filePath) {
    try {
        let content = fs.readFileSync(filePath, 'utf8');
        let originalContent = content;

        replacements.forEach(([pattern, replacement]) => {
            content = content.replace(pattern, replacement);
        });

        if (content !== originalContent) {
            fs.writeFileSync(filePath, content, 'utf8');
            console.log(`✅ Updated: ${path.relative(process.cwd(), filePath)}`);
        }
    } catch (error) {
        console.error(`❌ Error processing ${filePath}:`, error.message);
    }
}

function processDirectory(dir) {
    const items = fs.readdirSync(dir);
    items.forEach(item => {
        const fullPath = path.join(dir, item);
        const stat = fs.statSync(fullPath);
        if (stat.isDirectory()) {
            processDirectory(fullPath);
        } else if (item.endsWith('.vue')) {
            processFile(fullPath);
        }
    });
}

console.log('🚀 Starting aggressive translation replacement...\n');
processDirectory(pagesDir);
console.log('\n✅ Aggressive replacement complete!');
