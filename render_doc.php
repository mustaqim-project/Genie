<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
auth()->login($user);
view()->share('errors', new \Illuminate\Support\ViewErrorBag());

$shp = \App\Models\Shipment::find('01a1d0c2-2d49-7125-83ff-5e0e28e5ef6f');
if (!$shp) {
    $shp = \App\Models\Shipment::first();
}

$ctrl = $app->make(\App\Http\Controllers\ShipmentProjectController::class);
$res = $ctrl->show('01a1d0c2-2d49-7125-83ff-5e0e28e5ef6f');
$html = $res->render();
file_put_contents(__DIR__ . '/rendered_document_show.html', $html);
echo "Rendered " . strlen($html) . " bytes into rendered_document_show.html\n";

$lines = explode("\n", $html);
echo "Total lines: " . count($lines) . "\n";
if (isset($lines[1860])) {
    echo "Line 1861: " . trim($lines[1860]) . "\n";
}
if (isset($lines[1872])) {
    echo "Line 1873: " . trim($lines[1872]) . "\n";
}
