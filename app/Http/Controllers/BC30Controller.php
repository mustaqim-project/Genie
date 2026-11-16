<?php

namespace App\Http\Controllers;

use App\Models\CeisaApiLog;
use App\Models\CeisaDocument;
use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Services\Ceisa\CeisaDocumentService;
use App\Services\Ceisa\DocumentBC30Service;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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
            new Middleware('permission:document.edit|document.edit_after_ceisa', only: ['edit', 'update', 'uploadWizardDocument', 'resyncExtraction']),
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

        if ($request->filled('lartas')) {
            $filterLartas = $request->lartas;
            if ($filterLartas === 'yes' || $filterLartas === '1') {
                $allDocs = $allDocs->filter(fn($d) => $d->lartas_info['has_lartas'])->values();
            } elseif ($filterLartas === 'no' || $filterLartas === '0') {
                $allDocs = $allDocs->filter(fn($d) => !$d->lartas_info['has_lartas'])->values();
            }
        }

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
            'submitted' => CeisaDocument::where('jenis_dokumen', '30')->where('status', 'SUBMITTED')->count(),
            'approved' => CeisaDocument::where('jenis_dokumen', '30')->whereIn('status', ['NPE', 'SPPB'])->count(),
            'rejected' => CeisaDocument::where('jenis_dokumen', '30')->where('status', 'REJECT')->count(),
            'total_nilai_idr' => CeisaDocument::where('jenis_dokumen', '30')->sum('total_nilai_idr'),
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

        $clients = \App\Models\Client::orderBy('name')->get();
        $vendors = \App\Models\ShippingVendor::orderBy('name')->get();

        return view('bc30.create', compact('defaultNomorAju', 'kantorList', 'shipments', 'selectedShipment', 'activeKurs', 'itemList', 'clients', 'vendors'));
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

        return redirect()->route('bc30.edit', $doc->id)
            ->with('success', "Dokumen BC 3.0 (PEB) berhasil dibuat dengan Nomor Aju: {$nomorAju}. Silakan tinjau pada wizard 9 tahap.");
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

        $isBillingLocked = $bc30->isBillingLocked();
        $isFromCeisaPull = $bc30->isFromCeisaPull();
        $canEditData = $bc30->canEditData();
        $canSubmitCeisa = $bc30->canSubmitCeisa();

        $dokumenRef = config('ceisa.kode_dokumen', [
            '380' => 'Commercial Invoice',
            '217' => 'Packing List',
            '705' => 'Bill of Lading / Booking Confirmation',
            '861' => 'Certificate of Origin (e-COO)',
            '430' => 'Polis Asuransi',
        ]);
        $payload = $bc30->raw_payload ?? [];
        $dokumenList = $payload['dokumen'] ?? [];
        $barangList = $payload['barang'] ?? [];

        return view('bc30.show', compact('bc30', 'shipment', 'completionRate', 'threeWay', 'taxes', 'isBillingLocked', 'isFromCeisaPull', 'canEditData', 'canSubmitCeisa', 'dokumenRef', 'dokumenList', 'barangList'));
    }

    public function submit(CeisaDocument $bc30)
    {
        if (!$bc30->canSubmitCeisa()) {
            return back()->with('error', 'Dokumen tidak dapat disubmit ulang ke CEISA karena Billing / NPE resmi telah diterbitkan oleh DJBC.');
        }

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
     * Tampilan Wizard 9-Tahap & Split-Screen Dokumen BC 3.0 (PEB)
     */
    public function edit(CeisaDocument $bc30)
    {
        $isBillingLocked = $bc30->isBillingLocked();
        $isFromCeisaPull = $bc30->isFromCeisaPull();
        $canEditData = $bc30->canEditData();
        $canSubmitCeisa = $bc30->canSubmitCeisa();
        $isReadOnly = !$canEditData;
        $isDraft = ($bc30->status === 'DRAFT');

        $user = auth()->user();
        if ($canEditData && $user) {
            if ($isDraft) {
                if (!$user->isSuperAdmin() && !$user->hasAnyPermission(['document.edit', 'document.edit_after_ceisa'])) {
                    abort(403, 'Akses Ditolak: Anda memerlukan hak akses izin "document.edit" untuk mengubah draft dokumen ini.');
                }
            } else {
                if (!$user->isSuperAdmin() && !$user->hasPermission('document.edit_after_ceisa') && !$isFromCeisaPull) {
                    abort(403, 'Dokumen telah diajukan ke CEISA 4.0. Anda memerlukan hak akses izin "document.edit_after_ceisa" untuk merevisi data dokumen yang sudah terkirim.');
                }
            }
        }

        $kantorList = config('ceisa.kantor_pabean', []);
        $activeValuta = $bc30->kode_valuta ?? 'USD';
        $activeKurs = $this->kursService->getActiveKurs($activeValuta);
        $payload = $bc30->raw_payload ?? [];
        $header = $payload['header'] ?? [];
        $barangList = $payload['barang'] ?? [];

        $shipment = $bc30->shipment ?? $this->workflowService->ensureShipmentForCeisaDocument($bc30);
        $shipment->load(['documents', 'items', 'client']);

        $refOptions = $this->buildReferenceOptions();
        [$previewFiles, $dokumenList] = $this->buildPreviewAndLinkedDocs($shipment, $payload);
        $wizardState = $this->buildInitialWizardState($bc30, $payload, $header, $barangList, $dokumenList, $shipment);

        return view('bc30.edit', compact(
            'bc30',
            'kantorList',
            'activeKurs',
            'payload',
            'header',
            'barangList',
            'shipment',
            'isDraft',
            'isBillingLocked',
            'isFromCeisaPull',
            'canEditData',
            'canSubmitCeisa',
            'isReadOnly',
            'refOptions',
            'previewFiles',
            'wizardState'
        ));
    }

    /**
     * Simpan pembaruan data dokumen BC 3.0 (PEB)
     */
    public function update(Request $request, CeisaDocument $bc30)
    {
        if (!$bc30->canEditData()) {
            $msg = "Dokumen BC 3.0 (Nomor Aju: {$bc30->nomor_aju}) telah berstatus Billing / Respon Resmi dan terkunci permanen untuk menjaga integritas data pabean.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->route('bc30.show', $bc30->id)->with('error', $msg);
        }

        $user = auth()->user();
        $isDraft = ($bc30->status === 'DRAFT');

        $wizardInput = $request->input('wizard_payload');
        if (is_string($wizardInput)) {
            $decoded = json_decode($wizardInput, true);
            $wizardInput = is_array($decoded) ? $decoded : null;
        }

        if (is_array($wizardInput)) {
            $validated = $this->mergeWizardPayloadToBuildInput($wizardInput, $bc30);
        } else {
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
                'nama_sarana_pengangkut' => 'required|string',
                'pelabuhan_muat' => 'required|string',
                'pelabuhan_bongkar' => 'required|string',
                'barang' => 'required|array|min:1',
            ]);
        }

        $validated['nomor_aju'] = $bc30->nomor_aju;

        $payload = DocumentBC30Service::buildPayload($validated);

        $fob = $payload['header']['fob'];
        $fobRupiah = $payload['header']['fobRupiah'];
        $kantorName = config('ceisa.kantor_pabean.' . $validated['kode_kantor'], $bc30->nama_kantor);

        $desc = $isDraft 
            ? 'Draft dokumen pabean BC 3.0 (PEB) diperbarui oleh ' . $user->name . '.'
            : 'Dokumen pabean BC 3.0 disesuaikan oleh ' . $user->name . ' pada ' . now()->format('d/m/Y H:i') . '.';

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
            'total_kemasan' => $validated['jumlah_kemasan'] ?? ($request->jumlah_kemasan ?? $bc30->total_kemasan),
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

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Draft dokumen BC 3.0 ({$bc30->nomor_aju}) berhasil disimpan.",
                'total_fob' => $fob,
                'total_fob_idr' => $fobRupiah,
            ]);
        }

        return redirect()->route('bc30.show', $bc30->id)
            ->with('success', "Dokumen BC 3.0 (Nomor Aju: {$bc30->nomor_aju}) berhasil diperbarui!");
    }

    /**
     * Unggah berkas fisik dokumen pelengkap dari Wizard BC 3.0
     */
    public function uploadWizardDocument(Request $request, CeisaDocument $bc30)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp,txt,csv|max:10240',
            'kode_dokumen' => 'nullable|string|max:10',
            'nomor_dokumen' => 'nullable|string|max:100',
            'tanggal_dokumen' => 'nullable|date',
            'purpose' => 'nullable|string|in:dokumen,pebdraft,freight',
        ]);

        $shipment = $bc30->shipment ?? $this->workflowService->ensureShipmentForCeisaDocument($bc30);
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
            'pebdraft' => 'Draft PEB / Booking',
            'freight' => 'Bukti Freight Ekspor',
            default => "Lampiran CEISA 3.0 Kode {$kodeDok}",
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
            ]
        ]);
    }

    /**
     * Baca ulang seluruh berkas PDF/dokumen fisik tersimpan dan ekstrak data kepabeanan untuk form wizard BC 3.0
     */
    public function resyncExtraction(CeisaDocument $bc30, Request $request, \App\Services\Shipment\DocumentExtractionService $extractionService)
    {
        if (!$bc30->canEditData()) {
            return response()->json(['success' => false, 'message' => 'Dokumen telah terbit Billing / NPE resmi di CEISA dan terkunci permanen.'], 403);
        }

        $shipment = $bc30->shipment ?? $this->workflowService->ensureShipmentForCeisaDocument($bc30);
        $docs = $shipment->documents
            ->filter(fn($d) => !empty($d->file_path) && Storage::disk('public')->exists($d->file_path))
            ->values();

        if ($docs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada berkas fisik dokumen (PDF/Gambar) yang tersimpan pada shipment ini.',
            ], 422);
        }

        $filePaths = [];
        foreach ($docs as $d) {
            $filePaths[] = Storage::disk('public')->path($d->file_path);
        }

        $extracted = $extractionService->extractAllDocuments($filePaths);

        return response()->json([
            'success' => true,
            'message' => 'Ekstraksi dokumen kargo berhasil disinkronkan.',
            'extracted' => $extracted,
        ]);
    }

    /**
     * Mengembalikan status dokumen setelah kirim CEISA kembali ke DRAFT
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

        if ($bc30->shipment && in_array($bc30->shipment->status_tahap, ['CEISA_SUBMITTED', 'BILLING_ISSUED'])) {
            $bc30->shipment->update([
                'status_tahap' => 'DRAFT_REVIEW',
            ]);
        }

        return redirect()->route('bc30.show', $bc30->id)
            ->with('success', "Dokumen BC 3.0 berhasil dikembalikan ke status DRAFT. Anda kini dapat mengedit data.");
    }

    /**
     * Format entri preview dokumen untuk split-screen viewer
     */
    protected function formatPreviewFileEntry(Shipment $shipment, ShipmentDocument $doc): array
    {
        $ext = strtolower(pathinfo((string) ($doc->nama_file ?? $doc->file_path), PATHINFO_EXTENSION));
        $previewable = in_array($ext, ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'txt', 'csv'], true);

        return [
            'id' => $doc->id,
            'nama_file' => $doc->nama_file ?? basename($doc->file_path),
            'jenis_dokumen' => $doc->jenis_dokumen,
            'file_path' => $doc->file_path,
            'preview_url' => route('core.document.preview-doc', ['id' => $shipment->id, 'docId' => $doc->id]),
            'download_url' => route('core.document.download-doc', ['id' => $shipment->id, 'docId' => $doc->id]),
            'is_image' => in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true),
            'is_pdf' => ($ext === 'pdf'),
            'is_previewable' => $previewable,
            'catatan' => $doc->catatan,
        ];
    }

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
                ['seriDokumen' => 2, 'kodeDokumen' => '217', 'nomorDokumen' => 'PL/' . ($shipment->nomor_bl_awb ?? date('Ymd')), 'tanggalDokumen' => $docDate],
                ['seriDokumen' => 3, 'kodeDokumen' => '705', 'nomorDokumen' => $shipment->nomor_bl_awb ?? ('BL-' . date('Ymd')), 'tanggalDokumen' => $docDate],
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
            ];
        }

        return [$previewFiles, $dokumenList];
    }

    protected function buildReferenceOptions(): array
    {
        return [
            'pelabuhan' => [
                'IDTPP' => 'IDTPP - Tanjung Priok Jakarta',
                'IDSUB' => 'IDSUB - Tanjung Perak Surabaya',
                'IDBLW' => 'IDBLW - Belawan Medan',
                'IDSRG' => 'IDSRG - Tanjung Emas Semarang',
                'IDMAK' => 'IDMAK - Soekarno Hatta Makassar',
                'USLAX' => 'USLAX - Los Angeles, United States',
                'SGSIN' => 'SGSIN - Port of Singapore',
                'CNSHG' => 'CNSHG - Shanghai, China',
                'JPYOK' => 'JPYOK - Yokohama, Japan',
                'NLRTM' => 'NLRTM - Rotterdam, Netherlands',
            ],
            'jenis_ekspor' => [
                '1' => '1 - Ekspor Biasa',
                '2' => '2 - Ekspor Terikat Fasilitas (KITE/KB)',
                '3' => '3 - Ekspor Kembali (Re-Ekspor)',
                '4' => '4 - Ekspor Komoditas Khusus',
            ],
            'kategori_ekspor' => [
                '1' => '1 - Umum (General Cargo)',
                '2' => '2 - Barang Curah (Bulk Cargo)',
                '3' => '3 - Minyak & Gas (Oil & Gas)',
                '4' => '4 - Hasil Perikanan & Pertanian',
            ],
            'cara_dagang' => [
                '1' => '1 - Biasa / Normal',
                '2' => '2 - Imbal Dagang (Barter/Counter Trade)',
                '3' => '3 - Konsinyasi (Consignment)',
            ],
            'cara_bayar' => [
                '1' => '1 - Pembayaran Kemudian (Open Account)',
                '2' => '2 - Letter of Credit (L/C)',
                '3' => '3 - Pembayaran Dimuka (Advance Payment)',
                '4' => '4 - Collection / Dokumen Penagihan',
            ],
            'kode_dokumen' => config('ceisa.kode_dokumen', [
                '380' => 'Commercial Invoice',
                '217' => 'Packing List',
                '705' => 'Bill of Lading / Booking Confirmation',
                '861' => 'Certificate of Origin (e-COO)',
                '430' => 'Polis Asuransi',
                '999' => 'Lain-lain / Dokumen Pendukung',
            ]),
        ];
    }

    protected function buildInitialWizardState(
        CeisaDocument $bc30,
        array $payload,
        array $header,
        array $barangList,
        array $dokumenList,
        ?Shipment $shipment
    ): array {
        $entitasByKode = collect($payload['entitas'] ?? [])->keyBy('kodeEntitas');
        $eEksp = $entitasByKode->get('2', []);
        $ePemilik = $entitasByKode->get('7', []);
        $eBuyer = $entitasByKode->get('6', []);
        $ePpjk = $entitasByKode->get('4', []);

        $angkut = ($payload['pengangkut'][0] ?? []);

        if (empty($barangList) && $shipment && $shipment->items->isNotEmpty()) {
            $barangList = $shipment->items->map(function ($it, $idx) use ($bc30) {
                return [
                    'seriBarang' => $idx + 1,
                    'posTarif' => $it->kode_hs ?? '09011110',
                    'uraian' => $it->uraian_barang ?? 'KOMODITAS EKSPOR INDONESIA',
                    'merk' => $it->merk ?? 'INDONESIAN ORIGIN',
                    'tipe' => $it->tipe ?? 'GRADE A',
                    'jumlahSatuan' => (float) ($it->jumlah_kemasan ?? 1),
                    'kodeSatuanBarang' => 'KGM',
                    'jumlahKemasan' => (float) ($it->jumlah_kemasan ?? 1),
                    'kodeJenisKemasan' => $it->jenis_kemasan ?? 'BG',
                    'netto' => (float) ($it->berat_netto ?? 1),
                    'fob' => (float) ($it->nilai_fob ?? ($bc30->total_nilai ?: 1000)),
                    'tarifBk' => (float) ($it->tarif_bk ?? 0.0),
                ];
            })->all();
        }

        $formattedBarang = array_values(array_map(function ($b, $idx) {
            return [
                'seri' => (int) ($b['seriBarang'] ?? ($idx + 1)),
                'hs_code' => (string) ($b['posTarif'] ?? ($b['hs_code'] ?? ($b['kodeHs'] ?? '09011110'))),
                'uraian' => (string) ($b['uraian'] ?? ($b['uraianBarang'] ?? '')),
                'merk' => (string) ($b['merk'] ?? 'INDONESIAN ORIGIN'),
                'tipe' => (string) ($b['tipe'] ?? 'GRADE A'),
                'jumlah' => (float) ($b['jumlahSatuan'] ?? ($b['jumlah'] ?? 1)),
                'satuan' => (string) ($b['kodeSatuanBarang'] ?? ($b['satuan'] ?? 'KGM')),
                'jumlah_kemasan' => (float) ($b['jumlahKemasan'] ?? ($b['jumlah_kemasan'] ?? 1)),
                'kemasan' => (string) ($b['kodeJenisKemasan'] ?? ($b['jenisKemasan'] ?? 'BG')),
                'netto' => (float) ($b['netto'] ?? 1),
                'fob' => (float) ($b['fob'] ?? ($b['cif'] ?? 0)),
                'tarif_bk' => (float) ($b['tarifBk'] ?? ($b['tarif_bk'] ?? 0.0)),
            ];
        }, $barangList, array_keys($barangList)));

        $kursVal = (float) ($bc30->ndbm_kurs > 0 ? $bc30->ndbm_kurs : ($header['ndpbm'] ?? 15850));
        $fobVal = (float) ($bc30->total_nilai > 0 ? $bc30->total_nilai : ($header['fob'] ?? 0));

        return [
            'header' => [
                'pelabuhan_muat' => (string) ($payload['kodePelMuat'] ?? ($header['kodePelMuat'] ?? ($shipment?->pelabuhan_muat ?? 'IDTPP'))),
                'pelabuhan_ekspor' => (string) ($payload['kodePelEkspor'] ?? ($header['kodePelEkspor'] ?? ($shipment?->pelabuhan_muat ?? 'IDTPP'))),
                'pelabuhan_bongkar' => (string) ($payload['kodePelBongkar'] ?? ($header['kodePelBongkar'] ?? ($shipment?->pelabuhan_bongkar ?? 'USLAX'))),
                'kode_kantor' => (string) ($bc30->kode_kantor ?? ($payload['kodeKantor'] ?? '040300')),
                'kode_jenis_ekspor' => (string) ($payload['kodeJenisEkspor'] ?? '1'),
                'kode_kategori_ekspor' => (string) ($payload['kodeKategoriEkspor'] ?? '1'),
                'kode_cara_dagang' => (string) ($payload['kodeCaraDagang'] ?? '1'),
                'kode_cara_bayar' => (string) ($payload['kodeCaraBayar'] ?? '1'),
            ],
            'entitas' => [
                'eksportir_jenis_identitas' => (string) ($eEksp['kodeJenisIdentitas'] ?? '6'),
                'eksportir_npwp' => (string) ($eEksp['nomorIdentitas'] ?? ($bc30->npwp_perusahaan_lokal ?? config('ceisa.company.npwp'))),
                'eksportir_nama' => (string) ($eEksp['namaEntitas'] ?? ($bc30->nama_perusahaan_lokal ?? config('ceisa.company.name'))),
                'eksportir_status' => (string) ($eEksp['kodeStatus'] ?? 'PRODUSEN'),
                'eksportir_nib' => (string) ($eEksp['nibEntitas'] ?? ($shipment?->client?->nib ?? config('ceisa.company.nib'))),
                'eksportir_alamat' => (string) ($eEksp['alamatEntitas'] ?? ($shipment?->client?->address ?? config('ceisa.company.address'))),

                'pemilik_npwp' => (string) ($ePemilik['nomorIdentitas'] ?? ($eEksp['nomorIdentitas'] ?? '')),
                'pemilik_nama' => (string) ($ePemilik['namaEntitas'] ?? ($eEksp['namaEntitas'] ?? '')),
                'pemilik_alamat' => (string) ($ePemilik['alamatEntitas'] ?? ($eEksp['alamatEntitas'] ?? '')),

                'buyer_nama' => (string) ($eBuyer['namaEntitas'] ?? ($bc30->nama_mitra_luar_negeri ?? 'PACIFIC IMPORT DISTRIBUTORS INC')),
                'buyer_negara' => (string) ($eBuyer['kodeNegara'] ?? ($bc30->negara_mitra_luar_negeri ?? 'US')),
                'buyer_alamat' => (string) ($eBuyer['alamatEntitas'] ?? '1200 Pacific Coast Hwy, California, US'),

                'ppjk_npwp' => (string) ($ePpjk['nomorIdentitas'] ?? ''),
                'ppjk_nama' => (string) ($ePpjk['namaEntitas'] ?? ''),
                'ppjk_alamat' => (string) ($ePpjk['alamatEntitas'] ?? ''),
            ],
            'dokumen' => $dokumenList,
            'pengangkut' => [
                'cara_angkut' => (string) ($angkut['kodeCaraAngkut'] ?? '1'),
                'nama_sarana_pengangkut' => (string) ($angkut['namaSaranaPengangkut'] ?? ($shipment?->nama_kapal ?? 'PACIFIC EXPRESS')),
                'voyage_no' => (string) ($angkut['nomorVoyageFlight'] ?? ($shipment?->voyage_no ?? '045N')),
                'bendera_sarana_pengangkut' => (string) ($angkut['kodeBendera'] ?? 'PA'),
                'tanggal_ekspor' => (string) ($angkut['tanggalKeberangkatan'] ?? ($shipment?->etd_date?->format('Y-m-d') ?? date('Y-m-d'))),
                'lokasi_siap_periksa' => (string) ($bc30->lokasi_siap_periksa ?? 'GUDANG PT AGUNG TRANS SOLUSINDO'),
            ],
            'kemasan' => [
                'nomor_kontainer' => (string) ($payload['petiKemas'][0]['nomorPetiKemas'] ?? ($shipment?->nomor_kontainer ?? '')),
                'kode_ukuran_kontainer' => (string) ($payload['petiKemas'][0]['kodeUkuranPetiKemas'] ?? '20'),
                'kode_tipe_kontainer' => (string) ($payload['petiKemas'][0]['kodeTipePetiKemas'] ?? 'FCL'),
                'total_kontainer' => (int) ($bc30->total_kontainer > 0 ? $bc30->total_kontainer : ($shipment?->total_kontainer ?? 1)),
                'jumlah_kemasan' => (int) ($bc30->total_kemasan > 0 ? $bc30->total_kemasan : ($shipment?->total_kemasan ?? 100)),
                'kode_kemasan' => (string) ($payload['kemasan'][0]['kodeJenisKemasan'] ?? ($shipment?->jenis_kemasan ?? 'PK')),
                'bruto' => (float) ($bc30->total_bruto > 0 ? $bc30->total_bruto : ($shipment?->total_bruto ?? 1000)),
                'netto' => (float) ($bc30->total_netto > 0 ? $bc30->total_netto : ($shipment?->total_netto ?? 950)),
            ],
            'transaksi' => [
                'kode_valuta' => (string) ($bc30->kode_valuta ?? ($header['kodeValuta'] ?? 'USD')),
                'ndbm_kurs' => $kursVal,
                'kode_incoterm' => (string) ($header['kodeIncoterm'] ?? 'FOB'),
                'fob' => $fobVal,
                'freight' => (float) ($header['freight'] ?? 0),
                'asuransi' => (float) ($header['asuransi'] ?? 0),
                'nilai_maklon' => (float) ($header['nilaiMaklon'] ?? 0),
                'bank_kode' => (string) ($bc30->bank_kode ?? 'BMRI'),
                'bank_nama' => (string) ($bc30->bank_nama ?? 'PT BANK MANDIRI (PERSERO) TBK'),
            ],
            'barang' => $formattedBarang,
            'pernyataan' => [
                'nama_ttd' => (string) ($header['namaTtd'] ?? ($bc30->pic_pembuat ?? config('ceisa.company.name'))),
                'tanggal_ttd' => (string) ($header['tanggalTtd'] ?? date('Y-m-d')),
                'kota_ttd' => (string) ($header['kotaTtd'] ?? 'JAKARTA'),
                'jabatan_ttd' => (string) ($header['jabatanTtd'] ?? 'DIREKTUR'),
            ]
        ];
    }

    protected function mergeWizardPayloadToBuildInput(array $w, CeisaDocument $bc30): array
    {
        $h = $w['header'] ?? [];
        $e = $w['entitas'] ?? [];
        $p = $w['pengangkut'] ?? [];
        $t = $w['transaksi'] ?? [];
        $k = $w['kemasan'] ?? [];
        $pernyataan = $w['pernyataan'] ?? [];

        return [
            'kode_kantor' => (string) ($h['kode_kantor'] ?? $bc30->kode_kantor),
            'ndbm_kurs' => (float) ($t['ndbm_kurs'] ?? 15850.0),
            'kode_valuta' => (string) ($t['kode_valuta'] ?? 'USD'),
            'fob' => (float) ($t['fob'] ?? 0.0),
            'freight' => (float) ($t['freight'] ?? 0.0),
            'asuransi' => (float) ($t['asuransi'] ?? 0.0),
            'nilai_maklon' => (float) ($t['nilai_maklon'] ?? 0.0),
            'bruto' => (float) ($k['bruto'] ?? 0.0),
            'netto' => (float) ($k['netto'] ?? 0.0),
            'jumlah_kemasan' => (int) ($k['jumlah_kemasan'] ?? 1),
            'kode_kemasan' => (string) ($k['kode_kemasan'] ?? 'PK'),
            'nomor_kontainer' => (string) ($k['nomor_kontainer'] ?? ''),
            'kode_ukuran_kontainer' => (string) ($k['kode_ukuran_kontainer'] ?? '20'),
            'kode_tipe_kontainer' => (string) ($k['kode_tipe_kontainer'] ?? 'FCL'),
            'total_kontainer' => (int) ($k['total_kontainer'] ?? 1),
            'buyer_nama' => (string) ($e['buyer_nama'] ?? $bc30->nama_mitra_luar_negeri),
            'buyer_negara' => (string) ($e['buyer_negara'] ?? $bc30->negara_mitra_luar_negeri),
            'buyer_alamat' => (string) ($e['buyer_alamat'] ?? ''),
            'eksportir_nama' => (string) ($e['eksportir_nama'] ?? config('ceisa.company.name')),
            'eksportir_npwp' => (string) ($e['eksportir_npwp'] ?? config('ceisa.company.npwp')),
            'eksportir_nib' => (string) ($e['eksportir_nib'] ?? config('ceisa.company.nib')),
            'eksportir_alamat' => (string) ($e['eksportir_alamat'] ?? config('ceisa.company.address')),
            'nama_sarana_pengangkut' => (string) ($p['nama_sarana_pengangkut'] ?? 'PACIFIC EXPRESS'),
            'voyage_no' => (string) ($p['voyage_no'] ?? '045N'),
            'cara_angkut' => (string) ($p['cara_angkut'] ?? '1'),
            'bendera_sarana_pengangkut' => (string) ($p['bendera_sarana_pengangkut'] ?? 'PA'),
            'tanggal_ekspor' => (string) ($p['tanggal_ekspor'] ?? date('Y-m-d')),
            'pelabuhan_muat' => (string) ($h['pelabuhan_muat'] ?? 'IDTPP'),
            'pelabuhan_ekspor' => (string) ($h['pelabuhan_ekspor'] ?? 'IDTPP'),
            'pelabuhan_bongkar' => (string) ($h['pelabuhan_bongkar'] ?? 'USLAX'),
            'pic_pembuat' => (string) ($pernyataan['nama_ttd'] ?? null),
            'bank_kode' => (string) ($t['bank_kode'] ?? null),
            'bank_nama' => (string) ($t['bank_nama'] ?? null),
            'lokasi_siap_periksa' => (string) ($p['lokasi_siap_periksa'] ?? null),
            'dokumen' => array_values(array_map(function ($d, $idx) {
                return [
                    'seriDokumen' => $idx + 1,
                    'kodeDokumen' => (string) ($d['kodeDokumen'] ?? '380'),
                    'nomorDokumen' => (string) ($d['nomorDokumen'] ?? ''),
                    'tanggalDokumen' => (string) ($d['tanggalDokumen'] ?? date('Y-m-d')),
                    'shipmentDocumentId' => $d['shipmentDocumentId'] ?? null,
                ];
            }, $w['dokumen'] ?? [])),
            'barang' => array_values(array_map(function ($b, $idx) {
                return [
                    'seriBarang' => $idx + 1,
                    'posTarif' => preg_replace('/[^0-9]/', '', (string) ($b['hs_code'] ?? '09011110')),
                    'uraian' => (string) ($b['uraian'] ?? 'KOMODITAS EKSPOR INDONESIA'),
                    'merk' => (string) ($b['merk'] ?? 'INDONESIAN ORIGIN'),
                    'tipe' => (string) ($b['tipe'] ?? 'GRADE A'),
                    'jumlahSatuan' => (float) ($b['jumlah'] ?? 1),
                    'kodeSatuanBarang' => (string) ($b['satuan'] ?? 'KGM'),
                    'jumlahKemasan' => (float) ($b['jumlah_kemasan'] ?? 1),
                    'kodeJenisKemasan' => (string) ($b['kemasan'] ?? 'BG'),
                    'netto' => (float) ($b['netto'] ?? 1),
                    'fob' => (float) ($b['fob'] ?? 0),
                    'tarifBk' => (float) ($b['tarif_bk'] ?? 0.0),
                ];
            }, $w['barang'] ?? [])),
        ];
    }
}
