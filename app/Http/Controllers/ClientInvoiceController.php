<?php

namespace App\Http\Controllers;

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
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$s}%"));
            });
        }

        $invoices = $query->latest()->paginate(10);

        return view('finance.invoices_index', compact('invoices'));
    }

    /**
     * Detail Invoice Klien P19
     * Hak Akses: finance.view
     */
    public function show(string $id)
    {
        Gate::authorize('finance.view');

        $invoice = ClientInvoice::with(['client', 'shipment.ceisaDocument', 'shipment.items'])->findOrFail($id);

        return view('finance.invoices_show', compact('invoice'));
    }

    /**
     * Pelunasan / Pencatatan Pembayaran Invoice Klien
     * Hak Akses: finance.pay
     */
    public function markAsPaid(string $id, Request $request)
    {
        Gate::authorize('finance.pay');

        $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $invoice = ClientInvoice::findOrFail($id);
        $invoice->update([
            'status' => 'PAID',
            'paid_at' => $request->payment_date,
            'notes' => ($invoice->notes ? $invoice->notes . ' | ' : '') . "Lunas via {$request->payment_method}: {$request->notes}",
        ]);

        return back()->with('success', "Invoice {$invoice->invoice_number} berhasil ditandai LUNAS.");
    }
}
