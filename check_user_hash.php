<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenantId = 'ahmadtest';
$tenant = Tenant::find($tenantId);

if (!$tenant) {
    die("Tenant not found.");
}

tenancy()->initialize($tenant);

$email = 'manager@mhala.com';
$password = 'password123';

$user = User::where('email', $email)->first();

if (!$user) {
    echo "User NOT found via Eloquent.\n";
    // Check DB directly
    $dbUser = \Illuminate\Support\Facades\DB::table('users')->where('email', $email)->first();
    if ($dbUser) {
        echo "User found in DB table directly. ID: " . $dbUser->id . "\n";
    } else {
        echo "User not found in DB table either.\n";
    }
} else {
    echo "User found: " . $user->name . " (ID: " . $user->id . ")\n";
    echo "Stored Password Hash: " . $user->password . "\n";

    $check = Hash::check($password, $user->password);
    echo "Hash::check('password123', stored_hash): " . ($check ? 'TRUE' : 'FALSE') . "\n";

    if (!$check) {
        echo "Trying to manually update password to simple bcrypt...\n";
        // Directly update for testing
        $newHash = bcrypt($password);
        $user->password = $newHash;
        $user->save(); // This might double hash if cast is on!

        echo "Updated via model save(). Verifying...\n";
        $user->refresh();
        echo "New Hash: " . $user->password . "\n";
        echo "Check: " . (Hash::check($password, $user->password) ? 'TRUE' : 'FALSE') . "\n";

        // Force update via DB to avoid model casting
        DB::table('users')->where('id', $user->id)->update(['password' => bcrypt($password)]);
        echo "Forced update via DB builder. Verifying...\n";
        $user->refresh();
        echo "New Hash (DB set): " . $user->password . "\n";
        echo "Check: " . (Hash::check($password, $user->password) ? 'TRUE' : 'FALSE') . "\n";
    }
}
