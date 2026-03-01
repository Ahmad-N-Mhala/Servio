<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Artisan Booted Successfully\n";
    $app->make('router')->getRoutes();
    echo "Routes loaded successfully\n";
} catch (\Exception $e) {
    echo "Boot Error: " . $e->getMessage() . "\n";
}
