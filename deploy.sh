#!/bin/bash

# ─────────────────────────────────────────────────────────────────────────────
# Servio Production Deploy Script — Bulletproof Edition
# Fixes: chicken-and-egg cache issue, silent failures, permission problems
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

# ── Step 2: Kill bootstrap cache FIRST (before ANYTHING else) ────────────────
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
    echo "   ❌ FATAL: git pull failed. Aborting deployment."
    exit 1
fi
echo "   ✅ Code updated"

# ── Step 4: Install PHP dependencies (no dev packages) ───────────────────────
echo ""
echo "➡️  [4/9] Installing PHP dependencies (no-dev)..."
if ! composer install --no-dev --optimize-autoloader; then
    echo "   ❌ FATAL: composer install failed. Aborting deployment."
    exit 1
fi
echo "   ✅ PHP dependencies installed"

# ── Step 5: Force nuke cache AGAIN (composer may have regenerated with stale data)
echo ""
echo "➡️  [5/9] Re-nuking bootstrap cache after composer..."
rm -f $DEPLOY_DIR/bootstrap/cache/services.php
rm -f $DEPLOY_DIR/bootstrap/cache/packages.php
echo "   ✅ Cache cleared again"

# ── Step 6: Re-discover packages cleanly ──────────────────────────────────────
# This respects the "dont-discover" list in composer.json, ensuring dev-only
# packages like nunomaduro/collision are NEVER registered in production.
echo ""
echo "➡️  [6/9] Discovering packages (respecting dont-discover list)..."
php artisan package:discover --ansi
echo "   ✅ Packages discovered"

# ── Step 7: Build frontend assets ─────────────────────────────────────────────
echo ""
echo "➡️  [7/9] Building frontend assets..."
npm install
npm run build
echo "   ✅ Frontend built"

# ── Step 8: Laravel cache rebuild ─────────────────────────────────────────────
echo ""
echo "➡️  [8/9] Rebuilding Laravel caches..."
php artisan optimize:clear
php artisan view:cache
echo "   ✅ Caches rebuilt"

# ── Step 9: Restore web server ownership and restart PHP ──────────────────────
echo ""
echo "➡️  [9/9] Restoring permissions and restarting PHP..."
sudo chown -R www-data:www-data $DEPLOY_DIR
sudo chmod -R 775 $DEPLOY_DIR/storage
sudo chmod -R 775 $DEPLOY_DIR/bootstrap/cache
sudo systemctl restart php8.2-fpm
echo "   ✅ Permissions restored, PHP restarted"

echo ""
echo "========================================"
echo "  ✅ Deployment complete!"
echo "========================================"
echo ""
