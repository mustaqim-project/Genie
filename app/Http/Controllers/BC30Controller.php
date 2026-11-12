<?php

namespace App\Http\Controllers;

use App\Models\CeisaApiLog;
use App\Models\CeisaDocument;
use App\Services\Ceisa\CeisaDocumentService;
use App\Services\Ceisa\DocumentBC30Service;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BC30Controller extends Controller implements HasMiddleware
{
    protected CeisaDocumentService $documentService;
    protected \App\Services\Ceisa\CeisaKursService $kursService;
    protected \App\Services\Shipment\ShipmentWorkflowService $workflowService;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:document.view', only: ['index', 'show']),
            new Middleware('permission:document.create', only: ['create', 'store']),
            new Middleware('permission:document.create|document.edit', only: ['destroy']),
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

    /**
     * Hapus dokumen BC 3.0 berstatus DRAFT beserta proyek & aset file dokumen terkait.
     */
    public function destroy(Request $request, CeisaDocument $bc30)
    {
        if ($bc30->jenis_dokumen !== '30') {
            abort(404);
        }

        try {
            $result = $this->workflowService->deleteDraftCeisaDocumentAndAssets($bc30, true);
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        $label = $result['project_no'] ?? ($result['nomor_aju'] ?? $bc30->nomor_aju);
        $msg = "Draft BC 3.0 ({$label}) dan {$result['deleted_files']} aset file dokumen berhasil dihapus permanen.";

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => $msg, 'data' => $result]);
        }

        return redirect()->route('bc30.index')->with('success', $msg);
    }

    public function index(Request $request)
    {
        $query = CeisaDocument::where('jenis_dokumen', '30');

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
            'total' => CeisaDocument::where('jenis_dokumen', '30')->count(),
            'draft' => CeisaDocument::where('jenis_dokumen', '30')->where('status', 'DRAFT')->count(),
            'proses' => CeisaDocument::where('jenis_dokumen', '30')->whereIn('status', ['SUBMITTED', 'PROSES'])->count(),
            'npe' => CeisaDocument::where('jenis_dokumen', '30')->where('status', 'NPE')->count(),
            'lartas_docs' => $lartasDocsCount,
            'lartas_products' => $lartasProductsCount,
            'non_lartas_docs' => $nonLartasDocsCount,
        ];

        return view('bc30.index', compact('documents', 'stats'));
    }

    public function create(Request $request)
    {
        $defaultNomorAju = DocumentBC30Service::generateNomorAju();
        $kantorList = config('ceisa.kantor_pabean', []);

        $shipments = \App\Models\Shipment::with(['client', 'items'])
            ->where('jenis_layanan', 'EKSPORT_BC30')
            ->latest()
            ->take(20)
            ->get();

        $selectedShipment = null;
        if ($request->filled('shipment_id')) {
            $selectedShipment = \App\Models\Shipment::with(['client', 'items'])->find($request->shipment_id);
        }

        $activeValuta = $selectedShipment->currency ?? 'USD';
        $activeKurs = $this->kursService->getActiveKurs($activeValuta);

        $itemList = ($selectedShipment && $selectedShipment->items && $selectedShipment->items->isNotEmpty())
            ? $selectedShipment->items
            : [
                (object)[
                    'kode_hs' => '0901.11.10',
                    'uraian_barang' => 'BIJI KOPI ARABIKA GAYO GRADE 1 (EXPORT QUALITY)',
                    'merk' => 'GAYO HIGHLAND GRADE 1',
                    'jumlah_kemasan' => 200,
                    'jenis_kemasan' => 'BAG',
                    'nilai_fob' => 48500.00,
                    'tarif_bk' => 0.0,
                ]
            ];

        return view('bc30.create', compact('defaultNomorAju', 'kantorList', 'shipments', 'selectedShipment', 'activeKurs', 'itemList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'nullable|uuid|exists:shipments,id',
            'kode_kantor' => 'required|string',
            'ndbm_kurs' => 'required|numeric',
            'kode_valuta' => 'required|string',
            'fob' => 'required|numeric',
            'freight' => 'nullable|numeric',
            'asuransi' => 'nullable|numeric',
            'bruto' => 'required|numeric',
            'netto' => 'required|numeric',
            'buyer_nama' => 'required|string',
            'buyer_negara' => 'required|string',
            'invoice_no' => 'required|string',
            'invoice_date' => 'required|date',
            'nama_sarana_pengangkut' => 'required|string',
            'pelabuhan_muat' => 'required|string',
            'pelabuhan_bongkar' => 'required|string',
            'pic_pembuat' => 'nullable|string',
            'telepon_pembuat' => 'nullable|string',
            'lokasi_siap_periksa' => 'nullable|string',
            'tanggal_periksa' => 'nullable|date',
            'bank_kode' => 'nullable|string',
            'bank_nama' => 'nullable|string',
            'barang' => 'required|array|min:1',
        ]);

        $nomorAju = DocumentBC30Service::generateNomorAju($validated['kode_kantor']);
        $validated['nomor_aju'] = $nomorAju;

        // Susun Payload CEISA
        $payload = DocumentBC30Service::buildPayload($validated);

        $fob = $payload['header']['fob'];
        $fobRupiah = $payload['header']['fobRupiah'];
        $kantorName = config('ceisa.kantor_pabean.' . $validated['kode_kantor'], 'KPU Tanjung Priok');

        $doc = CeisaDocument::create([
            'nomor_aju' => $nomorAju,
            'jenis_dokumen' => '30',
            'kode_kantor' => $validated['kode_kantor'],
            'nama_kantor' => $kantorName,
            'status' => 'DRAFT',
            'status_description' => 'Draft dokumen pabean BC 3.0 (PEB) siap diajukan ke CEISA 4.0 DJBC.',
            'kode_valuta' => $validated['kode_valuta'],
            'ndbm_kurs' => $validated['ndbm_kurs'],
            'total_nilai' => $fob,
            'total_nilai_idr' => $fobRupiah,
            'total_bruto' => $validated['bruto'],
            'total_netto' => $validated['netto'],
            'total_kemasan' => $request->jumlah_kemasan ?? 50,
            'total_kontainer' => 1,
            'total_barang' => count($validated['barang']),
            'nama_perusahaan_lokal' => config('ceisa.company.name'),
            'npwp_perusahaan_lokal' => config('ceisa.company.npwp'),
            'nama_mitra_luar_negeri' => $validated['buyer_nama'],
            'negara_mitra_luar_negeri' => $validated['buyer_negara'],
            'pic_pembuat' => $validated['pic_pembuat'] ?? null,
            'telepon_pembuat' => $validated['telepon_pembuat'] ?? null,
            'lokasi_siap_periksa' => $validated['lokasi_siap_periksa'] ?? null,
            'tanggal_periksa' => $validated['tanggal_periksa'] ?? null,
            'bank_kode' => $validated['bank_kode'] ?? null,
            'bank_nama' => $validated['bank_nama'] ?? null,
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

        return redirect()->route('bc30.show', $doc->id)
            ->with('success', "Dokumen BC 3.0 (PEB) berhasil dibuat dengan Nomor Aju: {$nomorAju}");
    }

    public function checkSchema(CeisaDocument $bc30)
    {
        try {
            $result = $this->documentService->checkSchema($bc30);
            $isValid = ($result['success'] ?? false) && (($result['data']['status'] ?? '') !== 'error');
            $msg = $result['data']['message'] ?? ($result['data']['error'] ?? ($isValid ? 'JSON Schema Valid dan sesuai spesifikasi CEISA 4.0 DJBC' : 'Validasi skema gagal'));

            $bc30->update([
                'is_schema_valid' => $isValid,
                'schema_validation_message' => $msg,
            ]);

            if ($isValid) {
                return back()->with('success', 'Pre-Check Berhasil: ' . $msg);
            } else {
                return back()->with('error', 'Pre-Check Gagal: ' . $msg);
            }
        } catch (\Throwable $e) {
            $bc30->update([
                'is_schema_valid' => false,
                'schema_validation_message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Pre-Check Gagal: ' . $e->getMessage());
        }
    }

    public function show(CeisaDocument $bc30)
    {
        $bc30->load('logs');
        $shipment = $bc30->shipment ?? $this->workflowService->ensureShipmentForCeisaDocument($bc30);
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

        return view('bc30.show', compact('bc30', 'shipment', 'completionRate', 'threeWay', 'taxes'));
    }

    public function submit(CeisaDocument $bc30)
    {
        try {
            $result = $this->documentService->submit($bc30);
            if (!($result['success'] ?? false)) {
                return back()->with('error', $result['message'] ?? 'Pengiriman ke CEISA dibatalkan/diblokir.');
            }

            return back()->with('success', 'Dokumen berhasil dikirim ke gateway CEISA 4.0 DJBC!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal kirim ke CEISA: ' . $e->getMessage());
        }
    }

    public function checkStatus(CeisaDocument $bc30)
    {
        try {
            $this->documentService->checkStatus($bc30);
            return back()->with('success', 'Status dokumen berhasil diperbarui dari server CEISA 4.0!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal cek status: ' . $e->getMessage());
        }
    }

    public function fetchResponse(CeisaDocument $bc30)
    {
        try {
            $this->documentService->fetchResponse($bc30);
            return back()->with('success', 'Respon resmi (Nota Pelayanan Ekspor / NPE) berhasil diunduh dari CEISA 4.0!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal ambil respon: ' . $e->getMessage());
        }
    }

    /**
     * Tampilan form edit dokumen pabean BC 3.0 (PEB)
     * Hak Akses:
     * - Status DRAFT: document.edit atau document.edit_after_ceisa
     * - Status setelah kirim CEISA: document.edit_after_ceisa
     */
    public function edit(CeisaDocument $bc30)
    {
        if ($bc30->isBillingLocked()) {
            return redirect()->route('bc30.show', $bc30->id)
                ->with('error', "Dokumen BC 3.0 (Nomor Aju: {$bc30->nomor_aju}) telah berstatus Billing / Respon Resmi (NPE). Dokumen terkunci dan tidak dapat diedit kembali, namun seluruh data dan proses sebelumnya tetap dapat dilihat.");
        }

        $user = auth()->user();
        $isDraft = ($bc30->status === 'DRAFT');

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
        $activeValuta = $bc30->kode_valuta ?? 'USD';
        $activeKurs = $this->kursService->getActiveKurs($activeValuta);
        $payload = $bc30->raw_payload ?? [];
        $header = $payload['header'] ?? [];
        $barangList = $payload['barang'] ?? [];
        $shipment = $bc30->shipment;

        return view('bc30.edit', compact('bc30', 'kantorList', 'activeKurs', 'payload', 'header', 'barangList', 'shipment', 'isDraft'));
    }

    /**
     * Simpan pembaruan data dokumen BC 3.0 (PEB)
     */
    public function update(Request $request, CeisaDocument $bc30)
    {
        if ($bc30->isBillingLocked()) {
            return redirect()->route('bc30.show', $bc30->id)
                ->with('error', "Perubahan data ditolak: Dokumen BC 3.0 telah berstatus Billing / Respon Resmi (NPE). Dokumen terkunci dan tidak dapat dimodifikasi.");
        }

        $user = auth()->user();
        $isDraft = ($bc30->status === 'DRAFT');

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
            'freight' => 'nullable|numeric',
            'asuransi' => 'nullable|numeric',
            'bruto' => 'required|numeric',
            'netto' => 'required|numeric',
            'buyer_nama' => 'required|string',
            'buyer_negara' => 'required|string',
            'invoice_no' => 'required|string',
            'invoice_date' => 'required|date',
            'nama_sarana_pengangkut' => 'required|string',
            'pelabuhan_muat' => 'required|string',
            'pelabuhan_bongkar' => 'required|string',
            'pic_pembuat' => 'nullable|string',
            'telepon_pembuat' => 'nullable|string',
            'lokasi_siap_periksa' => 'nullable|string',
            'tanggal_periksa' => 'nullable|date',
            'bank_kode' => 'nullable|string',
            'bank_nama' => 'nullable|string',
            'barang' => 'required|array|min:1',
        ]);

        $validated['nomor_aju'] = $bc30->nomor_aju;

        // Susun Payload CEISA
        $payload = DocumentBC30Service::buildPayload($validated);

        $fob = $payload['header']['fob'];
        $fobRupiah = $payload['header']['fobRupiah'];
        $kantorName = config('ceisa.kantor_pabean.' . $validated['kode_kantor'], $bc30->nama_kantor);

        $desc = $isDraft 
            ? 'Draft dokumen pabean BC 3.0 (PEB) diperbarui oleh ' . $user->name . '.'
            : 'Dokumen pabean BC 3.0 direvisi pasca-kirim CEISA oleh ' . $user->name . ' pada ' . now()->format('d/m/Y H:i') . '.';

        $bc30->update([
            'kode_kantor' => $validated['kode_kantor'],
            'nama_kantor' => $kantorName,
            'status_description' => $desc,
            'kode_valuta' => $validated['kode_valuta'],
            'ndbm_kurs' => $validated['ndbm_kurs'],
            'total_nilai' => $fob,
            'total_nilai_idr' => $fobRupiah,
            'total_bruto' => $validated['bruto'],
            'total_netto' => $validated['netto'],
            'total_kemasan' => $request->jumlah_kemasan ?? $bc30->total_kemasan,
            'total_barang' => count($validated['barang']),
            'nama_mitra_luar_negeri' => $validated['buyer_nama'],
            'negara_mitra_luar_negeri' => $validated['buyer_negara'],
            'pic_pembuat' => $validated['pic_pembuat'] ?? null,
            'telepon_pembuat' => $validated['telepon_pembuat'] ?? null,
            'lokasi_siap_periksa' => $validated['lokasi_siap_periksa'] ?? null,
            'tanggal_periksa' => $validated['tanggal_periksa'] ?? null,
            'bank_kode' => $validated['bank_kode'] ?? null,
            'bank_nama' => $validated['bank_nama'] ?? null,
            'raw_payload' => $payload,
            'is_schema_valid' => false,
            'schema_validation_message' => 'Data diperbarui. Silakan lakukan validasi skema sebelum pengajuan CEISA.',
        ]);

        // Audit Trail ke CeisaApiLog
        CeisaApiLog::create([
            'nomor_aju' => $bc30->nomor_aju,
            'endpoint' => '/document/bc-30/update',
            'method' => 'PUT',
            'status_code' => 200,
            'request_header' => ['user_id' => $user->id, 'user_name' => $user->name],
            'request_body' => ['status' => $bc30->status, 'barang_count' => count($validated['barang'])],
            'response_body' => ['message' => $desc],
            'duration_ms' => 0,
            'is_success' => true,
        ]);

        // Sinkronkan ke shipment jika ada relasi
        if ($bc30->shipment) {
            $bc30->shipment->update([
                'kode_valuta' => $validated['kode_valuta'],
                'ndbm_kurs' => $validated['ndbm_kurs'],
                'total_nilai_valuta' => $fob,
                'total_nilai_idr' => $fobRupiah,
                'total_bruto' => $validated['bruto'],
                'total_netto' => $validated['netto'],
            ]);
        }

        return redirect()->route('bc30.show', $bc30->id)
            ->with('success', "Dokumen BC 3.0 (Nomor Aju: {$bc30->nomor_aju}) berhasil diperbarui!");
    }

    /**
     * Mengembalikan status dokumen setelah kirim CEISA kembali ke DRAFT
     * Hak Akses: document.edit_after_ceisa
     */
    public function reopenDraft(CeisaDocument $bc30)
    {
        if ($bc30->isBillingLocked()) {
            return redirect()->route('bc30.show', $bc30->id)
                ->with('error', "Dokumen tidak dapat dikembalikan ke DRAFT karena Billing / Respon Kepabeanan telah diterbitkan oleh CEISA DJBC.");
        }

        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->hasPermission('document.edit_after_ceisa')) {
            abort(403, 'Akses Ditolak: Anda memerlukan hak akses izin "document.edit_after_ceisa" untuk membuka kembali dokumen yang sudah terkirim ke status DRAFT.');
        }

        $prevStatus = $bc30->status;
        $bc30->update([
            'status' => 'DRAFT',
            'status_description' => 'Dokumen dibuka kembali ke status DRAFT oleh ' . $user->name . ' (Status sebelumnya: ' . $prevStatus . ') untuk revisi/perbaikan data.',
            'is_schema_valid' => false,
            'schema_validation_message' => 'Status dikembalikan ke DRAFT. Silakan lakukan validasi skema ulang sebelum pengiriman berikutnya.',
        ]);

        CeisaApiLog::create([
            'nomor_aju' => $bc30->nomor_aju,
            'endpoint' => '/document/bc-30/reopen-draft',
            'method' => 'POST',
            'status_code' => 200,
            'request_header' => ['user_id' => $user->id, 'user_name' => $user->name],
            'request_body' => ['status_lama' => $prevStatus],
            'response_body' => ['message' => 'Dokumen berhasil dikembalikan ke status DRAFT.'],
            'duration_ms' => 0,
            'is_success' => true,
        ]);

        if ($bc30->shipment && in_array($bc30->shipment->status_tahap, ['CEISA_SUBMITTED', 'BILLING_ISSUED'])) {
            $bc30->shipment->update([
                'status_tahap' => 'DRAFT_REVIEW',
            ]);
        }

        return redirect()->route('bc30.show', $bc30->id)
            ->with('success', "Dokumen BC 3.0 berhasil dikembalikan ke status DRAFT. Anda kini dapat mengedit data.");
    }
}
