<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

app()->setLocale('id');
echo "Locale is: " . app()->getLocale() . "\n";
echo "Dashboard definition: " . __('Dashboard') . "\n";
echo "Workspace definition: " . __('Workspace') . "\n";
echo "From english: " . __('Dashboard', [], 'en') . "\n";
