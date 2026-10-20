<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Shipment;
use App\Services\Accounting\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:accounting.view', only: ['index', 'exportLedger']),
            new Middleware('permission:accounting.manage', except: ['index', 'exportLedger']),
        ];
    }

    /**
     * Dashboard Akuntansi, Buku Besar, P&L, Neraca, & Job Costing Margin
     */
    public function index(Request $request)
    {
        // Pastikan transaksi historis tersinkron ke jurnal umum
        app(AccountingService::class)->syncHistoricalTransactions();

        $activeTab = $request->query('tab', 'pnl'); // pnl, balance_sheet, gl, job_costing

        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : now()->startOfYear();
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : now()->endOfDay();

        // =========================================================================
        // 1. LAPORAN LABA RUGI (PROFIT & LOSS STATEMENT / P&L)
        // =========================================================================
        $revenueAccounts = ChartOfAccount::where('type', 'REVENUE')
            ->orderBy('code')
            ->get()
            ->map(function ($acc) use ($dateFrom, $dateTo) {
                // Untuk akun REVENUE: Saldo Normal Kredit (Credit - Debit)
                $lines = JournalLine::where('account_id', $acc->id)
                    ->whereHas('entry', fn($q) => $q->whereBetween('entry_date', [$dateFrom, $dateTo]))
                    ->get();

                $total = (float) $lines->sum('credit') - (float) $lines->sum('debit');
                $acc->total_amount = max(0, $total);
                return $acc;
            });

        $totalRevenue = $revenueAccounts->sum('total_amount');

        // Beban Pokok Forwarding (HPP 5-1xxx)
        $hppAccounts = ChartOfAccount::where('type', 'EXPENSE')
            ->where('code', 'like', '5-1%')
            ->orderBy('code')
            ->get()
            ->map(function ($acc) use ($dateFrom, $dateTo) {
                // Untuk akun EXPENSE: Saldo Normal Debit (Debit - Credit)
                $lines = JournalLine::where('account_id', $acc->id)
                    ->whereHas('entry', fn($q) => $q->whereBetween('entry_date', [$dateFrom, $dateTo]))
                    ->get();

                $total = (float) $lines->sum('debit') - (float) $lines->sum('credit');
                $acc->total_amount = max(0, $total);
                return $acc;
            });

        $totalHpp = $hppAccounts->sum('total_amount');
        $grossProfit = $totalRevenue - $totalHpp;
        $grossMarginPct = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;

        // Beban Operasional & Umum (OPEX 5-2xxx s.d. 5-4xxx)
        $opexAccounts = ChartOfAccount::where('type', 'EXPENSE')
            ->where('code', 'not like', '5-1%')
            ->orderBy('code')
            ->get()
            ->map(function ($acc) use ($dateFrom, $dateTo) {
                $lines = JournalLine::where('account_id', $acc->id)
                    ->whereHas('entry', fn($q) => $q->whereBetween('entry_date', [$dateFrom, $dateTo]))
                    ->get();

                $total = (float) $lines->sum('debit') - (float) $lines->sum('credit');
                $acc->total_amount = max(0, $total);
                return $acc;
            });

        $totalOpex = $opexAccounts->sum('total_amount');
        $netOperatingProfit = $grossProfit - $totalOpex;
        $netMarginPct = $totalRevenue > 0 ? round(($netOperatingProfit / $totalRevenue) * 100, 1) : 0;

        // =========================================================================
        // 2. NERACA KEUANGAN (BALANCE SHEET)
        // =========================================================================
        // ASET (1-xxxx)
        $assetAccounts = ChartOfAccount::where('type', 'ASSET')
            ->whereNotNull('parent_id')
            ->orderBy('code')
            ->get()
            ->map(function ($acc) {
                // Saldo kumulatif aset (Debit - Credit)
                $lines = JournalLine::where('account_id', $acc->id)->get();
                $acc->total_amount = (float) $lines->sum('debit') - (float) $lines->sum('credit');
                return $acc;
            });
        $totalAssets = $assetAccounts->sum('total_amount');

        // KEWAJIBAN / HUTANG (2-xxxx)
        $liabilityAccounts = ChartOfAccount::where('type', 'LIABILITY')
            ->whereNotNull('parent_id')
            ->orderBy('code')
            ->get()
            ->map(function ($acc) {
                // Saldo kumulatif kewajiban (Credit - Debit)
                $lines = JournalLine::where('account_id', $acc->id)->get();
                $acc->total_amount = (float) $lines->sum('credit') - (float) $lines->sum('debit');
                return $acc;
            });
        $totalLiabilities = $liabilityAccounts->sum('total_amount');

        // EKUITAS (3-xxxx)
        $equityAccounts = ChartOfAccount::where('type', 'EQUITY')
            ->whereNotNull('parent_id')
            ->orderBy('code')
            ->get()
            ->map(function ($acc) {
                $lines = JournalLine::where('account_id', $acc->id)->get();
                $acc->total_amount = (float) $lines->sum('credit') - (float) $lines->sum('debit');
                return $acc;
            });

        $capitalPaid = $equityAccounts->where('code', '3-1100')->first()?->total_amount ?? 850000000.00;
        $retainedEarnings = $equityAccounts->where('code', '3-2100')->first()?->total_amount ?? 0.00;
        $currentEarnings = $netOperatingProfit; // Laba Berjalan dialokasikan ke Ekuitas

        $totalEquity = $capitalPaid + $retainedEarnings + $currentEarnings;
        $balanceDifference = abs($totalAssets - ($totalLiabilities + $totalEquity));
        $isBalanceSheetBalanced = $balanceDifference < 1.00;

        // =========================================================================
        // 3. BUKU BESAR / JURNAL UMUM (GENERAL LEDGER)
        // =========================================================================
        $glQuery = JournalEntry::with(['lines.account', 'postedBy'])
            ->whereBetween('entry_date', [$dateFrom, $dateTo]);

        if ($request->filled('account_id')) {
            $glQuery->whereHas('lines', fn($q) => $q->where('account_id', $request->account_id));
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $glQuery->where(function ($q) use ($s) {
                $q->where('memo', 'like', "%{$s}%")
                  ->orWhere('source_type', 'like', "%{$s}%");
            });
        }

        $journalEntries = $glQuery->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $allCoas = ChartOfAccount::whereNotNull('parent_id')->orderBy('code')->get();

        // =========================================================================
        // 4. ANALISIS JOB COSTING & PROFIT MARGIN PER SHIPMENT
        // =========================================================================
        $shipments = Shipment::with(['client', 'ceisaDocument', 'invoices', 'vendorBills'])
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($shipment) {
                $inv = $shipment->invoices->first();
                // Pendapatan Resmi Forwarding (DPP Jasa + Trucking + Handling Pelayaran)
                $revForwarding = $inv 
                    ? (float) ($inv->jasa_ppjk_idr + $inv->biaya_trucking_idr + $inv->biaya_pelayaran_idr)
                    : 0.0;

                // Beban Pokok Langsung (Seluruh tagihan vendor AP)
                $costDirect = (float) $shipment->vendorBills->sum('amount');

                $marginIdr = $revForwarding - $costDirect;
                $marginPct = $revForwarding > 0 ? round(($marginIdr / $revForwarding) * 100, 1) : 0;

                $health = 'HEALTHY';
                if ($revForwarding == 0) {
                    $health = 'PENDING_BILLING';
                } elseif ($marginIdr < 0) {
                    $health = 'LOSS';
                } elseif ($marginPct < 15) {
                    $health = 'LOW_MARGIN';
                }

                return [
                    'id' => $shipment->id,
                    'number' => $shipment->shipment_number,
                    'aju' => $shipment->ceisaDocument?->nomor_aju ?? '-',
                    'doc_type' => $shipment->ceisaDocument?->jenis_dokumen ?? 'PIB BC 2.0',
                    'client' => $shipment->client?->name ?? 'Klien Umum',
                    'revenue' => $revForwarding,
                    'cost' => $costDirect,
                    'margin_idr' => $marginIdr,
                    'margin_pct' => $marginPct,
                    'health' => $health,
                    'status' => $shipment->status_tahap,
                ];
            });

        return view('accounting.index', compact(
            'activeTab',
            'dateFrom',
            'dateTo',
            'revenueAccounts',
            'totalRevenue',
            'hppAccounts',
            'totalHpp',
            'grossProfit',
            'grossMarginPct',
            'opexAccounts',
            'totalOpex',
            'netOperatingProfit',
            'netMarginPct',
            'assetAccounts',
            'totalAssets',
            'liabilityAccounts',
            'totalLiabilities',
            'capitalPaid',
            'retainedEarnings',
            'currentEarnings',
            'totalEquity',
            'balanceDifference',
            'isBalanceSheetBalanced',
            'journalEntries',
            'allCoas',
            'shipments'
        ));
    }

    /**
     * Export Buku Jurnal Umum (General Ledger) ke format CSV
     */
    public function exportLedger(Request $request)
    {
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : now()->startOfYear();
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : now()->endOfDay();

        $entries = JournalEntry::with(['lines.account'])
            ->whereBetween('entry_date', [$dateFrom, $dateTo])
            ->orderBy('entry_date', 'asc')
            ->get();

        $fileName = "jurnal_umum_genie_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($entries) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, [
                'ID_JURNAL', 'TANGGAL', 'SUMBER', 'KETERANGAN_MEMO', 
                'KODE_AKUN', 'NAMA_AKUN', 'DEBIT_IDR', 'KREDIT_IDR'
            ]);

            foreach ($entries as $e) {
                foreach ($e->lines as $l) {
                    fputcsv($file, [
                        $e->id,
                        $e->entry_date ? $e->entry_date->format('Y-m-d') : '',
                        $e->source_type,
                        $e->memo,
                        $l->account?->code ?? '-',
                        $l->account?->name ?? '-',
                        number_format((float) $l->debit, 2, '.', ''),
                        number_format((float) $l->credit, 2, '.', ''),
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
