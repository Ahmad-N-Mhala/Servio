#!/usr/bin/env node

/**
 * Automated Translation Replacement Script
 * 
 * This script automatically replaces hardcoded English text in Vue files
 * with translation keys from the i18n system.
 * 
 * Usage: node translate-vue-files.js
 */

const fs = require('fs');
const path = require('path');

// Define replacement patterns
// Format: [regex pattern, replacement, description]
const replacements = [
    // Common buttons and actions
    [/>\s*Save\s*</g, ">{{ $t('common.save') }}<", 'Save button'],
    [/>\s*Cancel\s*</g, ">{{ $t('common.cancel') }}<", 'Cancel button'],
    [/>\s*Delete\s*</g, ">{{ $t('common.delete') }}<", 'Delete button'],
    [/>\s*Edit\s*</g, ">{{ $t('common.edit') }}<", 'Edit button'],
    [/>\s*Create\s*</g, ">{{ $t('common.create') }}<", 'Create button'],
    [/>\s*Update\s*</g, ">{{ $t('common.update') }}<", 'Update button'],
    [/>\s*Close\s*</g, ">{{ $t('common.close') }}<", 'Close button'],
    [/>\s*Submit\s*</g, ">{{ $t('common.submit') }}<", 'Submit button'],
    [/>\s*Add\s*</g, ">{{ $t('common.add') }}<", 'Add button'],
    [/>\s*Remove\s*</g, ">{{ $t('common.remove') }}<", 'Remove button'],
    [/>\s*View\s*</g, ">{{ $t('common.view') }}<", 'View button'],
    [/>\s*Export\s*</g, ">{{ $t('common.export') }}<", 'Export button'],
    [/>\s*Import\s*</g, ">{{ $t('common.import') }}<", 'Import button'],
    [/>\s*Search\s*</g, ">{{ $t('common.search') }}<", 'Search button'],
    [/>\s*Filter\s*</g, ">{{ $t('common.filter') }}<", 'Filter button'],
    [/>\s*Reset\s*</g, ">{{ $t('common.reset') }}<", 'Reset button'],
    [/>\s*Back\s*</g, ">{{ $t('common.back') }}<", 'Back button'],
    [/>\s*Next\s*</g, ">{{ $t('common.next') }}<", 'Next button'],
    [/>\s*Confirm\s*</g, ">{{ $t('common.confirm') }}<", 'Confirm button'],

    // Common labels
    [/>\s*Name\s*</g, ">{{ $t('common.name') }}<", 'Name label'],
    [/>\s*Description\s*</g, ">{{ $t('common.description') }}<", 'Description label'],
    [/>\s*Price\s*</g, ">{{ $t('common.price') }}<", 'Price label'],
    [/>\s*Status\s*</g, ">{{ $t('common.status') }}<", 'Status label'],
    [/>\s*Actions\s*</g, ">{{ $t('common.actions') }}<", 'Actions label'],
    [/>\s*Date\s*</g, ">{{ $t('common.date') }}<", 'Date label'],
    [/>\s*Time\s*</g, ">{{ $t('common.time') }}<", 'Time label'],
    [/>\s*Total\s*</g, ">{{ $t('common.total') }}<", 'Total label'],
    [/>\s*Quantity\s*</g, ">{{ $t('common.quantity') }}<", 'Quantity label'],

    // Common status
    [/>\s*Active\s*</g, ">{{ $t('common.active') }}<", 'Active status'],
    [/>\s*Inactive\s*</g, ">{{ $t('common.inactive') }}<", 'Inactive status'],
    [/>\s*Loading\.\.\.\s*</g, ">{{ $t('common.loading') }}<", 'Loading text'],
    [/>\s*No data available\s*</g, ">{{ $t('charts.no_data') }}<", 'No data text'],

    // Placeholders (be careful with these)
    [/placeholder="Search\.\.\."/, 'placeholder="Search..."', 'Search placeholder (skip)'],
    [/placeholder="Search orders\.\.\."/, ':placeholder="$t(\'kitchen.search_orders\')"', 'Search orders placeholder'],

    // Chart titles
    [/title="Revenue Trend"/, ':title="$t(\'charts.revenue_trend\')"', 'Revenue Trend chart'],
    [/title="Order Status"/, ':title="$t(\'charts.order_status\')"', 'Order Status chart'],
    [/title="Payment Methods"/, ':title="$t(\'charts.payment_methods\')"', 'Payment Methods chart'],
    [/title="Top Menu Items"/, ':title="$t(\'charts.top_menu_items\')"', 'Top Menu Items chart'],
    [/title="Peak Hours"/, ':title="$t(\'charts.peak_hours\')"', 'Peak Hours chart'],
    [/title="Waste Trend \(Money\)"/, ':title="$t(\'charts.waste_trend\')"', 'Waste Trend chart'],
    [/title="Top Categories \(Sales\)"/, ':title="$t(\'charts.top_categories\')"', 'Top Categories chart'],
    [/title="Top Customers"/, ':title="$t(\'charts.top_customers\')"', 'Top Customers chart'],
    [/title="Customer Retention \(Visit Funnel\)"/, ':title="$t(\'charts.customer_retention\')"', 'Customer Retention chart'],

    // Kitchen specific
    [/>\s*Pending\s*</g, ">{{ $t('kitchen.pending') }}<", 'Pending status'],
    [/>\s*Processing\s*</g, ">{{ $t('kitchen.processing') }}<", 'Processing status'],
    [/>\s*Dine In\s*</g, ">{{ $t('kitchen.dine_in') }}<", 'Dine In'],
    [/>\s*Takeaway\s*</g, ">{{ $t('kitchen.takeaway') }}<", 'Takeaway'],
    [/>\s*Notes\s*</g, ">{{ $t('kitchen.notes') }}<", 'Notes'],
    [/>\s*Items\s*</g, ">{{ $t('kitchen.items') }}<", 'Items'],

    // Dashboard specific
    [/>\s*Export Excel\s*</g, ">{{ $t('common.export') }} Excel<", 'Export Excel'],
    [/>\s*Export PDF\s*</g, ">{{ $t('common.export') }} PDF<", 'Export PDF'],
    [/>\s*Net Profit\s*</g, ">{{ $t('dashboard_page.net_profit') }}<", 'Net Profit'],
    [/>\s*Low Stock\s*</g, ">{{ $t('dashboard_page.low_stock') }}<", 'Low Stock'],
    [/>\s*Avg Dining Time\s*</g, ">{{ $t('dashboard_page.avg_dining_time') }}<", 'Avg Dining Time'],
];

// Files to process
const pagesDir = path.join(__dirname, 'resources', 'js', 'Pages');

// Statistics
let filesProcessed = 0;
let replacementsMade = 0;
const changedFiles = [];

/**
 * Process a single Vue file
 */
function processFile(filePath) {
    try {
        let content = fs.readFileSync(filePath, 'utf8');
        let originalContent = content;
        let fileReplacements = 0;

        // Apply each replacement pattern
        replacements.forEach(([pattern, replacement, description]) => {
            const matches = content.match(pattern);
            if (matches) {
                content = content.replace(pattern, replacement);
                fileReplacements += matches.length;
                console.log(`  ✓ ${description}: ${matches.length} replacement(s)`);
            }
        });

        // Only write if changes were made
        if (content !== originalContent) {
            fs.writeFileSync(filePath, content, 'utf8');
            filesProcessed++;
            replacementsMade += fileReplacements;
            changedFiles.push({
                file: path.relative(process.cwd(), filePath),
                replacements: fileReplacements
            });
            console.log(`✅ Updated: ${path.relative(process.cwd(), filePath)} (${fileReplacements} replacements)\n`);
        }

    } catch (error) {
        console.error(`❌ Error processing ${filePath}:`, error.message);
    }
}

/**
 * Recursively process all Vue files in a directory
 */
function processDirectory(dir) {
    const items = fs.readdirSync(dir);

    items.forEach(item => {
        const fullPath = path.join(dir, item);
        const stat = fs.statSync(fullPath);

        if (stat.isDirectory()) {
            processDirectory(fullPath);
        } else if (item.endsWith('.vue')) {
            console.log(`\n📄 Processing: ${path.relative(process.cwd(), fullPath)}`);
            processFile(fullPath);
        }
    });
}

/**
 * Main execution
 */
console.log('🚀 Starting automated translation replacement...\n');
console.log('📁 Scanning directory:', pagesDir);
console.log('='.repeat(80));

processDirectory(pagesDir);

console.log('\n' + '='.repeat(80));
console.log('\n📊 SUMMARY:');
console.log(`   Files processed: ${filesProcessed}`);
console.log(`   Total replacements: ${replacementsMade}`);
console.log('\n📝 Changed files:');
changedFiles.forEach(({ file, replacements }) => {
    console.log(`   - ${file} (${replacements} changes)`);
});

console.log('\n✅ Translation replacement complete!');
console.log('\n⚠️  IMPORTANT: Review the changes and test thoroughly.');
console.log('   Some replacements may need manual adjustment.');
console.log('\n💡 Next steps:');
console.log('   1. Review git diff to see all changes');
console.log('   2. Test the application in both English and Arabic');
console.log('   3. Manually fix any incorrect replacements');
console.log('   4. Add any missing translation keys to locale files');
