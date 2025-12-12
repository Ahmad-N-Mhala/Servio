<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = Tenant::find('ahmadtest');
tenancy()->initialize($tenant);

$credentials = [
    'email' => 'manager@mhala.com',
    'password' => 'password123',
];

echo "Attempting login for ahmad@test.com...\n";

if (Auth::attempt($credentials)) {
    echo "✅ LOGIN SUCCESSFUL! Auth::attempt() returned true.\n";
    echo "User ID: " . Auth::id() . "\n";
} else {
    echo "❌ LOGIN FAILED. Auth::attempt() returned false.\n";
}
