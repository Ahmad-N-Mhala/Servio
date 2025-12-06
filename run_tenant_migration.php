<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find the tenant by domain
$domain = 'ahmadtest.localhost';
$tenant = Tenant::whereHas('domains', function ($q) use ($domain) {
    $q->where('domain', $domain);
})->first();

if (!$tenant) {
    echo "Tenant not found for domain: $domain\n";
    exit(1);
}

echo "Found tenant: " . $tenant->id . "\n";

echo "Initializing tenant...\n";
tenancy()->initialize($tenant);

// Ensure tenant connection is configured
config(['database.connections.tenant.database' => $tenant->id]);
\Illuminate\Support\Facades\DB::purge('tenant');
\Illuminate\Support\Facades\DB::reconnect('tenant');

echo "Running migrations...\n";
Artisan::call('migrate', [
    '--path' => 'database/migrations/tenant',
    '--database' => 'tenant',
    '--force' => true,
]);

echo Artisan::output();
echo "Done.\n";
