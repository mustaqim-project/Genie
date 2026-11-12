<?php

namespace App\Http\Controllers;

use App\Models\CeisaApiLog;
use App\Models\CeisaDocument;
use App\Models\Shipment;
use App\Services\Ceisa\CeisaDocumentService;
use App\Services\Ceisa\DocumentBC20Service;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

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
            new Middleware('permission:document.create|document.edit', only: ['destroy']),
            new Middleware('permission:document.edit|document.edit_after_ceisa', only: ['edit', 'update', 'uploadWizardDocument']),
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
     * Hapus dokumen BC 2.0 berstatus DRAFT beserta proyek & aset file dokumen terkait.
     */
    public function destroy(Request $request, CeisaDocument $bc20)
    {
        if ($bc20->jenis_dokumen !== '20') {
            abort(404);
        }

        try {
            $result = $this->workflowService->deleteDraftCeisaDocumentAndAssets($bc20, true);
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        $label = $result['project_no'] ?? ($result['nomor_aju'] ?? $bc20->nomor_aju);
        $msg = "Draft BC 2.0 ({$label}) dan {$result['deleted_files']} aset file dokumen berhasil dihapus permanen.";

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => $msg, 'data' => $result]);
        }

        return redirect()->route('bc20.index')->with('success', $msg);
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
     * Tampilan form edit 9 tahap (PIB BC 2.0) dengan Live Split-Screen Document Preview
     * Hak Akses:
     * - Status DRAFT: document.edit atau document.edit_after_ceisa
     * - Status setelah kirim CEISA: document.edit_after_ceisa
     */
    public function edit(CeisaDocument $bc20)
    {
        if ($redirect = $this->guardEditable($bc20)) {
            return $redirect;
        }

        $isDraft = ($bc20->status === 'DRAFT');
        $kantorList = config('ceisa.kantor_pabean', []);
        $activeValuta = $bc20->kode_valuta ?? 'USD';
        $activeKurs = $this->kursService->getActiveKurs($activeValuta);
        $payload = $bc20->raw_payload ?? [];
        $header = $payload['header'] ?? [];
        $barangList = $payload['barang'] ?? [];

        $shipment = $bc20->shipment ?? $this->workflowService->ensureShipmentForCeisaDocument($bc20);
        $shipment->load(['documents', 'items', 'client']);

        $refOptions = $this->buildReferenceOptions();
        [$previewFiles, $dokumenList] = $this->buildPreviewAndLinkedDocs($shipment, $payload);
        $wizardState = $this->buildInitialWizardState($bc20, $payload, $header, $barangList, $dokumenList, $shipment);

        return view('bc20.edit', compact(
            'bc20',
            'kantorList',
            'activeKurs',
            'payload',
            'header',
            'barangList',
            'shipment',
            'isDraft',
            'refOptions',
            'previewFiles',
            'wizardState'
        ));
    }

    /**
     * Simpan pembaruan data dokumen BC 2.0 (mendukung form klasik & payload 9 tahap JSON)
     */
    public function update(Request $request, CeisaDocument $bc20)
    {
        if ($redirect = $this->guardEditable($bc20)) {
            return $redirect;
        }

        $user = auth()->user();
        $isDraft = ($bc20->status === 'DRAFT');

        $wizardInput = $request->input('wizard_payload');
        if (is_string($wizardInput)) {
            $decoded = json_decode($wizardInput, true);
            $wizardInput = is_array($decoded) ? $decoded : null;
        }

        if (is_array($wizardInput)) {
            $inputData = $this->mergeWizardPayloadToBuildInput($wizardInput, $bc20);
            $barangArray = $inputData['barang'];
        } else {
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
            $inputData = $validated;
            $inputData['jumlah_kemasan'] = $request->input('jumlah_kemasan', $bc20->total_kemasan);
            $barangArray = $validated['barang'];
        }

        $inputData['nomor_aju'] = $bc20->nomor_aju;

        // Susun Payload CEISA 9 Tahap
        $payload = DocumentBC20Service::buildPayload($inputData);

        $cif = $payload['header']['cif'];
        $cifRupiah = $payload['header']['cifRupiah'];
        $kodeKantor = $inputData['kode_kantor'] ?? $bc20->kode_kantor;
        $kantorName = config('ceisa.kantor_pabean.' . $kodeKantor, $bc20->nama_kantor);

        $desc = $isDraft
            ? 'Draft dokumen pabean BC 2.0 (PIB) diperbarui oleh ' . $user->name . '.'
            : 'Dokumen pabean BC 2.0 direvisi pasca-kirim CEISA oleh ' . $user->name . ' pada ' . now()->format('d/m/Y H:i') . '.';

        $totalKemasan = collect($payload['kemasan'] ?? [])->sum('jumlahKemasan') ?: ($inputData['jumlah_kemasan'] ?? $bc20->total_kemasan);
        $totalKontainer = count($payload['kontainer'] ?? []) ?: $bc20->total_kontainer;
        $totalPungutan = (float) ($payload['header']['totalPungutan'] ?? $bc20->total_billing);

        $bc20->update([
            'kode_kantor' => $kodeKantor,
            'nama_kantor' => $kantorName,
            'status_description' => $desc,
            'kode_valuta' => $inputData['kode_valuta'] ?? $bc20->kode_valuta,
            'ndbm_kurs' => $inputData['ndbm_kurs'] ?? $bc20->ndbm_kurs,
            'total_nilai' => $cif,
            'total_nilai_idr' => $cifRupiah,
            'total_bruto' => $inputData['bruto'] ?? $bc20->total_bruto,
            'total_netto' => $inputData['netto'] ?? $bc20->total_netto,
            'total_kemasan' => $totalKemasan,
            'total_kontainer' => $totalKontainer,
            'total_barang' => count($payload['barang'] ?? $barangArray),
            'total_billing' => $totalPungutan,
            'nama_perusahaan_lokal' => $inputData['importir_nama'] ?? $bc20->nama_perusahaan_lokal,
            'npwp_perusahaan_lokal' => $inputData['importir_npwp'] ?? $bc20->npwp_perusahaan_lokal,
            'nama_mitra_luar_negeri' => $inputData['supplier_nama'] ?? $bc20->nama_mitra_luar_negeri,
            'negara_mitra_luar_negeri' => $inputData['supplier_negara'] ?? $bc20->negara_mitra_luar_negeri,
            'nomor_bc11' => $inputData['nomor_bc11'] ?? null,
            'tanggal_bc11' => !empty($inputData['tanggal_bc11']) ? $inputData['tanggal_bc11'] : null,
            'pos_bc11' => $inputData['pos_bc11'] ?? null,
            'subpos_bc11' => $inputData['subpos_bc11'] ?? null,
            'kode_tps' => $inputData['kode_tps'] ?? null,
            'tanggal_tiba' => !empty($inputData['tanggal_tiba']) ? $inputData['tanggal_tiba'] : null,
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
            'request_body' => ['status' => $bc20->status, 'barang_count' => count($payload['barang'] ?? $barangArray)],
            'response_body' => ['message' => $desc],
            'duration_ms' => 0,
            'is_success' => true,
        ]);

        // Sinkronkan ke shipment jika ada relasi
        if ($bc20->shipment) {
            $bc20->shipment->update([
                'kode_valuta' => $inputData['kode_valuta'] ?? $bc20->kode_valuta,
                'ndbm_kurs' => $inputData['ndbm_kurs'] ?? $bc20->ndbm_kurs,
                'total_nilai_valuta' => $cif,
                'total_nilai_idr' => $cifRupiah,
                'total_bruto' => $inputData['bruto'] ?? $bc20->total_bruto,
                'total_netto' => $inputData['netto'] ?? $bc20->total_netto,
                'total_kemasan' => $totalKemasan,
                'total_kontainer' => $totalKontainer,
                'pelabuhan_muat' => $inputData['pelabuhan_muat'] ?? $bc20->shipment->pelabuhan_muat,
                'pelabuhan_bongkar' => $inputData['pelabuhan_bongkar'] ?? $bc20->shipment->pelabuhan_bongkar,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Draft BC 2.0 ({$bc20->nomor_aju}) berhasil disimpan.",
                'pungutan' => $payload['pungutan'] ?? [],
                'header' => $payload['header'] ?? [],
            ]);
        }

        return redirect()->route('bc20.show', $bc20->id)
            ->with('success', "Dokumen BC 2.0 (Nomor Aju: {$bc20->nomor_aju}) berhasil diperbarui!");
    }

    /**
     * Unggah PDF/Gambar langsung dari Wizard BC 2.0 (Tab Dokumen / NOA / Freight)
     */
    public function uploadWizardDocument(Request $request, CeisaDocument $bc20)
    {
        if ($redirect = $this->guardEditable($bc20)) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Dokumen terkunci dan tidak dapat diubah.'], 403)
                : $redirect;
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp,txt,csv|max:10240',
            'kode_dokumen' => 'nullable|string|max:10',
            'nomor_dokumen' => 'nullable|string|max:100',
            'tanggal_dokumen' => 'nullable|date',
            'purpose' => 'nullable|string|in:dokumen,noa,freight',
        ]);

        $shipment = $bc20->shipment ?? $this->workflowService->ensureShipmentForCeisaDocument($bc20);
        $kodeDok = $validated['kode_dokumen'] ?? '380';
        $purpose = $validated['purpose'] ?? 'dokumen';

        $slot = match ($kodeDok) {
            '380' => 'INVOICE',
            '705', '740' => 'BL_AWB',
            '217' => 'PACKING_LIST',
            '861' => 'COO_FORME',
            '430' => 'INSURANCE',
            default => 'PERIJINAN_LARTAS',
        };

        $notePrefix = match ($purpose) {
            'noa' => 'Notice of Arrival (NOA)',
            'freight' => 'Bukti Freight',
            default => "Lampiran CEISA Kode {$kodeDok}",
        };

        $file = $request->file('file');
        $doc = $this->workflowService->uploadDocument(
            $shipment,
            $slot,
            $file,
            "{$notePrefix}: " . ($validated['nomor_dokumen'] ?? $file->getClientOriginalName())
        );

        $previewEntry = $this->formatPreviewFileEntry($shipment, $doc);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diunggah dan siap dipreview.',
            'preview_file' => $previewEntry,
            'doc_entry' => [
                'kodeDokumen' => $kodeDok,
                'nomorDokumen' => $validated['nomor_dokumen'] ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'tanggalDokumen' => $validated['tanggal_dokumen'] ?: date('Y-m-d'),
                'shipmentDocumentId' => $doc->id,
                'ecooStatus' => $kodeDok === '861' ? 'Valid / Linked' : '-',
                'linkedSeri' => '',
            ],
        ]);
    }

    /**
     * Validasi izin edit & status billing lock BC 2.0
     */
    protected function guardEditable(CeisaDocument $bc20)
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

        return null;
    }

    /**
     * Format entri preview dokumen untuk split-screen viewer
     */
    protected function formatPreviewFileEntry(Shipment $shipment, \App\Models\ShipmentDocument $doc): array
    {
        $ext = strtolower(pathinfo((string) ($doc->nama_file ?? $doc->file_path), PATHINFO_EXTENSION));
        $previewable = in_array($ext, ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'txt', 'csv'], true);

        return [
            'id' => $doc->id,
            'jenis_dokumen' => $doc->jenis_dokumen,
            'nama_file' => $doc->nama_file ?? basename((string) $doc->file_path),
            'ext' => strtoupper($ext ?: 'PDF'),
            'is_image' => in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true),
            'is_previewable' => $previewable,
            'preview_url' => route('core.document.preview-doc', [$shipment->id, $doc->id]),
            'download_url' => route('core.document.download-doc', [$shipment->id, $doc->id]),
            'status' => $doc->status,
        ];
    }

    /**
     * Susun daftar file preview & tautkan ke array dokumen CEISA
     */
    protected function buildPreviewAndLinkedDocs(Shipment $shipment, array $payload): array
    {
        $uploadedDocs = $shipment->documents
            ->filter(fn($d) => !empty($d->file_path) && Storage::disk('public')->exists($d->file_path))
            ->sortByDesc(fn($d) => ($d->is_current ? 1 : 0) . '_' . optional($d->created_at)->timestamp)
            ->values();

        $previewFiles = $uploadedDocs->map(fn($d) => $this->formatPreviewFileEntry($shipment, $d))->values()->all();

        $kodeToSlotMap = [
            '380' => ['INVOICE', 'INV'],
            '705' => ['BL_AWB', 'BL'],
            '740' => ['BL_AWB', 'BL'],
            '217' => ['PACKING_LIST', 'PL'],
            '861' => ['COO_FORME', 'COO'],
            '430' => ['INSURANCE', 'INS'],
        ];

        $rawDokumen = $payload['dokumen'] ?? [];
        if (empty($rawDokumen)) {
            $docDate = $shipment->tgl_bl_awb ? $shipment->tgl_bl_awb->format('Y-m-d') : date('Y-m-d');
            $rawDokumen = [
                ['seriDokumen' => 1, 'kodeDokumen' => '380', 'nomorDokumen' => 'INV/' . ($shipment->nomor_bl_awb ?? date('Ymd')), 'tanggalDokumen' => $docDate],
                ['seriDokumen' => 2, 'kodeDokumen' => '705', 'nomorDokumen' => $shipment->nomor_bl_awb ?? ('BL-' . date('Ymd')), 'tanggalDokumen' => $docDate],
                ['seriDokumen' => 3, 'kodeDokumen' => '217', 'nomorDokumen' => 'PL/' . ($shipment->nomor_bl_awb ?? date('Ymd')), 'tanggalDokumen' => $docDate],
            ];
        }

        $linkedIds = [];
        $dokumenList = [];
        foreach ($rawDokumen as $idx => $d) {
            $kode = (string) ($d['kodeDokumen'] ?? '380');
            $docId = $d['shipmentDocumentId'] ?? null;
            if (!$docId && isset($kodeToSlotMap[$kode])) {
                $matched = $uploadedDocs->firstWhere(fn($u) => in_array($u->jenis_dokumen, $kodeToSlotMap[$kode], true));
                $docId = $matched?->id;
            }
            if ($docId) {
                $linkedIds[] = $docId;
            }
            $dokumenList[] = [
                'seriDokumen' => (int) ($d['seriDokumen'] ?? ($idx + 1)),
                'kodeDokumen' => $kode,
                'nomorDokumen' => (string) ($d['nomorDokumen'] ?? ''),
                'tanggalDokumen' => (string) ($d['tanggalDokumen'] ?? date('Y-m-d')),
                'shipmentDocumentId' => $docId,
                'ecooStatus' => (string) ($d['ecooStatus'] ?? '-'),
                'linkedSeri' => (string) ($d['linkedSeri'] ?? ''),
            ];
        }

        // Tambahkan dokumen terunggah lain yang belum masuk ke daftar CEISA agar langsung muncul & bisa dipreview
        $slotToKodeMap = [
            'INVOICE' => '380',
            'INV' => '380',
            'BL_AWB' => '705',
            'BL' => '705',
            'PACKING_LIST' => '217',
            'PL' => '217',
            'COO_FORME' => '861',
            'COO' => '861',
            'INSURANCE' => '430',
            'INS' => '430',
            'PERIJINAN_LARTAS' => '860',
            'LARTAS' => '860',
        ];
        foreach ($uploadedDocs as $u) {
            if ($u->is_current && !in_array($u->id, $linkedIds, true)) {
                $dokumenList[] = [
                    'seriDokumen' => count($dokumenList) + 1,
                    'kodeDokumen' => $slotToKodeMap[$u->jenis_dokumen] ?? '999',
                    'nomorDokumen' => pathinfo((string) $u->nama_file, PATHINFO_FILENAME),
                    'tanggalDokumen' => optional($u->created_at)->format('Y-m-d') ?? date('Y-m-d'),
                    'shipmentDocumentId' => $u->id,
                    'ecooStatus' => '-',
                    'linkedSeri' => '',
                ];
                $linkedIds[] = $u->id;
            }
        }

        return [$previewFiles, $dokumenList];
    }

    /**
     * Daftar opsi referensi CEISA 4.0 untuk form 9 tahap
     */
    protected function buildReferenceOptions(): array
    {
        return [
            'pelabuhan' => [
                'IDTPP' => 'IDTPP - Tanjung Priok',
                'IDSUB' => 'IDSUB - Tanjung Perak Surabaya',
                'IDCGK' => 'IDCGK - Bandara Soekarno-Hatta',
                'IDSRG' => 'IDSRG - Tanjung Emas Semarang',
                'IDBLW' => 'IDBLW - Belawan Medan',
                'IDMAK' => 'IDMAK - Makassar',
                'IDBEN' => 'IDBEN - Benoa Bali',
                'CNSHA' => 'CNSHA - Shanghai, China',
                'CNTAO' => 'CNTAO - Qingdao, China',
                'CNYTN' => 'CNYTN - Yantian, China',
                'CNNGB' => 'CNNGB - Ningbo, China',
                'CNXMN' => 'CNXMN - Xiamen, China',
                'SGSIN' => 'SGSIN - Singapore',
                'HKHKG' => 'HKHKG - Hong Kong',
                'JPYOK' => 'JPYOK - Yokohama, Japan',
                'JPTYO' => 'JPTYO - Tokyo, Japan',
                'KRPUS' => 'KRPUS - Busan, South Korea',
                'MYPKG' => 'MYPKG - Port Klang, Malaysia',
                'THLCH' => 'THLCH - Laem Chabang, Thailand',
                'VNSGN' => 'VNSGN - Ho Chi Minh City, Vietnam',
            ],
            'pib_jenis' => [
                '1' => '1 - BIASA',
                '2' => '2 - BERKALA',
            ],
            'jenis_impor' => [
                '1' => '1 - UNTUK DIPAKAI',
                '2' => '2 - SEMENTARA',
                '5' => '5 - PELAYANAN SEGERA',
                '6' => '6 - VOORUITSLAG',
                '9' => '9 - GABUNGAN',
            ],
            'cara_bayar' => [
                '1' => '1 - BIASA/TUNAI',
                '2' => '2 - BERKALA',
                '3' => '3 - DENGAN JAMINAN',
                '9' => '9 - LAINNYA',
            ],
            'jenis_identitas' => [
                '6' => '6 - NPWP 16 DIGIT',
                '5' => '5 - NPWP 15 DIGIT',
                '0' => '0 - LAINNYA',
            ],
            'kode_dokumen' => [
                '380' => '380 - INVOICE',
                '705' => '705 - B/L (Bill of Lading)',
                '740' => '740 - AWB (Air Waybill)',
                '217' => '217 - PACKING LIST',
                '861' => '861 - Electronic Certificate of Origin (E-CO)',
                '860' => '860 - Persetujuan Impor (PI Kemendag)',
                '810' => '810 - Laporan Surveyor (LS)',
                '852' => '852 - Surat Keterangan Impor (SKI BPOM)',
                '853' => '853 - Sertifikat SDPPI / POSTEL',
                '430' => '430 - Polis Asuransi',
                '911' => '911 - Surat Keputusan Fasilitas',
                '999' => '999 - Dokumen Pendukung Lainnya',
            ],
            'negara' => [
                'CN' => 'CN - China',
                'SG' => 'SG - Singapore',
                'JP' => 'JP - Japan',
                'KR' => 'KR - South Korea',
                'TW' => 'TW - Taiwan',
                'HK' => 'HK - Hong Kong',
                'US' => 'US - United States',
                'DE' => 'DE - Germany',
                'MY' => 'MY - Malaysia',
                'TH' => 'TH - Thailand',
                'VN' => 'VN - Vietnam',
                'IN' => 'IN - India',
                'AU' => 'AU - Australia',
                'ID' => 'ID - Indonesia',
                'PA' => 'PA - Panama',
                'LR' => 'LR - Liberia',
            ],
            'kemasan' => [
                'CT' => 'CT - Carton',
                'TK' => 'TK - Tank, rectangular',
                'PK' => 'PK - Package',
                'PX' => 'PX - Pallet',
                'BG' => 'BG - Bag',
                'DR' => 'DR - Drum',
                'BX' => 'BX - Box',
                'RL' => 'RL - Roll',
                'CS' => 'CS - Case',
                'BE' => 'BE - Bundle',
            ],
            'ukuran_kontainer' => ['20', '40', '45', '60'],
            'jenis_kontainer' => [
                '8' => '8 - FCL',
                '7' => '7 - LCL',
                '4' => '4 - EMPTY',
            ],
            'tipe_kontainer' => [
                '1' => '1 - GENERAL / DRY CARGO',
                '2' => '2 - REEFER',
                '3' => '3 - OPEN TOP',
                '4' => '4 - FLAT RACK',
                '5' => '5 - TANK',
            ],
            'jenis_transaksi' => [
                'KMD' => 'KMD - Pembayaran Kemudian',
                'NTR' => 'NTR - Transaksi Jual Beli',
                'FOB' => 'FOB - Transaksi FOB',
                'LAI' => 'LAI - Transaksi Lainnya',
            ],
            'incoterm' => ['CIF', 'FOB', 'CFR', 'EXW', 'FCA', 'CIP', 'DDP'],
            'satuan' => ['PCE', 'TNE', 'KGM', 'SET', 'C62', 'CT', 'LTR', 'MTR', 'DZN'],
            'fasilitas_tarif' => [
                '1' => 'Dibayar',
                '2' => 'Ditanggung Pemerintah',
                '4' => 'Ditunda',
                '5' => 'Tidak Dipungut',
                '6' => 'Dibebaskan',
                '7' => 'Sudah Dilunasi',
            ],
        ];
    }

    /**
     * Siapkan state awal Alpine untuk 9 tahap BC 2.0 wizard
     */
    protected function buildInitialWizardState(
        CeisaDocument $bc20,
        array $payload,
        array $header,
        array $barangList,
        array $dokumenList,
        ?Shipment $shipment
    ): array {
        $entitasByKode = collect($payload['entitas'] ?? [])->keyBy('kodeEntitas');
        $eImp = $entitasByKode->get('1', []);
        $ePemilik = $entitasByKode->get('7', []);
        $ePengirim = $entitasByKode->get('9', []);
        $ePenjual = $entitasByKode->get('10', []);
        $ePemusatan = $entitasByKode->get('11', $eImp);
        $ePpjk = $entitasByKode->get('4', []);

        $pungutanDefault = $payload['pungutan'] ?? null;
        $angkut = ($payload['pengangkut'][0] ?? []);

        $formattedBarang = array_values(array_map(function ($b, $idx) {
            $tarifs = collect($b['barangTarif'] ?? [])->keyBy('kodeJenisPungutan');
            $bm = $tarifs->get('BM', []);
            $ppn = $tarifs->get('PPN', []);
            $pph = $tarifs->get('PPH', []);
            return [
                'seri' => (int) ($b['seriBarang'] ?? ($idx + 1)),
                'group' => (int) ($b['groupBarang'] ?? ($b['group'] ?? 1)),
                'hs_code' => (string) ($b['posTarif'] ?? ($b['hs_code'] ?? ($b['kodeHs'] ?? '84713020'))),
                'pernyataan_lartas' => (($b['pernyataanLartas'] ?? 'T') === 'Y' ? 'LARTAS' : 'NON'),
                'uraian' => (string) ($b['uraian'] ?? ($b['uraianBarang'] ?? '')),
                'merk' => (string) ($b['merk'] ?? 'TANPA MEREK'),
                'tipe' => (string) ($b['tipe'] ?? 'TANPA TIPE'),
                'spesifikasi' => (string) ($b['spesifikasiLain'] ?? '-'),
                'negara_asal' => (string) ($b['kodeNegaraAsal'] ?? 'CN'),
                'jumlah' => (float) ($b['jumlahSatuan'] ?? ($b['jumlah'] ?? 1)),
                'satuan' => (string) ($b['kodeSatuanBarang'] ?? ($b['satuan'] ?? 'PCE')),
                'jumlah_kemasan' => (float) ($b['jumlahKemasan'] ?? ($b['jumlah_kemasan'] ?? 1)),
                'kemasan' => (string) ($b['kodeJenisKemasan'] ?? ($b['jenisKemasan'] ?? 'CT')),
                'netto' => (float) ($b['netto'] ?? 0),
                'bruto' => (float) ($b['bruto'] ?? 0),
                'fob' => (float) ($b['cif'] ?? ($b['fob'] ?? 0)),
                'tarif_bm' => (float) ($bm['tarif'] ?? ($b['tarifBm'] ?? ($b['tarif_bm'] ?? 0))),
                'tarif_ppn' => (float) ($ppn['tarif'] ?? ($b['tarifPpn'] ?? ($b['tarif_ppn'] ?? 11))),
                'tarif_pph' => (float) ($pph['tarif'] ?? ($b['tarifPph'] ?? ($b['tarif_pph'] ?? 2.5))),
                'kode_fasilitas_bm' => (string) ($bm['kodeFasilitasTarif'] ?? '1'),
                'kode_fasilitas_ppn' => (string) ($ppn['kodeFasilitasTarif'] ?? '1'),
                'kode_fasilitas_pph' => (string) ($pph['kodeFasilitasTarif'] ?? '1'),
                'barang_dokumen' => $b['barangDokumen'] ?? [],
                'hs_info' => '',
            ];
        }, $barangList, array_keys($barangList)));

        return [
            'header' => [
                'pelabuhan_bongkar' => (string) ($payload['kodePelTujuan'] ?? ($header['kodePelTujuan'] ?? ($shipment?->pelabuhan_bongkar ?? 'IDTPP'))),
                'kode_kantor' => (string) ($bc20->kode_kantor ?? ($payload['kodeKantor'] ?? '040300')),
                'kode_jenis_prosedur' => (string) ($payload['kodeJenisProsedur'] ?? '1'),
                'kode_jenis_impor' => (string) ($payload['kodeJenisImpor'] ?? '1'),
                'kode_cara_bayar' => (string) ($payload['kodeCaraBayar'] ?? '1'),
            ],
            'entitas' => [
                'importir_jenis_identitas' => (string) ($eImp['kodeJenisIdentitas'] ?? '6'),
                'importir_npwp' => (string) ($eImp['nomorIdentitas'] ?? ($bc20->npwp_perusahaan_lokal ?? config('ceisa.company.npwp'))),
                'importir_nama' => (string) ($eImp['namaEntitas'] ?? ($bc20->nama_perusahaan_lokal ?? config('ceisa.company.name'))),
                'importir_status' => (string) ($eImp['kodeStatus'] ?? 'LAINNYA'),
                'importir_api' => (string) ($eImp['kodeJenisApi'] ?? '01'),
                'importir_nib' => (string) ($eImp['nibEntitas'] ?? ($shipment?->client?->nib ?? config('ceisa.company.nib'))),
                'importir_alamat' => (string) ($eImp['alamatEntitas'] ?? ($shipment?->client?->address ?? config('ceisa.company.address'))),

                'pemilik_jenis_identitas' => (string) ($ePemilik['kodeJenisIdentitas'] ?? '6'),
                'pemilik_npwp' => (string) ($ePemilik['nomorIdentitas'] ?? ($eImp['nomorIdentitas'] ?? '')),
                'pemilik_nama' => (string) ($ePemilik['namaEntitas'] ?? ($eImp['namaEntitas'] ?? '')),
                'pemilik_alamat' => (string) ($ePemilik['alamatEntitas'] ?? ($eImp['alamatEntitas'] ?? '')),
                'kode_afiliasi' => (string) ($ePemilik['kodeAfiliasi'] ?? 'TAH'),

                'pemusatan_jenis_identitas' => (string) ($ePemusatan['kodeJenisIdentitas'] ?? '6'),
                'pemusatan_npwp' => (string) ($ePemusatan['nomorIdentitas'] ?? ($eImp['nomorIdentitas'] ?? '')),
                'pemusatan_nama' => (string) ($ePemusatan['namaEntitas'] ?? ($eImp['namaEntitas'] ?? '')),
                'pemusatan_alamat' => (string) ($ePemusatan['alamatEntitas'] ?? ($eImp['alamatEntitas'] ?? '')),

                'supplier_nama' => (string) ($ePengirim['namaEntitas'] ?? ($bc20->nama_mitra_luar_negeri ?? 'OVERSEAS SUPPLIER CO., LTD.')),
                'supplier_negara' => (string) ($ePengirim['kodeNegara'] ?? ($bc20->negara_mitra_luar_negeri ?? 'CN')),
                'supplier_alamat' => (string) ($ePengirim['alamatEntitas'] ?? '88 Industrial Park, Harbor City'),

                'seller_nama' => (string) ($ePenjual['namaEntitas'] ?? ($ePengirim['namaEntitas'] ?? ($bc20->nama_mitra_luar_negeri ?? ''))),
                'seller_negara' => (string) ($ePenjual['kodeNegara'] ?? ($ePengirim['kodeNegara'] ?? ($bc20->negara_mitra_luar_negeri ?? 'CN'))),
                'seller_alamat' => (string) ($ePenjual['alamatEntitas'] ?? ($ePengirim['alamatEntitas'] ?? '')),

                'ppjk_jenis_identitas' => (string) ($ePpjk['kodeJenisIdentitas'] ?? '6'),
                'ppjk_npwp' => (string) ($ePpjk['nomorIdentitas'] ?? config('ceisa.company.npwp')),
                'ppjk_nama' => (string) ($ePpjk['namaEntitas'] ?? config('ceisa.company.name')),
                'ppjk_alamat' => (string) ($ePpjk['alamatEntitas'] ?? config('ceisa.company.address')),
            ],
            'dokumen' => $dokumenList,
            'pengangkut' => [
                'kode_tutup_pu' => (string) ($payload['kodeTutupPu'] ?? '11'),
                'nomor_bc11' => (string) ($bc20->nomor_bc11 ?? ($payload['nomorBc11'] ?? '')),
                'tanggal_bc11' => $bc20->tanggal_bc11?->format('Y-m-d') ?? (string) ($payload['tanggalBc11'] ?? ''),
                'pos_bc11' => (string) ($bc20->pos_bc11 ?? ($payload['posBc11'] ?? '')),
                'subpos_bc11' => (string) ($bc20->subpos_bc11 ?? ($payload['subposBc11'] ?? '')),
                'subsubpos_bc11' => (string) ($payload['subsubposBc11'] ?? ''),
                'cara_angkut' => (string) ($angkut['kodeCaraAngkut'] ?? ($shipment?->cara_angkut ?? '1')),
                'nama_sarana_pengangkut' => (string) ($angkut['namaPengangkut'] ?? ($shipment?->nama_kapal ?? 'COSCO SHIPPING')),
                'nomor_voyage' => (string) ($angkut['nomorPengangkut'] ?? ($shipment?->voyage_no ?? '001E')),
                'bendera_kapal' => (string) ($angkut['kodeBendera'] ?? 'CN'),
                'tanggal_tiba' => $bc20->tanggal_tiba?->format('Y-m-d') ?? (string) ($payload['tanggalTiba'] ?? date('Y-m-d')),
                'pelabuhan_muat' => (string) ($payload['kodePelMuat'] ?? ($shipment?->pelabuhan_muat ?? 'CNSHA')),
                'pelabuhan_transit' => (string) ($payload['kodePelTransit'] ?? ''),
                'pelabuhan_bongkar' => (string) ($payload['kodePelTujuan'] ?? ($shipment?->pelabuhan_bongkar ?? 'IDTPP')),
                'kode_tps' => (string) ($bc20->kode_tps ?? ($payload['kodeTps'] ?? 'TPS1')),
                'noa_document_id' => $payload['noaDocumentId'] ?? null,
            ],
            'kemasan' => array_values($payload['kemasan'] ?? [[
                'seriKemasan' => 1,
                'kodeJenisKemasan' => 'CT',
                'jumlahKemasan' => (int) ($bc20->total_kemasan ?? 100),
                'merkKemasan' => '-',
            ]]),
            'kontainer' => array_values($payload['kontainer'] ?? [[
                'seriKontainer' => 1,
                'nomorKontainer' => explode(',', (string) ($shipment?->nomor_kontainer ?? 'TEMU1234567'))[0],
                'kodeUkuranKontainer' => '40',
                'kodeJenisKontainer' => '8',
                'kodeTipeKontainer' => '1',
            ]]),
            'transaksi' => [
                'kode_valuta' => (string) ($bc20->kode_valuta ?? 'USD'),
                'ndbm_kurs' => (float) ($bc20->ndbm_kurs ?? 15850),
                'kode_jenis_nilai' => (string) ($payload['kodeJenisNilai'] ?? 'KMD'),
                'kode_incoterm' => (string) ($payload['kodeIncoterm'] ?? 'FOB'),
                'fob' => (float) ($header['fob'] ?? ($payload['fob'] ?? ($bc20->total_nilai ?? 0))),
                'biaya_tambahan' => (float) ($payload['biayaTambahan'] ?? 0),
                'biaya_pengurang' => (float) ($payload['biayaPengurang'] ?? 0),
                'freight' => (float) ($header['freight'] ?? ($payload['freight'] ?? 0)),
                'kode_asuransi' => (string) ($payload['kodeAsuransi'] ?? 'LN'),
                'asuransi' => (float) ($header['asuransi'] ?? ($payload['asuransi'] ?? 0)),
                'vd' => (float) ($payload['vd'] ?? 0),
                'bruto' => (float) ($bc20->total_bruto ?? ($payload['bruto'] ?? 0)),
                'netto' => (float) ($bc20->total_netto ?? ($payload['netto'] ?? 0)),
                'freight_document_id' => $payload['freightDocumentId'] ?? null,
            ],
            'barang' => $formattedBarang,
            'pungutan' => $pungutanDefault,
            'pernyataan' => [
                'nama_ttd' => (string) ($payload['namaTtd'] ?? (auth()->user()?->name ?? 'AGUNG PRASETYO')),
                'tanggal_ttd' => (string) ($payload['tanggalTtd'] ?? date('Y-m-d')),
                'kota_ttd' => (string) ($payload['kotaTtd'] ?? 'JAKARTA'),
                'jabatan_ttd' => (string) ($payload['jabatanTtd'] ?? 'KUASA PENGUSAHA / PPJK'),
            ],
        ];
    }

    /**
     * Petakan state JSON 9 tahap menjadi array input bagi DocumentBC20Service::buildPayload()
     */
    protected function mergeWizardPayloadToBuildInput(array $w, CeisaDocument $bc20): array
    {
        $h = $w['header'] ?? [];
        $e = $w['entitas'] ?? [];
        $p = $w['pengangkut'] ?? [];
        $t = $w['transaksi'] ?? [];
        $ttd = $w['pernyataan'] ?? [];
        $docs = $w['dokumen'] ?? [];

        $invDoc = collect($docs)->firstWhere('kodeDokumen', '380');

        return [
            'kode_kantor' => $h['kode_kantor'] ?? $bc20->kode_kantor,
            'pelabuhan_bongkar' => $p['pelabuhan_bongkar'] ?? ($h['pelabuhan_bongkar'] ?? 'IDTPP'),
            'pelabuhan_muat' => $p['pelabuhan_muat'] ?? 'CNSHA',
            'pelabuhan_transit' => $p['pelabuhan_transit'] ?? null,
            'kode_jenis_prosedur' => $h['kode_jenis_prosedur'] ?? '1',
            'kode_jenis_impor' => $h['kode_jenis_impor'] ?? '1',
            'kode_cara_bayar' => $h['kode_cara_bayar'] ?? '1',

            'importir_jenis_identitas' => $e['importir_jenis_identitas'] ?? '6',
            'importir_npwp' => $e['importir_npwp'] ?? $bc20->npwp_perusahaan_lokal,
            'importir_nama' => $e['importir_nama'] ?? $bc20->nama_perusahaan_lokal,
            'importir_status' => $e['importir_status'] ?? 'LAINNYA',
            'importir_api' => $e['importir_api'] ?? '01',
            'importir_nib' => $e['importir_nib'] ?? config('ceisa.company.nib'),
            'importir_alamat' => $e['importir_alamat'] ?? config('ceisa.company.address'),

            'pemilik_jenis_identitas' => $e['pemilik_jenis_identitas'] ?? '6',
            'pemilik_npwp' => $e['pemilik_npwp'] ?? ($e['importir_npwp'] ?? null),
            'pemilik_nama' => $e['pemilik_nama'] ?? ($e['importir_nama'] ?? null),
            'pemilik_alamat' => $e['pemilik_alamat'] ?? ($e['importir_alamat'] ?? null),
            'kode_afiliasi' => $e['kode_afiliasi'] ?? 'TAH',

            'pemusatan_jenis_identitas' => $e['pemusatan_jenis_identitas'] ?? '6',
            'pemusatan_npwp' => $e['pemusatan_npwp'] ?? ($e['importir_npwp'] ?? null),
            'pemusatan_nama' => $e['pemusatan_nama'] ?? ($e['importir_nama'] ?? null),
            'pemusatan_alamat' => $e['pemusatan_alamat'] ?? ($e['importir_alamat'] ?? null),

            'supplier_nama' => $e['supplier_nama'] ?? $bc20->nama_mitra_luar_negeri,
            'supplier_negara' => $e['supplier_negara'] ?? $bc20->negara_mitra_luar_negeri,
            'supplier_alamat' => $e['supplier_alamat'] ?? 'Overseas Address',

            'seller_nama' => $e['seller_nama'] ?? ($e['supplier_nama'] ?? $bc20->nama_mitra_luar_negeri),
            'seller_negara' => $e['seller_negara'] ?? ($e['supplier_negara'] ?? $bc20->negara_mitra_luar_negeri),
            'seller_alamat' => $e['seller_alamat'] ?? ($e['supplier_alamat'] ?? 'Overseas Address'),

            'ppjk_jenis_identitas' => $e['ppjk_jenis_identitas'] ?? '6',
            'ppjk_npwp' => $e['ppjk_npwp'] ?? config('ceisa.company.npwp'),
            'ppjk_nama' => $e['ppjk_nama'] ?? config('ceisa.company.name'),
            'ppjk_alamat' => $e['ppjk_alamat'] ?? config('ceisa.company.address'),

            'dokumen' => array_values(array_map(function ($d, $idx) {
                return [
                    'seriDokumen' => $idx + 1,
                    'kodeDokumen' => (string) ($d['kodeDokumen'] ?? '380'),
                    'nomorDokumen' => (string) ($d['nomorDokumen'] ?? ''),
                    'tanggalDokumen' => (string) ($d['tanggalDokumen'] ?? date('Y-m-d')),
                    'shipmentDocumentId' => $d['shipmentDocumentId'] ?? null,
                    'ecooStatus' => (string) ($d['ecooStatus'] ?? '-'),
                    'linkedSeri' => (string) ($d['linkedSeri'] ?? ''),
                ];
            }, $docs, array_keys($docs))),
            'invoice_no' => $invDoc['nomorDokumen'] ?? ('INV/' . date('Ymd')),
            'invoice_date' => $invDoc['tanggalDokumen'] ?? date('Y-m-d'),

            'kode_tutup_pu' => $p['kode_tutup_pu'] ?? '11',
            'nomor_bc11' => $p['nomor_bc11'] ?? null,
            'tanggal_bc11' => $p['tanggal_bc11'] ?? null,
            'pos_bc11' => $p['pos_bc11'] ?? null,
            'subpos_bc11' => $p['subpos_bc11'] ?? null,
            'subsubpos_bc11' => $p['subsubpos_bc11'] ?? null,
            'cara_angkut' => $p['cara_angkut'] ?? '1',
            'nama_sarana_pengangkut' => $p['nama_sarana_pengangkut'] ?? 'COSCO SHIPPING',
            'nomor_voyage' => $p['nomor_voyage'] ?? '001E',
            'bendera_kapal' => $p['bendera_kapal'] ?? 'CN',
            'tanggal_tiba' => $p['tanggal_tiba'] ?? null,
            'kode_tps' => $p['kode_tps'] ?? null,
            'noa_document_id' => $p['noa_document_id'] ?? null,

            'kemasan' => $w['kemasan'] ?? [],
            'kontainer' => $w['kontainer'] ?? [],

            'kode_valuta' => $t['kode_valuta'] ?? $bc20->kode_valuta,
            'ndbm_kurs' => (float) ($t['ndbm_kurs'] ?? $bc20->ndbm_kurs),
            'kode_jenis_nilai' => $t['kode_jenis_nilai'] ?? 'KMD',
            'kode_incoterm' => $t['kode_incoterm'] ?? 'FOB',
            'fob' => (float) ($t['fob'] ?? 0),
            'biaya_tambahan' => (float) ($t['biaya_tambahan'] ?? 0),
            'biaya_pengurang' => (float) ($t['biaya_pengurang'] ?? 0),
            'freight' => (float) ($t['freight'] ?? 0),
            'kode_asuransi' => $t['kode_asuransi'] ?? 'LN',
            'asuransi' => (float) ($t['asuransi'] ?? 0),
            'vd' => (float) ($t['vd'] ?? 0),
            'bruto' => (float) ($t['bruto'] ?? 0),
            'netto' => (float) ($t['netto'] ?? 0),
            'freight_document_id' => $t['freight_document_id'] ?? null,

            'barang' => $w['barang'] ?? [],

            'nama_ttd' => $ttd['nama_ttd'] ?? (auth()->user()?->name ?? 'AGUNG PRASETYO'),
            'tanggal_ttd' => $ttd['tanggal_ttd'] ?? date('Y-m-d'),
            'kota_ttd' => $ttd['kota_ttd'] ?? 'JAKARTA',
            'jabatan_ttd' => $ttd['jabatan_ttd'] ?? 'DIREKTUR',
        ];
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
