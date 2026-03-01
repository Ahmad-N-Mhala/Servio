<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $routes = app('router')->getRoutes();
    echo "Total Routes: " . count($routes) . "\n";
} catch (\Exception $e) {
    echo "Route Error: " . $e->getMessage() . "\n";
}
