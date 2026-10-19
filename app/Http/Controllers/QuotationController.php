<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QuotationController extends Controller
{
    /**
     * Daftar Penawaran Harga (CRM)
     * Hak Akses: crm.view
     */
    public function index(Request $request)
    {
        Gate::authorize('crm.view');

        $query = Quotation::with('client');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $quotations = $query->latest()->paginate(10);
        $clients = Client::orderBy('name')->get();

        return view('crm.quotations_index', compact('quotations', 'clients'));
    }

    /**
     * Buat Penawaran Harga Baru (CRM)
     * Hak Akses: crm.deal
     */
    public function store(Request $request)
    {
        Gate::authorize('crm.deal');

        $validated = $request->validate([
            'client_id' => 'required|uuid|exists:clients,id',
            'origin_port' => 'required|string|max:100',
            'destination_port' => 'required|string|max:100',
            'total_estimate' => 'required|numeric|min:0',
            'valid_until' => 'nullable|date',
            'terms' => 'nullable|string',
        ]);

        $nextNum = Quotation::count() + 1;
        $quotationNumber = 'QUO-' . date('Ym') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $quotation = Quotation::create(array_merge($validated, [
            'quotation_number' => $quotationNumber,
            'issue_date' => now(),
            'status' => 'SENT',
        ]));

        return redirect()->route('crm.quotations.index')
            ->with('success', "Penawaran harga {$quotation->quotation_number} berhasil diterbitkan.");
    }

    /**
     * Update Status Penawaran (ACCEPTED / REJECTED)
     * Hak Akses: crm.deal
     */
    public function updateStatus(string $id, Request $request)
    {
        Gate::authorize('crm.deal');

        $request->validate([
            'status' => 'required|in:DRAFT,SENT,ACCEPTED,REJECTED,EXPIRED',
        ]);

        $quotation = Quotation::findOrFail($id);
        $quotation->update(['status' => $request->status]);

        return back()->with('success', "Status penawaran diperbarui menjadi {$request->status}.");
    }
}
