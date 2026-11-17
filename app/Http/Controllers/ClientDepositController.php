<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientDeposit;
use App\Models\ClientDepositUsage;
use App\Models\ClientInvoice;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ClientDepositController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:finance.view|finance.invoice', only: ['index']),
            new Middleware('permission:finance.invoice|finance.manage', only: ['store', 'deduct']),
        ];
    }

    /**
     * Halaman Buku Besar Deposit Klien (MoM Poin 10)
     */
    public function index(Request $request)
    {
        $clientId = $request->query('client_id');
        $status = $request->query('status');

        $depositsQuery = ClientDeposit::with(['client', 'usages.invoice', 'creator'])->latest('tanggal');
        if ($clientId) {
            $depositsQuery->where('client_id', $clientId);
        }
        if ($status) {
            $depositsQuery->where('status', $status);
        }

        $deposits = $depositsQuery->paginate(15);
        $clients = Client::where('is_active', true)->orderBy('name')->get();

        // Rekap KPI Deposit
        $totalSaldoAktif = ClientDeposit::where('status', 'CONFIRMED')->sum('saldo_sisa');
        $totalDepositMasuk = ClientDeposit::where('status', 'CONFIRMED')->sum('nominal');
        $totalDepositTerpakai = ClientDepositUsage::sum('nominal_terpakai');

        $recentUsages = ClientDepositUsage::with(['client', 'deposit', 'invoice', 'shipment', 'creator'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('finance.deposits_index', compact(
            'deposits',
            'clients',
            'totalSaldoAktif',
            'totalDepositMasuk',
            'totalDepositTerpakai',
            'recentUsages',
            'clientId',
            'status'
        ));
    }

    /**
     * Simpan Setoran Deposit Klien Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|uuid|exists:clients,id',
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric|min:1000',
            'metode_pembayaran' => 'required|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'rekening_tujuan' => 'nullable|string|max:100',
            'nomor_referensi_bank' => 'nullable|string|max:100',
            'catatan' => 'nullable|string|max:500',
            'bukti_bayar' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $buktiPath = null;
        if ($request->hasFile('bukti_bayar')) {
            $buktiPath = $request->file('bukti_bayar')->store('deposits', 'public');
        }

        $deposit = ClientDeposit::create([
            'deposit_no' => ClientDeposit::generateDepositNumber(),
            'client_id' => $validated['client_id'],
            'tanggal' => $validated['tanggal'],
            'nominal' => $validated['nominal'],
            'saldo_sisa' => $validated['nominal'],
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'bank_name' => $validated['bank_name'] ?? null,
            'rekening_tujuan' => $validated['rekening_tujuan'] ?? null,
            'nomor_referensi_bank' => $validated['nomor_referensi_bank'] ?? null,
            'bukti_bayar_path' => $buktiPath,
            'status' => 'CONFIRMED',
            'catatan' => $validated['catatan'] ?? null,
            'created_by_user_id' => auth()->id(),
        ]);

        return back()->with('success', "Deposit {$deposit->deposit_no} sebesar Rp " . number_format($deposit->nominal, 0, ',', '.') . " berhasil dicatat.");
    }

    /**
     * Potong Saldo Deposit Klien untuk Membayar Tagihan Invoice / Talangan Biaya
     */
    public function deduct(Request $request)
    {
        $validated = $request->validate([
            'client_deposit_id' => 'required|uuid|exists:client_deposits,id',
            'nominal' => 'required|numeric|min:1',
            'invoice_id' => 'nullable|uuid|exists:client_invoices,id',
            'shipment_id' => 'nullable|uuid|exists:shipments,id',
            'keterangan' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $deposit = ClientDeposit::lockForUpdate()->findOrFail($validated['client_deposit_id']);

            if ($deposit->saldo_sisa < $validated['nominal']) {
                abort(422, "Saldo sisa deposit ({$deposit->deposit_no}) tidak mencukupi. Sisa saldo: Rp " . number_format($deposit->saldo_sisa, 0, ',', '.'));
            }

            $saldoSebelum = $deposit->saldo_sisa;
            $saldoSesudah = $saldoSebelum - $validated['nominal'];

            $deposit->update([
                'saldo_sisa' => $saldoSesudah,
                'status' => ($saldoSesudah <= 0) ? 'DEPLETED' : 'CONFIRMED',
            ]);

            ClientDepositUsage::create([
                'client_deposit_id' => $deposit->id,
                'client_id' => $deposit->client_id,
                'client_invoice_id' => $validated['invoice_id'] ?? null,
                'shipment_id' => $validated['shipment_id'] ?? null,
                'tanggal' => now(),
                'nominal_terpakai' => $validated['nominal'],
                'saldo_sebelumnya' => $saldoSebelum,
                'saldo_setelahnya' => $saldoSesudah,
                'keterangan' => $validated['keterangan'],
                'created_by_user_id' => auth()->id(),
            ]);

            // Jika dipotongkan ke Invoice: kurangi sisa_tagihan_idr invoice
            if (!empty($validated['invoice_id'])) {
                $inv = ClientInvoice::findOrFail($validated['invoice_id']);
                $newDepositDeducted = (float) $inv->deposit_deducted_idr + $validated['nominal'];
                $newSisa = max(0, (float) $inv->total_tagihan_idr - $newDepositDeducted);
                
                $inv->update([
                    'deposit_deducted_idr' => $newDepositDeducted,
                    'sisa_tagihan_idr' => $newSisa,
                    'status' => ($newSisa <= 0) ? 'PAID' : 'PARTIALLY_PAID',
                    'paid_at' => ($newSisa <= 0) ? now() : $inv->paid_at,
                ]);
            }
        });

        return back()->with('success', 'Saldo deposit berhasil dipotong sebesar Rp ' . number_format($validated['nominal'], 0, ',', '.') . '.');
    }
}
