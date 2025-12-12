<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "💥 NUKING DATABASES...\n";

$dbs = [
    'restaurfy_central',
    'ahmadtest',
    'restaurfy_tenant_ahmadtest',
    'restaurfy_tenant_demo',
    'demo'
];

foreach ($dbs as $dbName) {
    try {
        DB::connection('pgsql')->statement("DROP DATABASE IF EXISTS \"$dbName\" WITH (FORCE)");
        echo "Dropping $dbName... ✅\n";
    } catch (\Exception $e) {
        echo "Failed to drop $dbName: " . $e->getMessage() . "\n";
    }
}

echo "\n✨ Databases wiped. Now re-running migration and setup...\n";

try {
    DB::connection('pgsql')->statement("CREATE DATABASE \"restaurfy_central\"");
    echo "Creating restaurfy_central... ✅\n";
} catch (\Exception $e) {
    echo "Failed to create central DB: " . $e->getMessage() . "\n";
}

echo "Please run: \n";
echo "1. php artisan migrate (for central)\n";
echo "2. php reset_mhala.php (to setup tenant)\n";
