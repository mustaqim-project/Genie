<?php

namespace App\Http\Controllers;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CashBankController extends Controller
{
    /**
     * Dashboard Buku Kas & Bank Real-Time
     * Hak Akses: finance.view
     */
    public function index(Request $request)
    {
        Gate::authorize('finance.view');

        $accounts = CashBankAccount::with('transactions')->get();
        $totalLiquidity = $accounts->sum(fn($acc) => $acc->current_balance);

        // Rekapitulasi Arus Kas Bulan Berjalan
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $totalInThisMonth = (float) CashBankTransaction::where('amount', '>', 0)
            ->whereBetween('trx_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $totalOutThisMonth = (float) abs(CashBankTransaction::where('amount', '<', 0)
            ->whereBetween('trx_date', [$startOfMonth, $endOfMonth])
            ->sum('amount'));

        // Query Mutasi Transaksi
        $query = CashBankTransaction::with('account');

        if ($request->filled('account_id') && $request->account_id !== 'ALL') {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('direction')) {
            if ($request->direction === 'IN') {
                $query->where('amount', '>', 0);
            } elseif ($request->direction === 'OUT') {
                $query->where('amount', '<', 0);
            }
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('ref_type', 'like', "%{$s}%");
            });
        }

        $transactions = $query->orderBy('trx_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('finance.cash_bank_index', compact(
            'accounts',
            'totalLiquidity',
            'totalInThisMonth',
            'totalOutThisMonth',
            'transactions'
        ));
    }

    /**
     * Tambah Rekening Kas / Bank Baru
     * Hak Akses: finance.manage
     */
    public function storeAccount(Request $request)
    {
        Gate::authorize('finance.manage');

        $request->validate([
            'name' => 'required|string|max:100',
            'bank' => 'required|string|max:100',
            'account_no' => 'required|string|max:50|unique:cash_bank_accounts,account_no',
            'initial_balance' => 'nullable|numeric|min:0',
        ]);

        $account = CashBankAccount::create([
            'name' => $request->name,
            'bank' => $request->bank,
            'account_no' => $request->account_no,
        ]);

        if ($request->filled('initial_balance') && (float) $request->initial_balance > 0) {
            CashBankTransaction::create([
                'account_id' => $account->id,
                'trx_date' => now(),
                'amount' => (float) $request->initial_balance,
                'description' => 'Saldo awal pembukaan rekening ' . $account->name,
                'reconciled' => true,
                'ref_type' => 'OPENING_BALANCE',
            ]);
        }

        return back()->with('success', "Rekening {$account->name} ({$account->account_no}) berhasil ditambahkan.");
    }

    /**
     * Catat Mutasi Kas Masuk / Keluar Operasional Manual
     * Hak Akses: finance.manage
     */
    public function storeTransaction(Request $request)
    {
        Gate::authorize('finance.manage');

        $request->validate([
            'account_id' => 'required|exists:cash_bank_accounts,id',
            'type' => 'required|in:IN,OUT',
            'amount' => 'required|numeric|min:1',
            'trx_date' => 'required|date',
            'description' => 'required|string|max:255',
            'ref_type' => 'nullable|string|max:50',
        ]);

        $amount = (float) $request->amount;
        $finalAmount = $request->type === 'OUT' ? (-1 * abs($amount)) : abs($amount);

        CashBankTransaction::create([
            'account_id' => $request->account_id,
            'trx_date' => $request->trx_date,
            'amount' => $finalAmount,
            'description' => $request->description,
            'reconciled' => false,
            'ref_type' => $request->ref_type ?? 'MANUAL_CASH',
        ]);

        $typeLabel = $request->type === 'IN' ? 'Kas Masuk' : 'Kas Keluar';
        return back()->with('success', "Mutasi {$typeLabel} sebesar Rp " . number_format($amount, 0, ',', '.') . " berhasil dicatat.");
    }

    /**
     * Pindah Buku / Transfer Antar-Rekening (Inter-Account Transfer)
     * Hak Akses: finance.manage
     */
    public function transfer(Request $request)
    {
        Gate::authorize('finance.manage');

        $request->validate([
            'from_account_id' => 'required|exists:cash_bank_accounts,id|different:to_account_id',
            'to_account_id' => 'required|exists:cash_bank_accounts,id',
            'amount' => 'required|numeric|min:1',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $fromAcc = CashBankAccount::findOrFail($request->from_account_id);
        $toAcc = CashBankAccount::findOrFail($request->to_account_id);
        $amount = (float) $request->amount;

        // Cek kecukupan saldo rekening asal
        if ($fromAcc->current_balance < $amount) {
            return back()->with('error', "Saldo rekening asal ({$fromAcc->name}) tidak mencukupi untuk transfer sebesar Rp " . number_format($amount, 0, ',', '.') . ". Saldo saat ini: Rp " . number_format($fromAcc->current_balance, 0, ',', '.'));
        }

        DB::transaction(function () use ($fromAcc, $toAcc, $amount, $request) {
            $refId = (string) \Illuminate\Support\Str::uuid();
            $memo = $request->notes ? " ({$request->notes})" : '';

            // 1. Pengeluaran di Rekening Asal
            CashBankTransaction::create([
                'account_id' => $fromAcc->id,
                'trx_date' => $request->transfer_date,
                'amount' => -1 * abs($amount),
                'description' => "Pindah buku ke {$toAcc->name}{$memo}",
                'reconciled' => true,
                'ref_type' => 'TRANSFER_OUT',
                'ref_id' => $refId,
            ]);

            // 2. Penerimaan di Rekening Tujuan
            CashBankTransaction::create([
                'account_id' => $toAcc->id,
                'trx_date' => $request->transfer_date,
                'amount' => abs($amount),
                'description' => "Pindah buku dari {$fromAcc->name}{$memo}",
                'reconciled' => true,
                'ref_type' => 'TRANSFER_IN',
                'ref_id' => $refId,
            ]);
        });

        return back()->with('success', "Pindah buku sebesar Rp " . number_format($amount, 0, ',', '.') . " dari {$fromAcc->name} ke {$toAcc->name} berhasil dibukukan.");
    }

    /**
     * Rekonsiliasi Bank (Pencocokan Rekening Koran)
     * Hak Akses: finance.manage
     */
    public function reconcile(string $id)
    {
        Gate::authorize('finance.manage');

        $trx = CashBankTransaction::findOrFail($id);
        $trx->update([
            'reconciled' => !$trx->reconciled,
        ]);

        $status = $trx->reconciled ? 'Telah Direkonsiliasi' : 'Dibatalkan Rekonsiliasi';
        return back()->with('success', "Status transaksi mutasi berhasil diubah: {$status}.");
    }
}
