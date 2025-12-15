#!/bin/bash

# RestoFy Code Cleanup Script
# This script cleans up temporary files and organizes the project structure

echo "🧹 Starting RestoFy Code Cleanup..."
echo ""

# Create directories for organization
echo "📁 Creating organizational directories..."
mkdir -p dev-tools
mkdir -p docs

# Move debug/test scripts to dev-tools
echo "🔧 Moving debug scripts to dev-tools/..."
[ -f reset_mhala.php ] && mv reset_mhala.php dev-tools/
[ -f fix_sidebar.sh ] && mv fix_sidebar.sh dev-tools/
[ -f audit_data_consistency.php ] && mv audit_data_consistency.php dev-tools/
[ -f check_user_hash.php ] && mv check_user_hash.php dev-tools/
[ -f nuke_and_reset.php ] && mv nuke_and_reset.php dev-tools/
[ -f run_tenant_migration.php ] && mv run_tenant_migration.php dev-tools/
[ -f simulate_login.php ] && mv simulate_login.php dev-tools/
[ -f test_auth.js ] && mv test_auth.js dev-tools/
[ -f migrations_sync.sql ] && mv migrations_sync.sql dev-tools/
[ -f missing_tables.sql ] && mv missing_tables.sql dev-tools/

# Move documentation to docs
echo "📚 Moving documentation to docs/..."
[ -f DATA_CONSISTENCY.md ] && mv DATA_CONSISTENCY.md docs/
[ -f DATA_CONSISTENCY_AUDIT.md ] && mv DATA_CONSISTENCY_AUDIT.md docs/
[ -f DATA_CONSISTENCY_FIXES_SUMMARY.md ] && mv DATA_CONSISTENCY_FIXES_SUMMARY.md docs/
[ -f DATA_CONSISTENCY_FIX_SUMMARY.md ] && mv DATA_CONSISTENCY_FIX_SUMMARY.md docs/
[ -f COMPREHENSIVE_DATA_AUDIT.md ] && mv COMPREHENSIVE_DATA_AUDIT.md docs/
[ -f LOGIN_TEST_RESULTS.md ] && mv LOGIN_TEST_RESULTS.md docs/
[ -f DELIVERY_PROVIDERS_FEATURE.md ] && mv DELIVERY_PROVIDERS_FEATURE.md docs/
[ -f LOGO_COLORS.md ] && mv LOGO_COLORS.md docs/

# Remove temporary files
echo "🗑️  Removing temporary files..."
[ -f "AED," ] && rm -f "AED,"
[ -f "ahmadtest," ] && rm -f "ahmadtest,"
[ -f "en," ] && rm -f "en,"
[ -f "Deprecated" ] && rm -f "Deprecated"

# Create .gitignore entries for dev-tools if not exists
echo "📝 Updating .gitignore..."
if ! grep -q "dev-tools/" .gitignore 2>/dev/null; then
    echo "" >> .gitignore
    echo "# Development tools (not for production)" >> .gitignore
    echo "dev-tools/" >> .gitignore
fi

# Create README for dev-tools
echo "📄 Creating dev-tools README..."
cat > dev-tools/README.md << 'EOF'
# Development Tools

This directory contains scripts and tools used during development and debugging.

**⚠️ WARNING:** These scripts should NOT be deployed to production.

## Scripts

- `reset_mhala.php` - Database reset script
- `audit_data_consistency.php` - Data consistency checker
- `check_user_hash.php` - User password hash checker
- `nuke_and_reset.php` - Complete database reset
- `run_tenant_migration.php` - Tenant migration runner
- `simulate_login.php` - Login simulation for testing
- `test_auth.js` - Authentication testing script

## SQL Files

- `migrations_sync.sql` - Migration synchronization
- `missing_tables.sql` - Missing table definitions

## Usage

These scripts are for development purposes only. Use with caution.
EOF

# Create consolidated development notes
echo "📋 Creating consolidated development notes..."
cat > docs/DEVELOPMENT_NOTES.md << 'EOF'
# Development Notes

This directory contains historical documentation and audit reports from the development process.

## Contents

- **Data Consistency Reports** - Various audits and fixes applied to ensure data integrity
- **Login Test Results** - Authentication testing documentation
- **Feature Documentation** - Implementation notes for specific features
- **Logo & Branding** - Color schemes and branding guidelines

## Note

These documents are kept for reference but may not reflect the current state of the application.
For current documentation, see the main README.md and SETUP_GUIDE.md files.
EOF

echo ""
echo "✅ Cleanup completed successfully!"
echo ""
echo "📊 Summary:"
echo "  - Moved debug scripts to dev-tools/"
echo "  - Moved documentation to docs/"
echo "  - Removed temporary files"
echo "  - Updated .gitignore"
echo "  - Created organizational READMEs"
echo ""
echo "🎯 Next steps:"
echo "  1. Review the changes"
echo "  2. Test the application"
echo "  3. Commit with: git add . && git commit -m 'chore: code cleanup and organization'"
echo ""
