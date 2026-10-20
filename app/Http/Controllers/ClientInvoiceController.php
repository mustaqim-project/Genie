<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientInvoice;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClientInvoiceController extends Controller
{
    /**
     * Daftar Seluruh Tagihan Invoice Klien (Finance)
     * Hak Akses: finance.view
     */
    public function index(Request $request)
    {
        Gate::authorize('finance.view');

        $query = ClientInvoice::with(['client', 'shipment']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_no', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('shipment', fn($sq) => $sq->where('shipment_number', 'like', "%{$s}%")->orWhere('bl_number', 'like', "%{$s}%"));
            });
        }

        $invoices = $query->latest()->paginate(10);

        $stats = [
            'total' => ClientInvoice::count(),
            'paid' => ClientInvoice::where('status', 'PAID')->count(),
            'unpaid' => ClientInvoice::where('status', 'UNPAID')->count(),
            'total_amount' => ClientInvoice::sum('total_tagihan_idr'),
        ];

        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $shipments = Shipment::whereNull('closed_at')->latest()->take(50)->get();

        return view('finance.invoices_index', compact('invoices', 'stats', 'clients', 'shipments'));
    }

    /**
     * Terbitkan Draf Invoice Penagihan Klien (AR) Manual Baru
     * Hak Akses: finance.invoice / finance.manage (ClientInvoicePolicy::create)
     */
    public function store(Request $request)
    {
        Gate::authorize('create', ClientInvoice::class);

        $validated = $request->validate([
            'client_id' => 'required|uuid|exists:clients,id',
            'shipment_id' => 'nullable|uuid|exists:shipments,id',
            'tanggal' => 'required|date',
            'jatuh_tempo' => 'required|date|after_or_equal:tanggal',
            'talangan_pabean_idr' => 'nullable|numeric|min:0',
            'biaya_pelayaran_idr' => 'nullable|numeric|min:0',
            'jasa_ppjk_idr' => 'nullable|numeric|min:0',
            'biaya_trucking_idr' => 'nullable|numeric|min:0',
            'ppn_jasa_idr' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string|max:500',
        ]);

        $talangan = (float) ($validated['talangan_pabean_idr'] ?? 0);
        $pelayaran = (float) ($validated['biaya_pelayaran_idr'] ?? 0);
        $jasaPpjk = (float) ($validated['jasa_ppjk_idr'] ?? 0);
        $trucking = (float) ($validated['biaya_trucking_idr'] ?? 0);

        $subtotal = $talangan + $pelayaran + $jasaPpjk + $trucking;

        // PPN Jasa 1.1% default sesuai PMK 71/2022 jika tidak diinput khusus
        $dppJasa = $jasaPpjk + $trucking;
        $ppnJasa = isset($validated['ppn_jasa_idr']) && $validated['ppn_jasa_idr'] !== null && $validated['ppn_jasa_idr'] !== ''
            ? (float) $validated['ppn_jasa_idr']
            : round($dppJasa * 0.011, 2);

        $totalTagihan = $subtotal + $ppnJasa;

        $invoice = ClientInvoice::create([
            'invoice_no' => ClientInvoice::generateInvoiceNo(),
            'shipment_id' => $validated['shipment_id'] ?? null,
            'client_id' => $validated['client_id'],
            'tanggal' => $validated['tanggal'],
            'jatuh_tempo' => $validated['jatuh_tempo'],
            'talangan_pabean_idr' => $talangan,
            'biaya_pelayaran_idr' => $pelayaran,
            'jasa_ppjk_idr' => $jasaPpjk,
            'biaya_trucking_idr' => $trucking,
            'subtotal_idr' => $subtotal,
            'ppn_jasa_idr' => $ppnJasa,
            'total_tagihan_idr' => $totalTagihan,
            'status' => 'UNPAID',
            'catatan_pembayaran' => $validated['catatan'] ?? null,
            'created_by_user_id' => auth()->id(),
        ]);

        // Auto-journaling Piutang Usaha
        try {
            $accountingService = app(\App\Services\Accounting\AccountingService::class);
            $accountingService->recordClientInvoiceIssued($invoice, auth()->id());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal auto-journal invoice manual: " . $e->getMessage());
        }

        return redirect()->route('finance.invoices.show', $invoice->id)
            ->with('success', "Invoice Penagihan Klien {$invoice->invoice_no} berhasil diterbitkan.");
    }

    /**
     * Detail Invoice Klien P19
     * Hak Akses: finance.view
     */
    public function show(string $id)
    {
        Gate::authorize('finance.view');

        $invoice = ClientInvoice::with(['client', 'shipment.ceisaDocument', 'shipment.items'])->findOrFail($id);
        $bankAccounts = \App\Models\CashBankAccount::all();

        return view('finance.invoices_show', compact('invoice', 'bankAccounts'));
    }

    /**
     * Pelunasan / Pencatatan Pembayaran Invoice Klien & Posting Jurnal Otomatis
     * Hak Akses: finance.pay
     */
    public function markAsPaid(string $id, Request $request)
    {
        Gate::authorize('finance.pay');

        $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string',
            'account_id' => 'nullable|exists:cash_bank_accounts,id',
            'notes' => 'nullable|string',
        ]);

        $invoice = ClientInvoice::findOrFail($id);

        // 1. Tentukan Rekening Penerimaan Kas/Bank
        $account = null;
        if ($request->filled('account_id')) {
            $account = \App\Models\CashBankAccount::find($request->account_id);
        }
        
        if (!$account && $request->filled('payment_method')) {
            $methodLower = strtolower($request->payment_method);
            if (str_contains($methodLower, 'mandiri')) {
                $account = \App\Models\CashBankAccount::where('bank', 'like', '%Mandiri%')->first();
            } elseif (str_contains($methodLower, 'kas') || str_contains($methodLower, 'tunai')) {
                $account = \App\Models\CashBankAccount::where('name', 'like', '%Kas%')->first();
            } else {
                $account = \App\Models\CashBankAccount::where('bank', 'like', '%BCA%')->first();
            }
        }

        if (!$account) {
            $account = \App\Models\CashBankAccount::first();
        }

        $methodName = $account ? "{$account->name} ({$account->bank})" : ($request->payment_method ?? 'Bank Transfer');
        $keterangan = "Lunas via {$methodName}" . ($request->notes ? " (Ref: {$request->notes})" : "");

        $invoice->update([
            'status' => 'PAID',
            'paid_at' => $request->payment_date,
            'catatan_pembayaran' => $invoice->catatan_pembayaran ? ($invoice->catatan_pembayaran . ' | ' . $keterangan) : $keterangan,
        ]);

        $amount = (float) $invoice->total_tagihan_idr;

        // 2. Catat Mutasi Kas Masuk ke Buku Bank (Cash Flow In)
        if ($account) {
            \App\Models\CashBankTransaction::create([
                'account_id' => $account->id,
                'trx_date' => $request->payment_date,
                'amount' => abs($amount),
                'description' => "Penerimaan Pelunasan Invoice {$invoice->invoice_no} a/n " . ($invoice->client?->name ?? 'Klien'),
                'reconciled' => true,
                'ref_type' => 'CLIENT_INVOICE',
                'ref_id' => $invoice->id,
            ]);
        }

        // 3. Engine Auto-Jurnal: Posting Double-Entry (DR Bank, CR Piutang Usaha)
        $accountingService = app(\App\Services\Accounting\AccountingService::class);
        $accountingService->recordClientInvoiceIssued($invoice, auth()->id());
        $accountingService->recordClientInvoicePayment($invoice, $amount, $account, $request->payment_date, auth()->id());

        return back()->with('success', "Invoice {$invoice->invoice_number} berhasil ditandai LUNAS. Saldo rekening kas/bank dan buku jurnal umum telah diperbarui otomatis.");
    }
}
