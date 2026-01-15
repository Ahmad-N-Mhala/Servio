<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Models\SystemConfiguration;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "\n--- Email Configuration Diagnostics ---\n";

// 1. Check DB Overrides
echo "Checking SystemConfiguration DB overrides...\n";
try {
    $dbConfig = SystemConfiguration::all()->pluck('value', 'key');
    echo "DB 'mail_host': " . ($dbConfig['mail_host'] ?? 'NOT SET') . "\n";
    echo "DB 'mail_username': " . ($dbConfig['mail_username'] ?? 'NOT SET') . "\n";
    // echo "DB 'mail_password': " . ($dbConfig['mail_password'] ? '******' : 'NOT SET') . "\n";
} catch (\Exception $e) {
    echo "Error reading DB: " . $e->getMessage() . "\n";
}

// 2. Check Final Config (what Laravel uses)
echo "\nChecking Active Laravel Config (Runtime):\n";
echo "Transport: " . Config::get('mail.mailers.smtp.transport') . "\n";
echo "Host: " . Config::get('mail.mailers.smtp.host') . "\n";
echo "Port: " . Config::get('mail.mailers.smtp.port') . "\n";
echo "Username: " . Config::get('mail.mailers.smtp.username') . "\n";
echo "Encryption: " . Config::get('mail.mailers.smtp.encryption') . "\n";
echo "From Address: " . Config::get('mail.from.address') . "\n";
echo "From Name: " . Config::get('mail.from.name') . "\n";

// 3. Test Send
echo "\n--- Attempting to Send Test Email ---\n";
$to = 'support@kenildock.com';
echo "Sending to: $to ...\n";

try {
    Mail::raw("This is a diagnostic test email from the Servio Backend.\nTimestamp: " . date('Y-m-d H:i:s'), function ($message) use ($to) {
        $message->to($to)
            ->subject('Servio Backend Email Test ' . time());
    });
    echo "SUCCESS: Email accepted by the mail server for delivery.\n";
} catch (\Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n--- End Diagnostics ---\n";
