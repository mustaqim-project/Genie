<?php

namespace App\Http\Controllers;

use App\Models\ClientInvoice;
use App\Models\VendorBill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaxController extends Controller
{
    /**
     * Dashboard Perpajakan Logistik & PPJK (PPN 1.1% PMK 71/2022 & PPh 23)
     * Hak Akses: finance.view
     */
    public function index(Request $request)
    {
        Gate::authorize('finance.view');

        $year = (int) ($request->year ?? now()->year);
        $month = (int) ($request->month ?? now()->month);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // 1. Rekap PPN Keluaran (PMK 71/PMK.03/2022 - Efektif 1.1% DPP Nilai Lain)
        $invoicesQuery = ClientInvoice::with(['client', 'shipment.ceisaDocument'])
            ->whereBetween('tanggal', [$startDate, $endDate]);

        $invoices = $invoicesQuery->latest('tanggal')->get();

        $totalDppJasa = $invoices->sum(fn($i) => (float) ($i->jasa_ppjk_idr + $i->biaya_trucking_idr));
        $totalPpnKeluaran = $invoices->sum(fn($i) => (float) $i->ppn_jasa_idr);
        $totalReimbursableCustoms = $invoices->sum(fn($i) => (float) $i->talangan_pabean_idr);

        // 2. Rekap PPN Masukan (Vendor AP - Tarif Normal 11%)
        // Asumsi faktur masukan dari pelayaran resmi (DO fee) dan depo TPS dengan PPN 11%
        $vendorBills = VendorBill::with('shipment')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalVendorSpend = $vendorBills->sum(fn($b) => (float) $b->amount);
        
        // Estimasi PPN Masukan dari vendor resmi pelayaran & TPS (11/111 dari nilai atau 11% DPP)
        $vatEligibleBills = $vendorBills->whereIn('vendor_type', ['SHIPPING_LINE', 'TPS']);
        $totalPpnMasukan = $vatEligibleBills->sum(function ($b) {
            // Jika tagihan sudah termasuk PPN 11%, nilai PPN = amount * (11 / 111)
            return round((float) $b->amount * (11 / 111), 2);
        });

        // 3. Estimasi Pemotongan PPh Pasal 23 (2% atas Jasa Forwarding/Trucking)
        $totalPph23Withheld = round($totalDppJasa * 0.02, 2);

        // 4. Net PPN Terutang (Kurang / Lebih Bayar)
        $netPpnPayable = $totalPpnKeluaran - $totalPpnMasukan;

        // 5. Kalender Kepatuhan Pajak (Tax Due Dates)
        $nextMonthEnd = $startDate->copy()->addMonth()->endOfMonth();
        $daysUntilPpnDue = now()->diffInDays($nextMonthEnd, false);

        $pph23PaymentDue = $startDate->copy()->addMonth()->setDay(10);
        $pph23ReportDue = $startDate->copy()->addMonth()->setDay(20);

        $taxCalendar = [
            [
                'title' => 'SPT Masa PPN (PMK 71/2022)',
                'period' => $startDate->translatedFormat('F Y'),
                'due_date' => $nextMonthEnd->format('d M Y'),
                'type' => 'PPN_MASA',
                'days_left' => (int) $daysUntilPpnDue,
                'status' => $daysUntilPpnDue < 0 ? 'OVERDUE' : ($daysUntilPpnDue <= 5 ? 'URGENT' : 'ON_SCHEDULE'),
                'description' => 'Batas akhir pembayaran & pelaporan SPT Masa PPN melalui e-Faktur Web DJP',
            ],
            [
                'title' => 'Setor PPh Pasal 23',
                'period' => $startDate->translatedFormat('F Y'),
                'due_date' => $pph23PaymentDue->format('d M Y'),
                'type' => 'PPH23_PAY',
                'days_left' => (int) now()->diffInDays($pph23PaymentDue, false),
                'status' => now()->diffInDays($pph23PaymentDue, false) < 0 ? 'OVERDUE' : 'ON_SCHEDULE',
                'description' => 'Batas akhir pembayaran PPh Pasal 23 yang dipotong melalui pembuatan Kode Billing',
            ],
            [
                'title' => 'Lapor SPT Masa Unifikasi PPh 23',
                'period' => $startDate->translatedFormat('F Y'),
                'due_date' => $pph23ReportDue->format('d M Y'),
                'type' => 'PPH23_REPORT',
                'days_left' => (int) now()->diffInDays($pph23ReportDue, false),
                'status' => now()->diffInDays($pph23ReportDue, false) < 0 ? 'OVERDUE' : 'ON_SCHEDULE',
                'description' => 'Pelaporan bukti potong unifikasi PPh Pasal 23 melalui e-Bupot Unifikasi',
            ],
        ];

        return view('finance.tax_index', compact(
            'year',
            'month',
            'startDate',
            'invoices',
            'vendorBills',
            'totalDppJasa',
            'totalPpnKeluaran',
            'totalReimbursableCustoms',
            'totalVendorSpend',
            'totalPpnMasukan',
            'totalPph23Withheld',
            'netPpnPayable',
            'taxCalendar'
        ));
    }

    /**
     * Export Rekap Faktur Pajak Keluaran ke format CSV (format e-Faktur DJP compatible)
     */
    public function exportEfaktur(Request $request)
    {
        Gate::authorize('finance.view');

        $year = (int) ($request->year ?? now()->year);
        $month = (int) ($request->month ?? now()->month);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $invoices = ClientInvoice::with(['client', 'shipment.ceisaDocument'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->latest('tanggal')
            ->get();

        $fileName = "rekap_efaktur_ppn_pmk71_{$year}_{$month}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');
            // Menulis BOM UTF-8 agar Excel membuka dengan karakter benar
            fputs($file, "\xEF\xBB\xBF");

            // Header kolom
            fputcsv($file, [
                'FK', 'KODE_OBJEK', 'NO_FAKTUR', 'TANGGAL_FAKTUR', 'NPWP_KLIEN', 'NAMA_KLIEN', 
                'ALAMAT_KLIEN', 'DPP_NILAI_LAIN', 'PPN_1_1', 'PPN_BM', 'REFERENSI_INVOICE'
            ]);

            foreach ($invoices as $inv) {
                $dpp = (float) ($inv->jasa_ppjk_idr + $inv->biaya_trucking_idr);
                $ppn = (float) $inv->ppn_jasa_idr;

                fputcsv($file, [
                    'FK',
                    '04', // Kode Transaksi 04: PPN DPP Nilai Lain PMK 71/2022
                    '040.' . date('y') . '.9' . substr(preg_replace('/\D/', '', $inv->id), 0, 7),
                    $inv->tanggal ? $inv->tanggal->format('d/m/Y') : date('d/m/Y'),
                    $inv->client?->npwp ?? '00.000.000.0-000.000',
                    $inv->client?->name ?? 'KLIEN UMUM',
                    $inv->client?->address ?? 'DKI Jakarta',
                    number_format($dpp, 2, '.', ''),
                    number_format($ppn, 2, '.', ''),
                    '0.00',
                    $inv->invoice_no,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
