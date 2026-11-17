<?php

namespace App\Http\Controllers;

use App\Models\ClientInvoice;
use App\Models\Quotation;
use App\Models\Shipment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class PdfController extends Controller implements HasMiddleware
{
    /**
     * Daftarkan middleware proteksi RBAC level Controller
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:document.view', only: ['pib', 'peb']),
            new Middleware('permission:finance.view|finance.invoice', only: ['invoice']),
            new Middleware('permission:crm.view', only: ['quotation']),
            new Middleware('permission:hrm.view|finance.view', only: ['form1721A1']),
            new Middleware('permission:traffic.view|traffic.process|traffic.manage|document.view', only: ['suratJalan']),
        ];
    }
    /**
     * Cetak / Download PIB BC 2.0 (Hak Akses: document.view)
     */
    public function pib(string $id, Request $request)
    {
        Gate::authorize('document.view');

        $relations = ['client', 'ceisaDocument', 'items.customsDocuments', 'itemCustomsDocuments', 'documents'];
        $shipment = Shipment::with($relations)->find($id);

        if (!$shipment) {
            $shipment = Shipment::with($relations)
                ->where('ceisa_document_id', $id)
                ->first();
        }

        if (!$shipment) {
            abort(404, 'Data pengapalan / dokumen pabean tidak ditemukan.');
        }

        if ($request->has('download')) {
            $pdf = Pdf::loadView('pdf.pib', compact('shipment'));
            return $pdf->download("PIB-BC20-{$shipment->shipment_number}.pdf");
        }

        return view('pdf.pib', compact('shipment'));
    }

    /**
     * Cetak / Download PEB BC 3.0 (Hak Akses: document.view)
     */
    public function peb(string $id, Request $request)
    {
        Gate::authorize('document.view');

        $relations = ['client', 'ceisaDocument', 'items.customsDocuments', 'itemCustomsDocuments', 'documents'];
        $shipment = Shipment::with($relations)->find($id);

        if (!$shipment) {
            $shipment = Shipment::with($relations)
                ->where('ceisa_document_id', $id)
                ->first();
        }

        if (!$shipment) {
            abort(404, 'Data pengapalan / dokumen pabean tidak ditemukan.');
        }

        if ($request->has('download')) {
            $pdf = Pdf::loadView('pdf.peb', compact('shipment'));
            return $pdf->download("PEB-BC30-{$shipment->shipment_number}.pdf");
        }

        return view('pdf.peb', compact('shipment'));
    }

    /**
     * Cetak / Download Invoice Klien P19 (Hak Akses: finance.view)
     */
    public function invoice(string $id, Request $request)
    {
        Gate::authorize('finance.view');

        $invoice = ClientInvoice::with(['client', 'shipment.ceisaDocument'])->findOrFail($id);

        if ($request->has('download')) {
            $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
            return $pdf->download("INVOICE-{$invoice->invoice_number}.pdf");
        }

        return view('pdf.invoice', compact('invoice'));
    }

    /**
     * Cetak / Download Quotation Penawaran Harga (Hak Akses: crm.view)
     */
    public function quotation(string $id, Request $request)
    {
        Gate::authorize('crm.view');

        $quotation = Quotation::with('client')->findOrFail($id);

        if ($request->has('download')) {
            $pdf = Pdf::loadView('pdf.quotation', compact('quotation'));
            return $pdf->download("QUOTATION-{$quotation->quotation_number}.pdf");
        }

        return view('pdf.quotation', compact('quotation'));
    }

    /**
     * Cetak / Download Surat Permohonan Rilis DO (Hak Akses: do.view)
     */
    public function doRequest(string $id, Request $request)
    {
        Gate::authorize('do.view');

        $shipment = Shipment::with(['client', 'shippingVendor'])->findOrFail($id);

        if ($request->has('download')) {
            $pdf = Pdf::loadView('pdf.do_request', compact('shipment'));
            return $pdf->download("DO-REQUEST-{$shipment->shipment_number}.pdf");
        }

        return view('pdf.do_request', compact('shipment'));
    }

    /**
     * Cetak / Download Billing MPN G3 / SSPCP (Hak Akses: document.view)
     */
    public function billingMpn(string $id, Request $request)
    {
        Gate::authorize('document.view');

        $shipment = Shipment::with(['client', 'ceisaDocument'])->findOrFail($id);

        if ($request->has('download')) {
            $pdf = Pdf::loadView('pdf.billing_mpn', compact('shipment'));
            return $pdf->download("BILLING-MPN-{$shipment->shipment_number}.pdf");
        }

        return view('pdf.billing_mpn', compact('shipment'));
    }

    /**
     * Cetak / Download Bukti Potong PPh 21 Formulir 1721-A1 (Hak Akses: hrm.manage / HR / Staf Pemilik)
     */
    public function form1721A1(string $employeeId, Request $request, \App\Services\Hrm\AnnualTaxReconciliationService $reconciliationService)
    {
        $employee = \App\Models\Employee::findOrFail($employeeId);
        $year = (int) $request->query('year', date('Y'));

        $isAuthorized = auth()->check() && (
            auth()->user()->can('hrm.manage') ||
            auth()->user()->hasRole('R-OWN') ||
            auth()->user()->employee?->id === $employee->id
        );

        if (!$isAuthorized) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk mencetak Bukti Potong 1721-A1 pegawai ini.');
        }

        $reconciliation = $reconciliationService->reconcileEmployee($employee, $year);

        if ($request->has('download')) {
            $pdf = Pdf::loadView('pdf.form_1721_a1', compact('reconciliation'));
            return $pdf->download("FORM-1721-A1-{$reconciliation['year']}-{$employee->nik}.pdf");
        }

        return view('pdf.form_1721_a1', compact('reconciliation'));
    }

    /**
     * Cetak / Download Surat Jalan (4 Template Sesuai MoM Poin 4)
     * Template:
     * 1. fcl (Pengiriman Kontainer FCL Pelabuhan ke Gudang Klien)
     * 2. lcl (Kargo LCL / Udara / CFS)
     * 3. inter_factory (Transfer Antar Pabrik Kawasan Berikat BC 2.7 / BC 4.0)
     * 4. empty_depo (Pengembalian Kontainer Kosong ke Depo Pelayaran)
     */
    public function suratJalan(string $id, Request $request)
    {
        $shipment = Shipment::with(['client', 'shippingVendor', 'ceisaDocument'])->findOrFail($id);

        $template = strtolower($request->query('template', 'fcl'));
        $viewMap = [
            'fcl' => 'pdf.surat_jalan_fcl',
            'template_1_fcl' => 'pdf.surat_jalan_fcl',
            'lcl' => 'pdf.surat_jalan_lcl',
            'template_2_lcl' => 'pdf.surat_jalan_lcl',
            'inter_factory' => 'pdf.surat_jalan_inter_factory',
            'template_3_inter_factory' => 'pdf.surat_jalan_inter_factory',
            'empty_depo' => 'pdf.surat_jalan_empty_depo',
            'template_4_empty_depo' => 'pdf.surat_jalan_empty_depo',
        ];

        $viewName = $viewMap[$template] ?? 'pdf.surat_jalan_fcl';
        $docNumber = $shipment->surat_jalan_number ?: $shipment->project_no;

        if ($request->has('download')) {
            $pdf = Pdf::loadView($viewName, compact('shipment'));
            return $pdf->download("SURAT-JALAN-{$docNumber}.pdf");
        }

        return view($viewName, compact('shipment'));
    }
}
