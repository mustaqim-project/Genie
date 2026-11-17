<?php

namespace App\Http\Controllers;

use App\Models\ClientInvoice;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Services\DivisionMailService;
use Illuminate\Http\Request;

class EmailPreviewController extends Controller
{
    protected DivisionMailService $mailService;

    public function __construct(DivisionMailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * Preview Template Email 1: Marketing (Quotation Penawaran Harga)
     */
    public function previewQuotation(string $id)
    {
        $quotation = Quotation::with('client')->findOrFail($id);
        return view('emails.marketing_quotation', compact('quotation'));
    }

    /**
     * Kirim Email Resmi Penawaran Harga (Divisi Marketing)
     */
    public function sendQuotation(Request $request, string $id)
    {
        $quotation = Quotation::with('client')->findOrFail($id);
        $recipient = $request->input('recipient_email', $quotation->client?->email);
        $notes = $request->input('custom_notes');

        $result = $this->mailService->sendQuotation($quotation, $recipient, $notes);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Preview Template Email 2: Finance (Invoice P19 & Rekening Resmi)
     */
    public function previewInvoice(string $id)
    {
        $invoice = ClientInvoice::with(['client', 'shipment'])->findOrFail($id);
        return view('emails.finance_invoice', compact('invoice'));
    }

    /**
     * Kirim Email Resmi Tagihan Invoice P19 (Divisi Finance)
     */
    public function sendInvoice(Request $request, string $id)
    {
        $invoice = ClientInvoice::with(['client', 'shipment'])->findOrFail($id);
        $recipient = $request->input('recipient_email', $invoice->client?->email);
        $notes = $request->input('custom_notes');

        $result = $this->mailService->sendInvoice($invoice, $recipient, $notes);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Preview Template Email 3: Dokumen Pabean (Draft PIB/PEB & Billing)
     */
    public function previewDocument(string $id)
    {
        $shipment = Shipment::with('client')->findOrFail($id);
        return view('emails.document_pabean_draft', compact('shipment'));
    }

    /**
     * Kirim Email Resmi Review Draft PIB/PEB (Divisi Dokumen)
     */
    public function sendDocumentDraft(Request $request, string $id)
    {
        $shipment = Shipment::with('client')->findOrFail($id);
        $recipient = $request->input('recipient_email', $shipment->client?->email);
        $notes = $request->input('custom_notes');

        $result = $this->mailService->sendDocumentDraft($shipment, $recipient, $notes);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Preview Template Email 4: Traffic & Lapangan (ETA, Tracking, SPPB)
     */
    public function previewTraffic(string $id)
    {
        $shipment = Shipment::with('client')->findOrFail($id);
        return view('emails.traffic_eta_notice', compact('shipment'));
    }

    /**
     * Kirim Email Resmi Update Status Kargo & ETA (Divisi Traffic)
     */
    public function sendTrafficEta(Request $request, string $id)
    {
        $shipment = Shipment::with('client')->findOrFail($id);
        $recipient = $request->input('recipient_email', $shipment->client?->email);
        $notes = $request->input('custom_notes');
        $template = $request->input('surat_jalan_template', 'fcl');

        $result = $this->mailService->sendTrafficEta($shipment, $recipient, $notes, $template);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}