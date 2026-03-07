#!/bin/bash

# ─────────────────────────────────────────────────────────────────────────────
# Servio Production Deploy Script — Bulletproof Edition v2
# ROOT FIX: --no-scripts on composer install prevents auto-running
# "php artisan package:discover" which was regenerating broken packages.php
# ─────────────────────────────────────────────────────────────────────────────

# NOTE: We deliberately do NOT use "set -e" here.
# "set -e" caused the script to silently abort on minor failures (e.g.
# a failing artisan command), leaving the server in a broken half-deployed
# state. Instead, we handle critical steps manually.

DEPLOY_DIR="/var/www/servio"

echo ""
echo "========================================"
echo "  🚀 Starting Servio Deployment..."
echo "========================================"
echo ""

# ── Step 1: Ownership ────────────────────────────────────────────────────────
echo "➡️  [1/9] Taking ownership of files..."
sudo chown -R $USER:$USER $DEPLOY_DIR
echo "   ✅ Done"

# ── Step 2: Kill ALL bootstrap cache FIRST ───────────────────────────────────
# This MUST happen before git pull or artisan runs, so we never hit
# the chicken-and-egg problem where artisan fails due to a stale cache.
echo ""
echo "➡️  [2/9] Nuking stale bootstrap cache..."
rm -f $DEPLOY_DIR/bootstrap/cache/services.php
rm -f $DEPLOY_DIR/bootstrap/cache/packages.php
rm -f $DEPLOY_DIR/bootstrap/cache/config.php
rm -f $DEPLOY_DIR/bootstrap/cache/routes-v7.php
echo "   ✅ Cache files deleted"

# ── Step 3: Pull latest code ─────────────────────────────────────────────────
echo ""
echo "➡️  [3/9] Pulling latest code from GitHub..."
cd $DEPLOY_DIR

git reset --hard HEAD
if ! git pull origin main; then
    echo "   ❌ FATAL: git pull failed. Aborting."
    exit 1
fi
echo "   ✅ Code updated"

# ── Step 4: Install PHP dependencies ─────────────────────────────────────────
# KEY FIX: --no-scripts prevents composer from auto-running
# "php artisan package:discover" via post-autoload-dump.
# That command was regenerating packages.php WITH CollisionServiceProvider
# (a dev-only class not installed in prod) — causing the 500 error.
# We run our OWN controlled package discovery in step 6 instead.
echo ""
echo "➡️  [4/9] Installing PHP dependencies (no-dev, no-scripts)..."
if ! composer install --no-dev --optimize-autoloader --no-scripts; then
    echo "   ❌ FATAL: composer install failed. Aborting."
    exit 1
fi
echo "   ✅ PHP dependencies installed"

# ── Step 5: Nuke cache again (safety — ensure nothing snuck back in) ─────────
echo ""
echo "➡️  [5/9] Re-nuking cache after composer..."
rm -f $DEPLOY_DIR/bootstrap/cache/services.php
rm -f $DEPLOY_DIR/bootstrap/cache/packages.php
echo "   ✅ Cache cleared"

# ── Step 6: Discover packages cleanly ────────────────────────────────────────
# Reads dont-discover from composer.json — collision is excluded.
echo ""
echo "➡️  [6/9] Running package discovery (with dont-discover list)..."
if ! php artisan package:discover --ansi; then
    echo "   ❌ FATAL: package:discover failed. Aborting."
    exit 1
fi
# Safety check: abort if collision somehow snuck into packages.php
if grep -q "CollisionServiceProvider" $DEPLOY_DIR/bootstrap/cache/packages.php 2>/dev/null; then
    echo "   ❌ FATAL: CollisionServiceProvider found in packages.php!"
    echo "   Deleting packages.php and aborting to prevent 500 error."
    rm -f $DEPLOY_DIR/bootstrap/cache/packages.php
    exit 1
fi
echo "   ✅ Packages discovered cleanly — no dev-only providers"

# ── Step 7: Build frontend assets ────────────────────────────────────────────
echo ""
echo "➡️  [7/9] Building frontend assets..."
npm install
npm run build
echo "   ✅ Frontend built"

# ── Step 8: Rebuild Laravel caches ───────────────────────────────────────────
echo ""
echo "➡️  [8/9] Rebuilding Laravel caches..."
php artisan optimize:clear
php artisan view:cache
echo "   ✅ Caches rebuilt"

# ── Step 9: Restore permissions and restart PHP ───────────────────────────────
echo ""
echo "➡️  [9/9] Restoring permissions and restarting PHP..."
sudo chown -R www-data:www-data $DEPLOY_DIR
sudo chmod -R 775 $DEPLOY_DIR/storage
sudo chmod -R 775 $DEPLOY_DIR/bootstrap/cache
sudo systemctl restart php8.2-fpm
echo "   ✅ Done"

echo ""
echo "========================================"
echo "  ✅ Deployment complete!"
echo "========================================"
echo ""
