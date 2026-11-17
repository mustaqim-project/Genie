<?php

namespace App\Http\Controllers;

use App\Models\CashBankAccount;
use App\Models\CeisaDocument;
use App\Models\Client;
use App\Models\ClientInvoice;
use App\Models\Quotation;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DivisionReportController extends Controller
{
    /**
     * Dashboard Ikhtisar Reporting 4 Divisi
     */
    public function index()
    {
        // 1. Marketing Metrics
        $totalQuotations = Quotation::count();
        $approvedQuotations = Quotation::whereIn('status', ['APPROVED', 'ACCEPTED', 'CONVERTED_TO_INVOICE', 'CONVERTED_TO_SO'])->count();
        $marketingWinRate = $totalQuotations > 0 ? round(($approvedQuotations / $totalQuotations) * 100, 1) : 0;
        $totalPipelineValue = Quotation::where('status', 'PENDING')->sum('total_idr');

        // 2. Document Metrics
        $totalShipments = Shipment::count();
        $sppbCount = Shipment::where('status_tahap', 'COMPLETED')->orWhere('sppb_received', true)->count();
        $jalurHijauCount = Shipment::where('customs_channel', 'HIJAU')->count();
        $jalurMerahCount = Shipment::where('customs_channel', 'MERAH')->count();

        // 3. Traffic Metrics
        $activeDeliveries = Shipment::whereIn('status_tahap', ['TRUCKING', 'GATE_OUT', 'IN_TRANSIT', 'DELIVERED'])->count();
        $completedPod = Shipment::whereNotNull('pod_signed_at')->orWhere('status_tahap', 'COMPLETED')->count();

        // 4. Finance Metrics
        $totalAr = ClientInvoice::where('status', '!=', 'PAID')->sum('total_tagihan_idr');
        $totalPaid = ClientInvoice::where('status', 'PAID')->sum('total_tagihan_idr');
        $cashBalance = CashBankAccount::all()->sum(fn($acc) => $acc->current_balance);

        return view('reports.division_index', compact(
            'totalQuotations',
            'approvedQuotations',
            'marketingWinRate',
            'totalPipelineValue',
            'totalShipments',
            'sppbCount',
            'jalurHijauCount',
            'jalurMerahCount',
            'activeDeliveries',
            'completedPod',
            'totalAr',
            'totalPaid',
            'cashBalance'
        ));
    }

    /**
     * Export data operasional per divisi ke file spreadsheet (.xlsx / CSV with UTF-8 BOM)
     */
    public function export(Request $request): StreamedResponse
    {
        $division = strtolower($request->query('division', 'marketing'));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $filename = "Laporan_Divisi_{$division}_" . date('Ymd_His') . ".csv";

        return response()->streamDownload(function () use ($division, $dateFrom, $dateTo) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            switch ($division) {
                case 'marketing':
                    // Header Marketing: Pipeline, Konversi & Penawaran
                    fputcsv($handle, [
                        'Nomor Quotation',
                        'Nama Klien / Prospek',
                        'Layanan / Service',
                        'Rute (Origin -> Destination)',
                        'Status Penawaran',
                        'Total Nilai Penawaran (IDR)',
                        'Tanggal Penawaran',
                        'Berlaku Hingga',
                        'Telah Dikonversi ke Invoice',
                    ]);

                    $quotations = Quotation::with('client')->latest();
                    if ($dateFrom) $quotations->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $quotations->whereDate('created_at', '<=', $dateTo);

                    foreach ($quotations->get() as $q) {
                        fputcsv($handle, [
                            $q->quotation_no ?? $q->quotation_number ?? '-',
                            $q->client->name ?? $q->client->nama_perusahaan ?? '-',
                            $q->service_type ?? 'Clearance',
                            ($q->pelabuhan_asal ?? $q->origin ?? '-') . ' -> ' . ($q->pelabuhan_tujuan ?? $q->destination ?? '-'),
                            $q->status ?? 'DRAFT',
                            number_format((float) ($q->total_idr ?? $q->total_amount ?? 0), 2, '.', ''),
                            $q->tanggal ? date('Y-m-d', strtotime($q->tanggal)) : ($q->created_at ? $q->created_at->format('Y-m-d') : '-'),
                            $q->valid_until ?? '-',
                            $q->converted_invoice_id ? 'YA' : 'BELUM',
                        ]);
                    }
                    break;

                case 'document':
                    // Header Document: Status Dokumen Pabean & Respon CEISA
                    fputcsv($handle, [
                        'No. Job Order',
                        'Klien / Importir / Eksportir',
                        'Jenis Dokumen (PIB/PEB)',
                        'Nomor Aju Pabean',
                        'Nomor Pendaftaran Pabean',
                        'Jalur Pabean (Hijau/Kuning/Merah)',
                        'Nomor B/L atau AWB',
                        'Pelabuhan Bongkar/Muat',
                        'Total Bea & Pajak (IDR)',
                        'Tanggal SPPB / Selesai',
                        'Status Dokumen',
                    ]);

                    $shipments = Shipment::with('client')->latest();
                    if ($dateFrom) $shipments->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $shipments->whereDate('created_at', '<=', $dateTo);

                    foreach ($shipments->get() as $s) {
                        fputcsv($handle, [
                            $s->project_no ?? '-',
                            $s->client->name ?? $s->client->nama_perusahaan ?? '-',
                            $s->type ?? ($s->jenis_layanan === 'EKSPORT_BC30' ? 'EXPORT' : 'IMPORT'),
                            $s->nomor_aju ?? '-',
                            $s->nomor_daftar ?? '-',
                            $s->customs_channel ?? $s->jalur ?? 'DRAFT',
                            $s->bl_number ?? $s->nomor_bl_awb ?? $s->m_awb ?? '-',
                            $s->port_of_discharge ?? $s->pod ?? $s->pelabuhan_bongkar ?? '-',
                            number_format((float) ($s->total_billing ?? $s->total_nilai_idr ?? 0), 2, '.', ''),
                            $s->sppb_received_at ? $s->sppb_received_at->format('Y-m-d') : ($s->sppb_date ? date('Y-m-d', strtotime($s->sppb_date)) : '-'),
                            $s->status_tahap ?? $s->status ?? '-',
                        ]);
                    }
                    break;

                case 'traffic':
                    // Header Traffic: Utilisasi Armada, Lead Time & POD Delivery
                    fputcsv($handle, [
                        'No. Job Order',
                        'Klien',
                        'Sarana Pengangkut (Kapal/Pesawat)',
                        'Voyage / Flight No',
                        'Nomor Kontainer / AWB',
                        'Truk / Vendor Ekspedisi',
                        'Nomor Polisi Truk',
                        'Driver & No. Kontak',
                        'Estimasi Tiba (ETA)',
                        'Aktual Tiba (ATA / Sandar)',
                        'Tanggal Gate Out Pelabuhan',
                        'Tanggal Terima Gudang (POD)',
                        'Template Surat Jalan',
                        'Status Pengiriman',
                    ]);

                    $trafficShipments = Shipment::with('client')->latest();
                    if ($dateFrom) $trafficShipments->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $trafficShipments->whereDate('created_at', '<=', $dateTo);

                    foreach ($trafficShipments->get() as $ts) {
                        fputcsv($handle, [
                            $ts->project_no ?? '-',
                            $ts->client->nama_perusahaan ?? '-',
                            $ts->vessel_name ?? '-',
                            $ts->voyage_number ?? '-',
                            $ts->container_number ?? $ts->bl_number ?? '-',
                            $ts->trucking_vendor ?? 'Armada Internal',
                            $ts->police_number ?? '-',
                            ($ts->driver_name ?? '-') . ' (' . ($ts->driver_phone ?? '-') . ')',
                            $ts->eta ? date('Y-m-d H:i', strtotime($ts->eta)) : '-',
                            $ts->ata ? date('Y-m-d H:i', strtotime($ts->ata)) : '-',
                            $ts->gate_out_date ? date('Y-m-d H:i', strtotime($ts->gate_out_date)) : '-',
                            $ts->pod_date ? date('Y-m-d H:i', strtotime($ts->pod_date)) : '-',
                            strtoupper($ts->surat_jalan_template ?? 'FCL'),
                            $ts->status ?? '-',
                        ]);
                    }
                    break;

                case 'finance':
                    // Header Finance: Cashflow, Tagihan Invoice & Aging AR
                    fputcsv($handle, [
                        'Nomor Invoice',
                        'Perusahaan Klien',
                        'No. Job Order',
                        'Tanggal Terbit',
                        'Jatuh Tempo (Due Date)',
                        'Total Nilai Tagihan (IDR)',
                        'Potongan Saldo Deposit (IDR)',
                        'Sisa Piutang Berjalan (IDR)',
                        'Status Pelunasan',
                        'Kategori Aging Piutang',
                    ]);

                    $invoices = ClientInvoice::with(['client', 'shipment'])->latest();
                    if ($dateFrom) $invoices->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $invoices->whereDate('created_at', '<=', $dateTo);

                    foreach ($invoices->get() as $inv) {
                        $dueDate = $inv->due_date ? strtotime($inv->due_date) : time();
                        $daysOverdue = floor((time() - $dueDate) / (60 * 60 * 24));
                        $aging = 'Lancar (Current)';
                        if ($inv->status != 'PAID') {
                            if ($daysOverdue > 90) $aging = '> 90 Hari (Macet)';
                            elseif ($daysOverdue > 60) $aging = '61 - 90 Hari';
                            elseif ($daysOverdue > 30) $aging = '31 - 60 Hari';
                            elseif ($daysOverdue > 0) $aging = '1 - 30 Hari Overdue';
                        }

                        fputcsv($handle, [
                            $inv->invoice_number ?? '-',
                            $inv->client->nama_perusahaan ?? '-',
                            $inv->shipment->project_no ?? '-',
                            $inv->created_at ? $inv->created_at->format('Y-m-d') : '-',
                            $inv->due_date ?? '-',
                            number_format($inv->total_tagihan_idr ?? 0, 2, '.', ''),
                            number_format($inv->deposit_deducted_idr ?? 0, 2, '.', ''),
                            number_format($inv->sisa_tagihan_idr ?? $inv->total_tagihan_idr ?? 0, 2, '.', ''),
                            $inv->status ?? 'DRAFT',
                            $aging,
                        ]);
                    }
                    break;
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}