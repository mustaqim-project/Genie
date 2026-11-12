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

    /**
     * Simpan Jurnal Manual Seimbang (Debit == Kredit)
     */
    public function storeManualEntry(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'memo' => 'required|string|max:500',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|uuid|exists:chart_of_accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
        ]);

        if (\App\Models\AccountingPeriod::isDateLocked($validated['entry_date'])) {
            return back()->with('error', "Posting Jurnal ditolak: Tanggal [{$validated['entry_date']}] berada di periode akuntansi yang telah DITUTUP/DIKUNCI.");
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;
        foreach ($validated['lines'] as $line) {
            $totalDebit += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        if (abs($totalDebit - $totalCredit) > 0.05) {
            return back()->with('error', "Jurnal tidak seimbang: Total Debit (Rp " . number_format($totalDebit, 2) . ") != Total Kredit (Rp " . number_format($totalCredit, 2) . ")");
        }

        $sourceId = (string) \Illuminate\Support\Str::uuid();
        $entry = AccountingService::createBalancedEntry(
            'MANUAL',
            $sourceId,
            $validated['entry_date'],
            $validated['memo'],
            $validated['lines'],
            auth()->id()
        );

        \App\Models\AuditLog::record('CREATE_MANUAL_JOURNAL', 'JournalEntry', $entry->id, null, [
            'entry_date' => $validated['entry_date'],
            'memo' => $validated['memo'],
            'total' => $totalDebit,
        ]);

        return back()->with('success', "Jurnal manual [{$entry->id}] berhasil diposting ke Buku Besar.");
    }

    /**
     * Pembalikan Jurnal (Reverse Entry) Satu-Klik pada Periode Terbuka
     */
    public function reverseEntry(string $id, Request $request)
    {
        $original = JournalEntry::with('lines')->findOrFail($id);

        $reverseDate = now()->format('Y-m-d');
        if (\App\Models\AccountingPeriod::isDateLocked($reverseDate)) {
            return back()->with('error', "Pembalikan jurnal ditolak: Hari ini berada pada periode buku yang dikunci.");
        }

        $alreadyReversed = JournalEntry::where('source_type', 'REVERSAL')
            ->where('source_id', $original->id)
            ->exists();

        if ($alreadyReversed) {
            return back()->with('error', "Jurnal ini sudah pernah dibalik sebelumnya.");
        }

        $reversalLines = [];
        foreach ($original->lines as $line) {
            $reversalLines[] = [
                'account_id' => $line->account_id,
                'debit' => (float) $line->credit,
                'credit' => (float) $line->debit,
            ];
        }

        $reversedEntry = AccountingService::createBalancedEntry(
            'REVERSAL',
            $original->id,
            $reverseDate,
            "Pembalikan Jurnal Pembalik dari #{$original->id}: {$original->memo}",
            $reversalLines,
            auth()->id()
        );

        \App\Models\AuditLog::record('REVERSE_JOURNAL', 'JournalEntry', $reversedEntry->id, ['original_id' => $original->id], [
            'reversed_id' => $reversedEntry->id,
        ]);

        return back()->with('success', "Jurnal #{$original->id} berhasil dibalik dengan Jurnal Pembalik #{$reversedEntry->id}.");
    }

    /**
     * Tutup Buku Periode Akuntansi (Closing Period to Laba Ditahan)
     */
    public function closePeriod(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'closing_notes' => 'nullable|string|max:500',
        ]);

        // Pastikan periode ini belum pernah ditutup
        $existing = \App\Models\AccountingPeriod::where('start_date', $validated['start_date'])
            ->where('end_date', $validated['end_date'])
            ->where('is_closed', true)
            ->first();

        if ($existing) {
            return back()->with('error', "Periode akuntansi untuk rentang tanggal ini sudah ditutup.");
        }

        $period = DB::transaction(function () use ($validated) {
            $startDate = Carbon::parse($validated['start_date'])->startOfDay();
            $endDate = Carbon::parse($validated['end_date'])->endOfDay();

            // Hitung akumulasi pendapatan dan beban
            $revenueLines = JournalLine::whereHas('account', fn($q) => $q->where('type', 'REVENUE'))
                ->whereHas('entry', fn($q) => $q->whereBetween('entry_date', [$startDate, $endDate])->where('source_type', '!=', 'CLOSING_PERIOD'))
                ->get();

            $expenseLines = JournalLine::whereHas('account', fn($q) => $q->where('type', 'EXPENSE'))
                ->whereHas('entry', fn($q) => $q->whereBetween('entry_date', [$startDate, $endDate])->where('source_type', '!=', 'CLOSING_PERIOD'))
                ->get();

            $totalRevenue = (float) $revenueLines->sum('credit') - (float) $revenueLines->sum('debit');
            $totalExpense = (float) $expenseLines->sum('debit') - (float) $expenseLines->sum('credit');
            $netIncome = $totalRevenue - $totalExpense;

            $closingId = (string) \Illuminate\Support\Str::uuid();
            $closingLines = [];

            // Nol-kan seluruh akun Pendapatan (Debit Revenue)
            $groupedRevenue = $revenueLines->groupBy('account_id');
            foreach ($groupedRevenue as $accId => $lines) {
                $bal = (float) $lines->sum('credit') - (float) $lines->sum('debit');
                if ($bal > 0) {
                    $closingLines[] = ['account_id' => $accId, 'debit' => $bal, 'credit' => 0];
                }
            }

            // Nol-kan seluruh akun Beban (Kredit Expense)
            $groupedExpense = $expenseLines->groupBy('account_id');
            foreach ($groupedExpense as $accId => $lines) {
                $bal = (float) $lines->sum('debit') - (float) $lines->sum('credit');
                if ($bal > 0) {
                    $closingLines[] = ['account_id' => $accId, 'debit' => 0, 'credit' => $bal];
                }
            }

            // Pindahkan selisih Laba Bersih ke Laba Ditahan (3-2100)
            $labaDitahanCoaId = AccountingService::getCoaId('3-2100');
            if ($netIncome >= 0) {
                $closingLines[] = ['account_id' => $labaDitahanCoaId, 'debit' => 0, 'credit' => $netIncome];
            } else {
                $closingLines[] = ['account_id' => $labaDitahanCoaId, 'debit' => abs($netIncome), 'credit' => 0];
            }

            if (!empty($closingLines)) {
                AccountingService::createBalancedEntry(
                    'CLOSING_PERIOD',
                    $closingId,
                    $endDate->format('Y-m-d'),
                    "Jurnal Penutup Periode: {$validated['name']} ke Laba Ditahan",
                    $closingLines,
                    auth()->id()
                );
            }

            return \App\Models\AccountingPeriod::create([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_closed' => true,
                'closed_at' => now(),
                'closed_by_id' => auth()->id(),
                'closing_notes' => $validated['closing_notes'],
            ]);
        });

        \App\Models\AuditLog::record('CLOSE_ACCOUNTING_PERIOD', 'AccountingPeriod', $period->id, null, [
            'name' => $period->name,
            'start_date' => $period->start_date,
            'end_date' => $period->end_date,
        ]);

        return back()->with('success', "Periode akuntansi {$period->name} berhasil DITUTUP & DIKUNCI. Seluruh saldo pendapatan dan beban telah dipindahkan ke Laba Ditahan.");
    }

    /**
     * Buka Kembali Periode Akuntansi yang Dikunci (Khusus Pimpinan / SuperAdmin)
     */
    public function reopenPeriod(string $id)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasRole('R-OWN')) {
            abort(403, 'Akses Ditolak: Hanya Pimpinan (R-OWN) yang memiliki wewenang membuka kembali periode akuntansi.');
        }

        $period = \App\Models\AccountingPeriod::findOrFail($id);

        DB::transaction(function () use ($period) {
            // Hapus jurnal penutup yang bersangkutan
            $closingEntries = JournalEntry::where('source_type', 'CLOSING_PERIOD')
                ->whereBetween('entry_date', [$period->start_date, $period->end_date])
                ->get();

            foreach ($closingEntries as $ce) {
                $ce->lines()->delete();
                $ce->delete();
            }

            $period->update([
                'is_closed' => false,
                'closed_at' => null,
                'closed_by_id' => null,
                'closing_notes' => ($period->closing_notes ? $period->closing_notes . " | " : "") . "Dibuka kembali oleh " . auth()->user()->name . " pada " . now()->format('d/m/Y H:i'),
            ]);
        });

        \App\Models\AuditLog::record('REOPEN_ACCOUNTING_PERIOD', 'AccountingPeriod', $period->id, ['is_closed' => true], ['is_closed' => false]);

        return back()->with('success', "Periode akuntansi {$period->name} berhasil DIBUKA KEMBALI. Transaksi kini dapat diposting ulang.");
    }
}
