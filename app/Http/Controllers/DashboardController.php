<?php

namespace App\Http\Controllers;

use App\Models\CeisaApiLog;
use App\Models\CeisaDocument;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:dashboard.view', only: ['index']),
        ];
    }
    public function index()
    {
        $totalDoc = CeisaDocument::count();
        $totalBC20 = CeisaDocument::where('jenis_dokumen', '20')->count();
        $totalBC30 = CeisaDocument::where('jenis_dokumen', '30')->count();

        $totalCifIdr = CeisaDocument::where('jenis_dokumen', '20')->sum('total_nilai_idr');
        $totalFobIdr = CeisaDocument::where('jenis_dokumen', '30')->sum('total_nilai_idr');

        $totalSppb = CeisaDocument::where('status', 'SPPB')->count();
        $totalNpe = CeisaDocument::where('status', 'NPE')->count();
        $totalBillingAktif = CeisaDocument::where('status_billing', 'UNPAID')->whereNotNull('kode_billing')->count();

        $totalShipments = Shipment::count();
        $shipmentsCollecting = Shipment::where('status_tahap', 'DOC_COLLECTING')->count();
        $shipmentsBilling = Shipment::whereIn('status_tahap', ['CEISA_SUBMITTED', 'BILLING_ISSUED'])->count();
        $shipmentsDo = Shipment::whereIn('status_tahap', ['DO_PROCESSING', 'DO_RELEASED'])->count();
        $shipmentsTraffic = Shipment::whereIn('status_tahap', ['ARRIVED', 'INSPECTION_CUSTOMS', 'TRAFFIC_HOLD', 'SPPB_RELEASED', 'TRAFFIC_SPPB', 'GATE_OUT'])->count();

        $recentShipments = Shipment::with(['client', 'shippingVendor', 'ceisaDocument'])->latest()->take(6)->get();
        $recentDocuments = CeisaDocument::latest()->take(6)->get();
        $recentLogs = CeisaApiLog::latest()->take(6)->get();

        return view('dashboard', compact(
            'totalDoc',
            'totalBC20',
            'totalBC30',
            'totalCifIdr',
            'totalFobIdr',
            'totalSppb',
            'totalNpe',
            'totalBillingAktif',
            'totalShipments',
            'shipmentsCollecting',
            'shipmentsBilling',
            'shipmentsDo',
            'shipmentsTraffic',
            'recentShipments',
            'recentDocuments',
            'recentLogs'
        ));
    }
}
