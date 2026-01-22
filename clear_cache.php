<?php

use Illuminate\Support\Facades\Artisan;

require __DIR__ . '/bootstrap/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// Clear application cache
Artisan::call('cache:clear');
echo "Application cache cleared.\n";

// Clear configuration cache
Artisan::call('config:clear');
echo "Configuration cache cleared.\n";

// Clear route cache
Artisan::call('route:clear');
echo "Route cache cleared.\n";

// Clear view cache
Artisan::call('view:clear');
echo "View cache cleared.\n";

// Clear all optimized caches
Artisan::call('optimize:clear');
echo "Optimized caches cleared.\n";
