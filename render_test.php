<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = \App\Models\User::first();
    auth()->login($user);
    $controller = $app->make(\App\Http\Controllers\ShipmentProjectController::class);
    $request = Illuminate\Http\Request::create('/core/document', 'GET');
    $response = $controller->index($request);
    $html = $response->render();
    echo "RENDER SUCCESS, HTML length: " . strlen($html) . "\n";
    file_put_contents('rendered_output.html', $html);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
