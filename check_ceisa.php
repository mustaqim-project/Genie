<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CEISA DOCUMENTS ===\n";
$docs = \App\Models\CeisaDocument::all();
foreach ($docs as $d) {
    echo "ID: {$d->id} | Aju: {$d->nomor_aju} | Jenis: {$d->jenis_dokumen} | Status: {$d->status}\n";
}

echo "\n=== IMPORT SHIPMENTS ===\n";
$shps = \App\Models\Shipment::where(function($q) {
    $q->where('jenis_layanan', 'IMPORT_BC20')
      ->orWhere('jenis_layanan', 'IMPORT')
      ->orWhereNull('jenis_layanan');
})->get();

foreach ($shps as $s) {
    echo "Shipment: {$s->id} | No: {$s->project_no} | Layanan: {$s->jenis_layanan} | CeisaDocID: {$s->ceisa_document_id}\n";
}

echo "\n=== CONTROLLER INDEX RESULT ===\n";
$req = \Illuminate\Http\Request::create('/bc-20', 'GET');
$controller = app(\App\Http\Controllers\BC20Controller::class);
$res = $controller->index($req);
echo "Total Documents in BC20 index: " . $res->getData()['documents']->total() . "\n";
foreach ($res->getData()['documents'] as $d) {
    echo "Doc: {$d->nomor_aju} | Status: {$d->status}\n";
}
