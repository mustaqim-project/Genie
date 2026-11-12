<?php

namespace App\Http\Controllers;

use App\Models\CeisaApiLog;
use App\Models\CeisaDocument;
use App\Services\Ceisa\CeisaDocumentService;
use App\Services\Ceisa\DocumentBC20Service;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BC20Controller extends Controller implements HasMiddleware
{
    protected CeisaDocumentService $documentService;
    protected \App\Services\Ceisa\CeisaKursService $kursService;
    protected \App\Services\Shipment\ShipmentWorkflowService $workflowService;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:document.view', only: ['index', 'show']),
            new Middleware('permission:document.create', only: ['create', 'store']),
            new Middleware('permission:document.edit|document.edit_after_ceisa', only: ['edit', 'update']),
            new Middleware('permission:document.edit_after_ceisa', only: ['reopenDraft']),
            new Middleware('permission:ceisa.submit', only: ['submit', 'checkSchema', 'checkStatus', 'fetchResponse']),
        ];
    }

    public function __construct(
        CeisaDocumentService $documentService,
        \App\Services\Ceisa\CeisaKursService $kursService,
        \App\Services\Shipment\ShipmentWorkflowService $workflowService
    ) {
        $this->documentService = $documentService;
        $this->kursService = $kursService;
        $this->workflowService = $workflowService;
    }

    public function index(Request $request)
    {
        $query = CeisaDocument::where('jenis_dokumen', '20');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_aju', 'like', "%{$search}%")
                    ->orWhere('nomor_daftar', 'like', "%{$search}%")
                    ->orWhere('nama_mitra_luar_negeri', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $allDocs = $query->latest()->get();

        // Hitung statistik Lartas produk & dokumen
        $lartasDocsCount = 0;
        $lartasProductsCount = 0;
        $nonLartasDocsCount = 0;

        foreach ($allDocs as $doc) {
            $info = $doc->lartas_info;
            if ($info['has_lartas']) {
                $lartasDocsCount++;
                $lartasProductsCount += $info['lartas_count'];
            } else {
                $nonLartasDocsCount++;
            }
        }

        // Filter berdasarkan status Lartas
        if ($request->filled('lartas')) {
            $filterLartas = $request->lartas;
            if ($filterLartas === 'yes' || $filterLartas === '1') {
                $allDocs = $allDocs->filter(fn($d) => $d->lartas_info['has_lartas'])->values();
            } elseif ($filterLartas === 'no' || $filterLartas === '0') {
                $allDocs = $allDocs->filter(fn($d) => !$d->lartas_info['has_lartas'])->values();
            }
        }

        // Paginate collection
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10;
        $currentPageItems = $allDocs->slice(($page - 1) * $perPage, $perPage)->values();
        $documents = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $allDocs->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $stats = [
            'total' => CeisaDocument::where('jenis_dokumen', '20')->count(),
            'draft' => CeisaDocument::where('jenis_dokumen', '20')->where('status', 'DRAFT')->count(),
            'billing' => CeisaDocument::where('jenis_dokumen', '20')->where('status', 'BILLING')->count(),
            'sppb' => CeisaDocument::where('jenis_dokumen', '20')->where('status', 'SPPB')->count(),
            'lartas_docs' => $lartasDocsCount,
            'lartas_products' => $lartasProductsCount,
            'non_lartas_docs' => $nonLartasDocsCount,
        ];

        return view('bc20.index', compact('documents', 'stats'));
    }

    public function create(Request $request)
    {
        $defaultNomorAju = DocumentBC20Service::generateNomorAju();
        $kantorList = config('ceisa.kantor_pabean', []);

        $shipments = \App\Models\Shipment::with(['client', 'items'])
            ->where('jenis_layanan', 'IMPORT_BC20')
            ->latest()
            ->take(20)
            ->get();

        $selectedShipment = null;
        if ($request->filled('shipment_id')) {
            $selectedShipment = \App\Models\Shipment::with(['client', 'items'])->find($request->shipment_id);
        }

        $activeValuta = $selectedShipment->currency ?? 'USD';
        $activeKurs = $this->kursService->getActiveKurs($activeValuta);

        return view('bc20.create', compact('defaultNomorAju', 'kantorList', 'shipments', 'selectedShipment', 'activeKurs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'nullable|uuid|exists:shipments,id',
            'kode_kantor' => 'required|string',
            'ndbm_kurs' => 'required|numeric',
            'kode_valuta' => 'required|string',
            'fob' => 'required|numeric',
            'freight' => 'required|numeric',
            'asuransi' => 'required|numeric',
            'bruto' => 'required|numeric',
            'netto' => 'required|numeric',
            'supplier_nama' => 'required|string',
            'supplier_negara' => 'required|string',
            'invoice_no' => 'required|string',
            'invoice_date' => 'required|date',
            'nama_sarana_pengangkut' => 'required|string',
            'pelabuhan_muat' => 'required|string',
            'pelabuhan_bongkar' => 'required|string',
            'nomor_bc11' => 'nullable|string',
            'tanggal_bc11' => 'nullable|date',
            'pos_bc11' => 'nullable|string',
            'subpos_bc11' => 'nullable|string',
            'kode_tps' => 'nullable|string',
            'tanggal_tiba' => 'nullable|date',
            'barang' => 'required|array|min:1',
        ]);

        $nomorAju = DocumentBC20Service::generateNomorAju($validated['kode_kantor']);
        $validated['nomor_aju'] = $nomorAju;

        // Susun Payload CEISA
        $payload = DocumentBC20Service::buildPayload($validated);

        $cif = $payload['header']['cif'];
        $cifRupiah = $payload['header']['cifRupiah'];
        $kantorName = config('ceisa.kantor_pabean.' . $validated['kode_kantor'], 'KPU Tanjung Priok');

        $doc = CeisaDocument::create([
            'nomor_aju' => $nomorAju,
            'jenis_dokumen' => '20',
            'kode_kantor' => $validated['kode_kantor'],
            'nama_kantor' => $kantorName,
            'status' => 'DRAFT',
            'status_description' => 'Draft dokumen pabean BC 2.0 (PIB) siap diajukan ke CEISA 4.0 DJBC.',
            'kode_valuta' => $validated['kode_valuta'],
            'ndbm_kurs' => $validated['ndbm_kurs'],
            'total_nilai' => $cif,
            'total_nilai_idr' => $cifRupiah,
            'total_bruto' => $validated['bruto'],
            'total_netto' => $validated['netto'],
            'total_kemasan' => $request->jumlah_kemasan ?? 100,
            'total_kontainer' => 1,
            'total_barang' => count($validated['barang']),
            'nama_perusahaan_lokal' => config('ceisa.company.name'),
            'npwp_perusahaan_lokal' => config('ceisa.company.npwp'),
            'nama_mitra_luar_negeri' => $validated['supplier_nama'],
            'negara_mitra_luar_negeri' => $validated['supplier_negara'],
            'nomor_bc11' => $validated['nomor_bc11'] ?? null,
            'tanggal_bc11' => $validated['tanggal_bc11'] ?? null,
            'pos_bc11' => $validated['pos_bc11'] ?? null,
            'subpos_bc11' => $validated['subpos_bc11'] ?? null,
            'kode_tps' => $validated['kode_tps'] ?? null,
            'tanggal_tiba' => $validated['tanggal_tiba'] ?? null,
            'raw_payload' => $payload,
        ]);

        if (!empty($validated['shipment_id'])) {
            $shipment = \App\Models\Shipment::find($validated['shipment_id']);
            if ($shipment) {
                $shipment->update([
                    'ceisa_document_id' => $doc->id,
                    'status_tahap' => 'DRAFT_REVIEW',
                ]);
            }
        }

        return redirect()->route('bc20.show', $doc->id)
            ->with('success', "Dokumen BC 2.0 (PIB) berhasil dibuat dengan Nomor Aju: {$nomorAju}");
    }

    public function checkSchema(CeisaDocument $bc20)
    {
        try {
            $result = $this->documentService->checkSchema($bc20);
            $isValid = ($result['success'] ?? false) && (($result['data']['status'] ?? '') !== 'error');
            $msg = $result['data']['message'] ?? ($result['data']['error'] ?? ($isValid ? 'JSON Schema Valid dan sesuai spesifikasi CEISA 4.0 DJBC' : 'Validasi skema gagal'));

            $bc20->update([
                'is_schema_valid' => $isValid,
                'schema_validation_message' => $msg,
            ]);

            if ($isValid) {
                return back()->with('success', 'Pre-Check Berhasil: ' . $msg);
            } else {
                return back()->with('error', 'Pre-Check Gagal: ' . $msg);
            }
        } catch (\Throwable $e) {
            $bc20->update([
                'is_schema_valid' => false,
                'schema_validation_message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Pre-Check Gagal: ' . $e->getMessage());
        }
    }

    public function show(CeisaDocument $bc20)
    {
        $bc20->load('logs');
        $shipment = $bc20->shipment ?? $this->workflowService->ensureShipmentForCeisaDocument($bc20);
        $shipment->load([
            'client',
            'shippingVendor',
            'documents.verifier',
            'items.customsDocuments.verifier',
            'itemCustomsDocuments.verifier',
            'itemCustomsDocuments.item',
            'ceisaDocument.logs',
            'invoice'
        ]);

        $completionRate = $shipment->document_completion_rate;
        $threeWay = $shipment->three_way_matching;
        $taxes = $shipment->estimated_taxes;

        return view('bc20.show', compact('bc20', 'shipment', 'completionRate', 'threeWay', 'taxes'));
    }

    public function submit(CeisaDocument $bc20)
    {
        try {
            $result = $this->documentService->submit($bc20);
            if (!($result['success'] ?? false)) {
                return back()->with('error', $result['message'] ?? 'Pengiriman ke CEISA dibatalkan/diblokir.');
            }

            return back()->with('success', 'Dokumen berhasil dikirim ke gateway CEISA 4.0 DJBC!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal kirim ke CEISA: ' . $e->getMessage());
        }
    }

    public function checkStatus(CeisaDocument $bc20)
    {
        try {
            $this->documentService->checkStatus($bc20);
            return back()->with('success', 'Status dokumen berhasil diperbarui dari server CEISA 4.0!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal cek status: ' . $e->getMessage());
        }
    }

    public function fetchResponse(CeisaDocument $bc20)
    {
        try {
            $this->documentService->fetchResponse($bc20);
            return back()->with('success', 'Respon resmi (Billing / SPPB) berhasil diunduh dari CEISA 4.0!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal ambil respon: ' . $e->getMessage());
        }
    }

    /**
     * Tampilan form edit dokumen pabean BC 2.0
     * Hak Akses:
     * - Status DRAFT: document.edit atau document.edit_after_ceisa
     * - Status setelah kirim CEISA: document.edit_after_ceisa
     */
    public function edit(CeisaDocument $bc20)
    {
        if ($bc20->isBillingLocked()) {
            return redirect()->route('bc20.show', $bc20->id)
                ->with('error', "Dokumen BC 2.0 (Nomor Aju: {$bc20->nomor_aju}) telah berstatus Billing / Respon Resmi (Kode Billing: {$bc20->kode_billing}). Dokumen terkunci dan tidak dapat diedit kembali, namun seluruh data dan proses sebelumnya tetap dapat dilihat.");
        }

        $user = auth()->user();
        $isDraft = ($bc20->status === 'DRAFT');

        if ($isDraft) {
            if (!$user->isSuperAdmin() && !$user->hasAnyPermission(['document.edit', 'document.edit_after_ceisa'])) {
                abort(403, 'Akses Ditolak: Anda memerlukan hak akses izin "document.edit" untuk mengubah draft dokumen ini.');
            }
        } else {
            if (!$user->isSuperAdmin() && !$user->hasPermission('document.edit_after_ceisa')) {
                abort(403, 'Dokumen telah diajukan ke CEISA 4.0. Anda memerlukan hak akses izin "document.edit_after_ceisa" untuk merevisi data dokumen yang sudah terkirim.');
            }
        }

        $kantorList = config('ceisa.kantor_pabean', []);
        $activeValuta = $bc20->kode_valuta ?? 'USD';
        $activeKurs = $this->kursService->getActiveKurs($activeValuta);
        $payload = $bc20->raw_payload ?? [];
        $header = $payload['header'] ?? [];
        $barangList = $payload['barang'] ?? [];
        $shipment = $bc20->shipment;

        return view('bc20.edit', compact('bc20', 'kantorList', 'activeKurs', 'payload', 'header', 'barangList', 'shipment', 'isDraft'));
    }

    /**
     * Simpan pembaruan data dokumen BC 2.0
     */
    public function update(Request $request, CeisaDocument $bc20)
    {
        if ($bc20->isBillingLocked()) {
            return redirect()->route('bc20.show', $bc20->id)
                ->with('error', "Perubahan data ditolak: Dokumen BC 2.0 telah berstatus Billing / Respon Resmi (Kode Billing: {$bc20->kode_billing}). Dokumen terkunci dan tidak dapat dimodifikasi.");
        }

        $user = auth()->user();
        $isDraft = ($bc20->status === 'DRAFT');

        if ($isDraft) {
            if (!$user->isSuperAdmin() && !$user->hasAnyPermission(['document.edit', 'document.edit_after_ceisa'])) {
                abort(403, 'Akses Ditolak: Anda memerlukan hak akses izin "document.edit" untuk mengubah draft dokumen ini.');
            }
        } else {
            if (!$user->isSuperAdmin() && !$user->hasPermission('document.edit_after_ceisa')) {
                abort(403, 'Dokumen telah diajukan ke CEISA 4.0. Anda memerlukan hak akses izin "document.edit_after_ceisa" untuk merevisi data dokumen yang sudah terkirim.');
            }
        }

        $validated = $request->validate([
            'kode_kantor' => 'required|string',
            'ndbm_kurs' => 'required|numeric',
            'kode_valuta' => 'required|string',
            'fob' => 'required|numeric',
            'freight' => 'required|numeric',
            'asuransi' => 'required|numeric',
            'bruto' => 'required|numeric',
            'netto' => 'required|numeric',
            'supplier_nama' => 'required|string',
            'supplier_negara' => 'required|string',
            'invoice_no' => 'required|string',
            'invoice_date' => 'required|date',
            'nama_sarana_pengangkut' => 'required|string',
            'pelabuhan_muat' => 'required|string',
            'pelabuhan_bongkar' => 'required|string',
            'nomor_bc11' => 'nullable|string',
            'tanggal_bc11' => 'nullable|date',
            'pos_bc11' => 'nullable|string',
            'subpos_bc11' => 'nullable|string',
            'kode_tps' => 'nullable|string',
            'tanggal_tiba' => 'nullable|date',
            'barang' => 'required|array|min:1',
        ]);

        $validated['nomor_aju'] = $bc20->nomor_aju;

        // Susun Payload CEISA
        $payload = DocumentBC20Service::buildPayload($validated);

        $cif = $payload['header']['cif'];
        $cifRupiah = $payload['header']['cifRupiah'];
        $kantorName = config('ceisa.kantor_pabean.' . $validated['kode_kantor'], $bc20->nama_kantor);

        $desc = $isDraft 
            ? 'Draft dokumen pabean BC 2.0 (PIB) diperbarui oleh ' . $user->name . '.'
            : 'Dokumen pabean BC 2.0 direvisi pasca-kirim CEISA oleh ' . $user->name . ' pada ' . now()->format('d/m/Y H:i') . '.';

        $bc20->update([
            'kode_kantor' => $validated['kode_kantor'],
            'nama_kantor' => $kantorName,
            'status_description' => $desc,
            'kode_valuta' => $validated['kode_valuta'],
            'ndbm_kurs' => $validated['ndbm_kurs'],
            'total_nilai' => $cif,
            'total_nilai_idr' => $cifRupiah,
            'total_bruto' => $validated['bruto'],
            'total_netto' => $validated['netto'],
            'total_kemasan' => $request->jumlah_kemasan ?? $bc20->total_kemasan,
            'total_barang' => count($validated['barang']),
            'nama_mitra_luar_negeri' => $validated['supplier_nama'],
            'negara_mitra_luar_negeri' => $validated['supplier_negara'],
            'nomor_bc11' => $validated['nomor_bc11'] ?? null,
            'tanggal_bc11' => $validated['tanggal_bc11'] ?? null,
            'pos_bc11' => $validated['pos_bc11'] ?? null,
            'subpos_bc11' => $validated['subpos_bc11'] ?? null,
            'kode_tps' => $validated['kode_tps'] ?? null,
            'tanggal_tiba' => $validated['tanggal_tiba'] ?? null,
            'raw_payload' => $payload,
            'is_schema_valid' => false,
            'schema_validation_message' => 'Data diperbarui. Silakan lakukan validasi skema sebelum pengajuan CEISA.',
        ]);

        // Audit Trail ke CeisaApiLog
        CeisaApiLog::create([
            'nomor_aju' => $bc20->nomor_aju,
            'endpoint' => '/document/bc-20/update',
            'method' => 'PUT',
            'status_code' => 200,
            'request_header' => ['user_id' => $user->id, 'user_name' => $user->name],
            'request_body' => ['status' => $bc20->status, 'barang_count' => count($validated['barang'])],
            'response_body' => ['message' => $desc],
            'duration_ms' => 0,
            'is_success' => true,
        ]);

        // Sinkronkan ke shipment jika ada relasi
        if ($bc20->shipment) {
            $bc20->shipment->update([
                'kode_valuta' => $validated['kode_valuta'],
                'ndbm_kurs' => $validated['ndbm_kurs'],
                'total_nilai_valuta' => $cif,
                'total_nilai_idr' => $cifRupiah,
                'total_bruto' => $validated['bruto'],
                'total_netto' => $validated['netto'],
            ]);
        }

        return redirect()->route('bc20.show', $bc20->id)
            ->with('success', "Dokumen BC 2.0 (Nomor Aju: {$bc20->nomor_aju}) berhasil diperbarui!");
    }

    /**
     * Mengembalikan status dokumen setelah kirim CEISA kembali ke DRAFT
     * Hak Akses: document.edit_after_ceisa
     */
    public function reopenDraft(CeisaDocument $bc20)
    {
        if ($bc20->isBillingLocked()) {
            return redirect()->route('bc20.show', $bc20->id)
                ->with('error', "Dokumen tidak dapat dikembalikan ke DRAFT karena Kode Billing Kepabeanan telah diterbitkan oleh CEISA DJBC. Dokumen terkunci permanen untuk menjaga integritas data fiskal.");
        }

        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->hasPermission('document.edit_after_ceisa')) {
            abort(403, 'Akses Ditolak: Anda memerlukan hak akses izin "document.edit_after_ceisa" untuk membuka kembali dokumen yang sudah terkirim ke status DRAFT.');
        }

        $prevStatus = $bc20->status;
        $bc20->update([
            'status' => 'DRAFT',
            'status_description' => 'Dokumen dibuka kembali ke status DRAFT oleh ' . $user->name . ' (Status sebelumnya: ' . $prevStatus . ') untuk revisi/perbaikan data.',
            'is_schema_valid' => false,
            'schema_validation_message' => 'Status dikembalikan ke DRAFT. Silakan lakukan validasi skema ulang sebelum pengiriman berikutnya.',
        ]);

        CeisaApiLog::create([
            'nomor_aju' => $bc20->nomor_aju,
            'endpoint' => '/document/bc-20/reopen-draft',
            'method' => 'POST',
            'status_code' => 200,
            'request_header' => ['user_id' => $user->id, 'user_name' => $user->name],
            'request_body' => ['status_lama' => $prevStatus],
            'response_body' => ['message' => 'Dokumen berhasil dikembalikan ke status DRAFT.'],
            'duration_ms' => 0,
            'is_success' => true,
        ]);

        if ($bc20->shipment && in_array($bc20->shipment->status_tahap, ['CEISA_SUBMITTED', 'BILLING_ISSUED'])) {
            $bc20->shipment->update([
                'status_tahap' => 'DRAFT_REVIEW',
            ]);
        }

        return redirect()->route('bc20.show', $bc20->id)
            ->with('success', "Dokumen BC 2.0 berhasil dikembalikan ke status DRAFT. Anda kini dapat mengedit data.");
    }
}
