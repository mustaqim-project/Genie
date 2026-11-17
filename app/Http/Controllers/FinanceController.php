<?php

namespace App\Http\Controllers;

use App\Models\CashBankAccount;
use App\Models\CeisaDocument;
use App\Models\Client;
use App\Models\ClientInvoice;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:finance.view', only: ['index', 'pnlReport', 'exportPnl']),
            new Middleware('permission:finance.manage', except: ['index', 'pnlReport', 'exportPnl']),
        ];
    }

    public function index()
    {
        $invoices = ClientInvoice::with(['client', 'shipment'])->latest()->paginate(10);
        $totalAr = ClientInvoice::where('status', '!=', 'PAID')->sum('total_tagihan_idr');
        $totalBilling = CeisaDocument::whereNotNull('kode_billing')->sum('total_billing');
        $totalCashBank = CashBankAccount::all()->sum(fn($acc) => $acc->current_balance);

        return view('finance.index', compact('invoices', 'totalAr', 'totalBilling', 'totalCashBank'));
    }

    /**
     * Laporan Profit & Loss (PnL) per Job Order & Rekapitulasi Periode (Sesuai MoM Poin 9)
     * Revenue = Total Invoice Job
     * Cost = Tebus DO + Penumpukan + Storage + LOLO + Trucking + Behandle + Operasional lainnya
     * Gross Profit = Revenue - Total Cost
     */
    public function pnlReport(Request $request)
    {
        $clients = Client::orderBy('name')->get();

        $query = Shipment::with(['client', 'invoices', 'operationCosts'])
            ->latest('created_at');

        // Filter Klien
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filter Jenis Layanan (Import / Export)
        if ($request->filled('type')) {
            $t = strtoupper($request->type);
            if ($t === 'EXPORT') {
                $query->where('jenis_layanan', 'EKSPORT_BC30');
            } else {
                $query->where('jenis_layanan', '!=', 'EKSPORT_BC30');
            }
        }

        // Filter Status Pekerjaan
        if ($request->filled('status')) {
            $query->where('status_tahap', $request->status);
        }

        // Filter Periode Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $allShipments = (clone $query)->get();

        // Hitung Metrik Agregat PnL
        $totalRevenue = 0;
        $totalCost = 0;
        $pnlItems = [];

        foreach ($allShipments as $shipment) {
            $revenue = (float) $shipment->invoices->where('status', '!=', 'VOID')->sum('total_tagihan_idr');
            $costDo = (float) ($shipment->biaya_tebus_do_idr ?? 0);
            $costOps = (float) $shipment->operationCosts->sum('nominal_idr');
            $costTotal = $costDo + $costOps;
            $grossProfit = $revenue - $costTotal;
            $marginPct = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : ($grossProfit < 0 ? -100 : 0);

            $totalRevenue += $revenue;
            $totalCost += $costTotal;

            $pnlItems[] = [
                'shipment' => $shipment,
                'revenue' => $revenue,
                'cost_do' => $costDo,
                'cost_ops' => $costOps,
                'cost_total' => $costTotal,
                'gross_profit' => $grossProfit,
                'margin_pct' => $marginPct,
            ];
        }

        $totalGrossProfit = $totalRevenue - $totalCost;
        $avgMarginPct = $totalRevenue > 0 ? round(($totalGrossProfit / $totalRevenue) * 100, 1) : 0;

        // Paginate data tabel
        $shipments = $query->paginate(15)->withQueryString();

        return view('finance.pnl_index', compact(
            'clients',
            'shipments',
            'totalRevenue',
            'totalCost',
            'totalGrossProfit',
            'avgMarginPct',
            'allShipments'
        ));
    }

    /**
     * Export PnL per Job ke file format Excel (.xlsx / CSV with UTF-8 BOM)
     */
    public function exportPnl(Request $request): StreamedResponse
    {
        $query = Shipment::with(['client', 'invoices', 'operationCosts'])->latest('created_at');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('type')) {
            $query->where('type', strtoupper($request->type));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $shipments = $query->get();
        $filename = 'Laporan_PnL_JobOrder_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($shipments) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM agar terbaca sempurna di Microsoft Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Header Kolom Laporan PnL
            fputcsv($handle, [
                'No. Job Order',
                'Klien / Perusahaan',
                'Tipe Layanan',
                'Nomor B/L / AWB',
                'Nomor Aju Pabean',
                'Status Pekerjaan',
                'Tanggal Order',
                'Revenue / Tagihan (IDR)',
                'Biaya Tebus DO (IDR)',
                'Biaya Operasional Lapangan (IDR)',
                'Total Beban Biaya (IDR)',
                'Gross Profit (IDR)',
                'Margin Laba (%)',
            ]);

            foreach ($shipments as $s) {
                $revenue = (float) $s->invoices->where('status', '!=', 'VOID')->sum('total_tagihan_idr');
                $costDo = (float) ($s->biaya_tebus_do_idr ?? 0);
                $costOps = (float) $s->operationCosts->sum('nominal_idr');
                $costTotal = $costDo + $costOps;
                $grossProfit = $revenue - $costTotal;
                $marginPct = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : 0;

                fputcsv($handle, [
                    $s->project_no ?? '-',
                    $s->client->nama_perusahaan ?? '-',
                    $s->type ?? 'IMPORT',
                    $s->bl_number ?? $s->m_awb ?? '-',
                    $s->nomor_aju ?? '-',
                    $s->status ?? '-',
                    $s->created_at ? $s->created_at->format('Y-m-d') : '-',
                    number_format($revenue, 2, '.', ''),
                    number_format($costDo, 2, '.', ''),
                    number_format($costOps, 2, '.', ''),
                    number_format($costTotal, 2, '.', ''),
                    number_format($grossProfit, 2, '.', ''),
                    $marginPct . '%',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
