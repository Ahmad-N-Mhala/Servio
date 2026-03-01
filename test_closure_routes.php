<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = app('router')->getRoutes();
$closures = [];
foreach ($routes as $route) {
    if (is_string($route->getAction('uses')) && str_contains($route->getAction('uses'), 'Closure')) {
        $closures[] = $route->uri();
    } elseif ($route->getAction('uses') instanceof \Closure) {
        $closures[] = $route->uri();
    }
}
echo "Closure Routes: " . implode(', ', $closures) . "\n";
