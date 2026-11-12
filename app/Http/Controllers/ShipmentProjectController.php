<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Models\ShipmentItem;
use App\Models\ShippingVendor;
use App\Services\Shipment\ShipmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ShipmentProjectController extends Controller
{
    protected ShipmentWorkflowService $workflowService;

    public function __construct(ShipmentWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Tampilan daftar seluruh project shipment (Tim Dokumen)
     * Hak Akses: document.view
     */
    public function index(Request $request)
    {
        Gate::authorize('document.view');

        $query = Shipment::with(['client', 'shippingVendor', 'documents', 'ceisaDocument', 'items.customsDocuments']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('project_no', 'like', "%{$s}%")
                  ->orWhere('nomor_bl_awb', 'like', "%{$s}%")
                  ->orWhere('nama_kapal', 'like', "%{$s}%")
                  ->orWhere('nomor_kontainer', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'IMPORT') {
                $query->where('jenis_layanan', 'IMPORT_BC20');
            } elseif ($type === 'EXPORT') {
                $query->where('jenis_layanan', 'EKSPORT_BC30');
            }
        }

        if ($request->filled('status')) {
            $query->where('status_tahap', $request->status);
        }

        $allShipments = $query->latest()->get();

        // Hitung statistik Lartas produk & proyek
        $lartasShipmentsCount = 0;
        $lartasProductsCount = 0;
        $nonLartasShipmentsCount = 0;

        foreach ($allShipments as $shp) {
            $info = $shp->lartas_info;
            if ($info['has_lartas']) {
                $lartasShipmentsCount++;
                $lartasProductsCount += $info['lartas_count'];
            } else {
                $nonLartasShipmentsCount++;
            }
        }

        // Filter berdasarkan status Lartas
        if ($request->filled('lartas')) {
            $filterLartas = $request->lartas;
            if ($filterLartas === 'yes' || $filterLartas === '1') {
                $allShipments = $allShipments->filter(fn($s) => $s->lartas_info['has_lartas'])->values();
            } elseif ($filterLartas === 'no' || $filterLartas === '0') {
                $allShipments = $allShipments->filter(fn($s) => !$s->lartas_info['has_lartas'])->values();
            }
        }

        // Manual pagination
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10;
        $currentPageItems = $allShipments->slice(($page - 1) * $perPage, $perPage)->values();
        $shipments = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $allShipments->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $clients = Client::orderBy('name')->get();
        $vendors = ShippingVendor::where('is_active', true)->orderBy('name')->get();

        // Statistik Proyek Tim Document
        $stats = [
            'total' => Shipment::count(),
            'collecting' => Shipment::where('status_tahap', 'DOC_COLLECTING')->count(),
            'ready' => Shipment::whereIn('status_tahap', ['DOC_READY', 'HS_DETECTED', 'DRAFT_REVIEW'])->count(),
            'billing' => Shipment::whereIn('status_tahap', ['BILLING_ISSUED', 'DO_PROCESSING', 'DO_RELEASED', 'TRAFFIC_SPPB', 'COMPLETED'])->count(),
            'lartas_shipments' => $lartasShipmentsCount,
            'lartas_products' => $lartasProductsCount,
            'non_lartas_shipments' => $nonLartasShipmentsCount,
        ];

        return view('core.document_index', compact('shipments', 'clients', 'vendors', 'stats'));
    }

    /**
     * AJAX: Pindai & Ekstrak Data dari Berkas Dokumen yang Diunggah (Smart Document Parser)
     * Hak Akses: document.create
     */
    public function parseDocuments(Request $request)
    {
        Gate::authorize('document.create');

        $files = [];
        foreach (['bl_file', 'invoice_file', 'packing_list_file', 'coo_file', 'sc_po_file', 'insurance_file', 'booking_file', 'lartas_file', 'etc_file'] as $singleKey) {
            if ($request->hasFile($singleKey)) {
                $files[$singleKey] = $request->file($singleKey);
            }
        }
        foreach (['bundle_files', 'documents'] as $multiKey) {
            if ($request->hasFile($multiKey)) {
                $files[$multiKey] = $request->file($multiKey);
            }
        }

        if (empty($files)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada berkas dokumen yang diunggah untuk dipindai.',
            ], 422);
        }

        $clientId = $request->input('client_id');
        $aiModel = $request->input('ai_model') ?? $request->input('model');
        $useAi = filter_var($request->input('use_ai', false), FILTER_VALIDATE_BOOLEAN);
        $preferredType = $request->input('type') ?: null;

        $extracted = $this->workflowService->parseDocumentsForPreview($files, $clientId, $aiModel, $useAi, $preferredType);

        return response()->json([
            'status' => 'success',
            'data' => $extracted,
        ]);
    }

    /**
     * Form & Pembuatan Project Baru (P01) oleh Tim Dokumen
     * Mendukung Bundle Upload (100% Tanpa AI maupun AI opsional) + Auto-Fill Form BC 2.0/3.0 / Post CEISA.
     * Hak Akses: document.create
     */
    public function store(Request $request)
    {
        Gate::authorize('document.create');

        $hasFiles = $request->hasFile('bundle_files')
            || $request->hasFile('bl_file')
            || $request->hasFile('invoice_file')
            || $request->hasFile('packing_list_file')
            || $request->hasFile('coo_file')
            || $request->hasFile('sc_po_file')
            || $request->hasFile('insurance_file')
            || $request->hasFile('booking_file')
            || $request->hasFile('lartas_file')
            || $request->hasFile('etc_file')
            || $request->hasFile('documents');

        $validated = $request->validate([
            'client_id' => $hasFiles ? 'nullable|uuid|exists:clients,id' : 'required|uuid|exists:clients,id',
            'shipping_vendor_id' => 'nullable|uuid|exists:shipping_vendors,id',
            'type' => 'nullable|in:IMPORT,EXPORT',
            'bl_number' => $hasFiles ? 'nullable|string|max:100' : 'required|string|max:100',
            'tgl_bl' => 'nullable|date',
            'etd_date' => 'nullable|date',
            'vessel_name' => 'nullable|string|max:150',
            'voyage_number' => 'nullable|string|max:50',
            'port_of_loading' => 'nullable|string|max:100',
            'port_of_discharge' => 'nullable|string|max:100',
            'eta' => 'nullable|date',
            'free_time_days' => 'nullable|integer|min:1|max:60',
            'total_kontainer' => 'nullable|integer|min:1',
            'nomor_kontainer' => 'nullable|string|max:100',
            'total_kemasan' => 'nullable|integer|min:1',
            'jenis_kemasan' => 'nullable|string|max:50',
            'total_bruto' => 'nullable|numeric|min:0',
            'total_netto' => 'nullable|numeric|min:0',
            'total_cif' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'ndbm_kurs' => 'nullable|numeric|min:1',
            'commodity_summary' => 'nullable|string|max:255',
            'catatan' => 'nullable|string|max:500',
            'use_ai' => 'nullable',
            'ai_model' => 'nullable|string|max:100',
            'after_action' => 'nullable|in:project,fill_bc_form,post_ceisa',
            'bundle_classifications' => 'nullable|array',
            'bl_file' => 'nullable|file|max:20480',
            'invoice_file' => 'nullable|file|max:20480',
            'packing_list_file' => 'nullable|file|max:20480',
            'coo_file' => 'nullable|file|max:20480',
            'sc_po_file' => 'nullable|file|max:20480',
            'insurance_file' => 'nullable|file|max:20480',
            'booking_file' => 'nullable|file|max:20480',
            'lartas_file' => 'nullable|file|max:20480',
            'etc_file' => 'nullable|file|max:20480',
            'bundle_files.*' => 'nullable|file|max:51200',
            'documents.*' => 'nullable|file|max:20480',
        ]);

        if ($hasFiles) {
            $files = [];
            foreach (['bl_file', 'invoice_file', 'packing_list_file', 'coo_file', 'sc_po_file', 'insurance_file', 'booking_file', 'lartas_file', 'etc_file', 'bundle_files', 'documents'] as $fk) {
                if ($request->hasFile($fk)) {
                    $files[$fk] = $request->file($fk);
                }
            }

            $shipment = $this->workflowService->createProjectFromUploadedDocuments($files, $validated, auth()->id());
            $modeLabel = filter_var($validated['use_ai'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'AI + Rule Engine' : 'Rule Engine Offline (Tanpa AI)';
            $msg = "Proyek {$shipment->project_no} berhasil dibuat otomatis dari bundle dokumen ({$modeLabel}).";
        } else {
            $shipment = $this->workflowService->createProject($validated, auth()->id());
            $msg = "Proyek {$shipment->project_no} berhasil dibuat. Slot checklist dokumen wajib telah diinisialisasi.";
        }

        $afterAction = $validated['after_action'] ?? 'project';
        if (in_array($afterAction, ['fill_bc_form', 'post_ceisa'], true)) {
            $hsCodeService = app(\App\Services\Ceisa\CeisaHsCodeService::class);
            $shipment->load(['documents', 'items', 'client', 'vendor']);
            if ($shipment->items->isEmpty()) {
                $this->workflowService->detectHsCodeFromPackingList($shipment);
                $shipment->refresh()->load(['documents', 'items', 'client', 'vendor']);
            }
            $ceisaDoc = $hsCodeService->generatePibOrPebFromPackingList($shipment);
            $routeName = ($ceisaDoc->jenis_dokumen === '30') ? 'bc30.show' : 'bc20.show';

            if ($afterAction === 'post_ceisa') {
                if ((bool) config('ceisa.api.read_only', true)) {
                    return redirect()->route($routeName, $ceisaDoc->id)
                        ->with('warning', "{$msg} Form BC {$ceisaDoc->jenis_dokumen} ({$ceisaDoc->nomor_aju}) sudah terisi otomatis, tetapi pengiriman ke CEISA ditahan karena Mode Read-Only (Tarik Data Saja) sedang aktif.");
                }

                if (Gate::allows('document.submit')) {
                    try {
                        $submittedDoc = $this->workflowService->submitCeisaAndAdvanceToDo($shipment);
                        return redirect()->route($routeName, $submittedDoc->id)
                            ->with('success', "{$msg} Form BC {$submittedDoc->jenis_dokumen} ({$submittedDoc->nomor_aju}) terisi otomatis & berhasil diposting ke CEISA API.");
                    } catch (\Throwable $e) {
                        return redirect()->route($routeName, $ceisaDoc->id)
                            ->with('warning', "{$msg} Form BC {$ceisaDoc->jenis_dokumen} ({$ceisaDoc->nomor_aju}) sudah terisi otomatis, namun posting CEISA gagal: " . $e->getMessage());
                    }
                }
            }

            return redirect()->route($routeName, $ceisaDoc->id)
                ->with('success', "{$msg} Form BC {$ceisaDoc->jenis_dokumen} ({$ceisaDoc->nomor_aju}) telah terisi otomatis & siap ditinjau.");
        }

        return redirect()->route('core.document.show', $shipment->id)->with('success', $msg);
    }

    /**
     * Sinkronisasi Ulang Data Shipment dari Dokumen Terunggah
     * Hak Akses: document.create
     */
    public function reSyncFromDocs(string $id)
    {
        Gate::authorize('document.create');

        $shipment = Shipment::findOrFail($id);
        $extracted = $this->workflowService->reSyncShipmentFromDocuments($shipment);

        return redirect()->route('core.document.show', $shipment->id)
            ->with('success', "Data proyek {$shipment->shipment_number} berhasil disinkronkan & diperbarui dari dokumen terlampir.");
    }

    /**
     * Detail Project Shipment: Tracking 9 Slot Dokumen, HS Code, 3-Way Matching
     * Hak Akses: document.view
     */
    public function show(string $id)
    {
        Gate::authorize('document.view');

        $shipment = Shipment::with([
            'client',
            'shippingVendor',
            'documents.verifier',
            'items.customsDocuments.verifier',
            'itemCustomsDocuments.verifier',
            'itemCustomsDocuments.item',
            'ceisaDocument.logs',
            'inspectionPhotos.uploader',
            'operationCosts',
            'invoice'
        ])->find($id);

        if (!$shipment) {
            $ceisaDoc = \App\Models\CeisaDocument::find($id);
            if ($ceisaDoc) {
                $shipment = $ceisaDoc->shipment ?? $this->workflowService->ensureShipmentForCeisaDocument($ceisaDoc);
                $shipment->load([
                    'client',
                    'shippingVendor',
                    'documents.verifier',
                    'items.customsDocuments.verifier',
                    'itemCustomsDocuments.verifier',
                    'itemCustomsDocuments.item',
                    'ceisaDocument.logs',
                    'inspectionPhotos.uploader',
                    'operationCosts',
                    'invoice'
                ]);
            }
        }

        if (!$shipment) {
            abort(404, 'Proyek shipment kepabeanan tidak ditemukan.');
        }

        $completionRate = $shipment->document_completion_rate;
        $threeWay = $shipment->three_way_matching;
        $taxes = $shipment->estimated_taxes;

        return view('core.document_show', compact('shipment', 'completionRate', 'threeWay', 'taxes'));
    }

    /**
     * Upload Dokumen (Invoice, Packing List, BL, COO, dll)
     * Hak Akses: document.create
     */
    public function uploadDocument(string $id, Request $request)
    {
        Gate::authorize('document.create');

        $request->validate([
            'document_type' => 'required|string',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png,xlsx,xls|max:10240',
            'catatan' => 'nullable|string|max:255',
        ]);

        $shipment = Shipment::findOrFail($id);
        $file = $request->file('document_file');

        $this->workflowService->uploadDocument($shipment, $request->document_type, $file, $request->catatan);

        // Jika yang diupload adalah PACKING_LIST, jalankan otomatis deteksi HS Code AI / BTKI
        if ($request->document_type === 'PACKING_LIST') {
            $this->workflowService->detectHsCodeFromPackingList($shipment);
            return back()->with('success', 'Packing List berhasil diunggah dan item HS Code (BTKI) terdeteksi otomatis.');
        }

        return back()->with('success', "Dokumen [{$request->document_type}] berhasil diunggah.");
    }

    /**
     * Download / Preview Berkas Dokumen yang Diunggah
     * Hak Akses: document.view
     */
    public function downloadDocument(string $id, string $docId)
    {
        Gate::authorize('document.view');

        $shipment = Shipment::findOrFail($id);
        $doc = $shipment->documents()->findOrFail($docId);

        if (!$doc->file_path || !Storage::disk('public')->exists($doc->file_path)) {
            return back()->with('error', 'Berkas fisik tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk('public')->download($doc->file_path, $doc->nama_file ?? 'dokumen.pdf');
    }

    /**
     * Verifikasi Kelengkapan Dokumen oleh Supervisor Dokumen
     * Hak Akses: document.verify
     */
    public function verifyDocument(string $id, string $docId, Request $request)
    {
        Gate::authorize('document.verify');

        $status = $request->input('status', 'VERIFIED');
        $notes = $request->input('notes');

        $this->workflowService->verifyDocument($docId, auth()->id(), $status, $notes);

        return back()->with('success', "Status dokumen berhasil diperbarui menjadi {$status}.");
    }

    /**
     * Verifikasi Massal Seluruh Dokumen Terunggah (Bulk Verify Supervisor)
     * Hak Akses: document.verify
     */
    public function verifyAllDocuments(string $id)
    {
        Gate::authorize('document.verify');

        $shipment = Shipment::findOrFail($id);
        $count = $this->workflowService->verifyAllDocuments($shipment, auth()->id());

        return back()->with('success', "{$count} berkas berhasil disetujui (VERIFIED) oleh Supervisor. Status proyek: DOC_READY.");
    }

    /**
     * Jalankan Ulang Deteksi Cerdas Pos Tarif HS Code & BTKI (Mendukung Upload File / OCR Text / AI)
     * Hak Akses: document.create
     */
    public function detectHsCode(string $id, Request $request)
    {
        Gate::authorize('document.create');

        $request->validate([
            'packing_list_file' => 'nullable|file|max:20480',
            'raw_text' => 'nullable|string|max:100000',
        ]);

        $shipment = Shipment::findOrFail($id);
        $file = $request->file('packing_list_file');
        $rawText = $request->input('raw_text');

        // Jika ada file packing list baru diupload, simpan juga ke slot dokumen PACKING_LIST
        if ($file) {
            $this->workflowService->uploadDocument($shipment, 'PACKING_LIST', $file, "Diunggah saat pemindaian ulang AI / OCR Packing List.");
        }

        $items = $this->workflowService->detectHsCodeFromPackingList($shipment, $rawText, $file);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => count($items) . ' pos tarif barang berhasil diekstraksi dan dicocokkan dengan database BTKI.',
                'items_count' => count($items),
                'items' => $items,
            ]);
        }

        return back()->with('success', count($items) . ' pos tarif barang berhasil diekstraksi dan dicocokkan dengan database BTKI.');
    }

    /**
     * Tambah Item Kargo Manual
     * Hak Akses: document.create
     */
    public function addItem(string $id, Request $request)
    {
        Gate::authorize('document.create');

        $validated = $request->validate([
            'kode_hs' => 'required|string|max:20',
            'uraian_barang' => 'required|string|max:255',
            'hs_legal_basis' => 'nullable|string|max:255',
            'negara_asal' => 'nullable|string|size:2',
            'jumlah_kemasan' => 'required|integer|min:1',
            'jenis_kemasan' => 'nullable|string|max:20',
            'berat_bruto' => 'required|numeric|min:0',
            'berat_netto' => 'required|numeric|min:0',
            'nilai_fob' => 'required|numeric|min:0',
            'tarif_bm_persen' => 'nullable|numeric|min:0|max:100',
            'tarif_ppn_persen' => 'nullable|numeric|min:0|max:100',
            'tarif_pph_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        $shipment = Shipment::findOrFail($id);
        $this->workflowService->addCargoItem($shipment, $validated);

        return back()->with('success', 'Item kargo berhasil ditambahkan ke daftar manifes pabean.');
    }

    /**
     * Edit Item Kargo
     * Hak Akses: document.create
     */
    public function updateItem(string $id, string $itemId, Request $request)
    {
        Gate::authorize('document.create');

        $validated = $request->validate([
            'kode_hs' => 'required|string|max:20',
            'uraian_barang' => 'required|string|max:255',
            'hs_legal_basis' => 'nullable|string|max:255',
            'negara_asal' => 'nullable|string|size:2',
            'jumlah_kemasan' => 'required|integer|min:1',
            'jenis_kemasan' => 'nullable|string|max:20',
            'berat_bruto' => 'required|numeric|min:0',
            'berat_netto' => 'required|numeric|min:0',
            'nilai_fob' => 'required|numeric|min:0',
            'tarif_bm_persen' => 'nullable|numeric|min:0|max:100',
            'tarif_ppn_persen' => 'nullable|numeric|min:0|max:100',
            'tarif_pph_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        $shipment = Shipment::findOrFail($id);
        $item = $shipment->items()->findOrFail($itemId);
        $this->workflowService->updateCargoItem($item, $validated);

        return back()->with('success', 'Perubahan rincian item kargo berhasil disimpan.');
    }

    /**
     * Hapus Item Kargo (Soft Delete)
     * Hak Akses: document.create
     */
    public function deleteItem(string $id, string $itemId)
    {
        Gate::authorize('document.create');

        $shipment = Shipment::findOrFail($id);
        $item = $shipment->items()->findOrFail($itemId);
        $this->workflowService->deleteCargoItem($item);

        return back()->with('success', 'Item kargo berhasil dihapus.');
    }

    /**
     * Verifikasi Packing List (3-Way Matching & Estimasi Pungutan Pabean)
     * Hak Akses: document.view
     */
    public function verifyPackingList(string $id, \App\Services\Ceisa\CeisaHsCodeService $hsCodeService)
    {
        Gate::authorize('document.view');

        $shipment = Shipment::with('items')->findOrFail($id);
        $result = $hsCodeService->verifyPackingList($shipment);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return back()->with('verification_result', $result);
    }

    /**
     * Generate Draft Dokumen Pabean (PIB BC 2.0 / PEB BC 3.0) Otomatis dari Packing List
     * Hak Akses: document.create
     */
    public function generatePibPeb(string $id, \App\Services\Ceisa\CeisaHsCodeService $hsCodeService)
    {
        Gate::authorize('document.create');

        $shipment = Shipment::with(['documents', 'items', 'client', 'vendor'])->findOrFail($id);

        if ($shipment->items->isEmpty()) {
            $this->workflowService->detectHsCodeFromPackingList($shipment);
            $shipment->refresh();
        }

        $doc = $hsCodeService->generatePibOrPebFromPackingList($shipment);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Draft dokumen BC {$doc->jenis_dokumen} ({$doc->nomor_aju}) berhasil digenerate dari Packing List.",
                'document' => $doc,
            ]);
        }

        $routeName = ($doc->jenis_dokumen === '20') ? 'bc20.show' : 'bc30.show';
        return redirect()->route($routeName, $doc->id)
            ->with('success', "Draft dokumen BC {$doc->jenis_dokumen} ({$doc->nomor_aju}) berhasil digenerate dari Packing List.");
    }

    /**
     * Generate Dokumen BC 2.0 / BC 3.0 & Submit ke CEISA hingga terbit Billing
     * Hak Akses: document.submit
     */
    public function generateAndSubmitCeisa(string $id)
    {
        Gate::authorize('document.submit');

        if ((bool) config('ceisa.api.read_only', true)) {
            return back()->with('error', 'Mode Tarik Data Saja (Read-Only) aktif: upload/pengiriman dokumen ke CEISA dinonaktifkan. Gunakan fitur Tarik Data CEISA.');
        }

        $shipment = Shipment::with(['documents', 'items', 'client', 'vendor'])->findOrFail($id);

        if (!$shipment->is_all_mandatory_documents_verified) {
            return back()->with('error', 'Dokumen wajib (Invoice, Packing List, B/L) belum 100% diverifikasi. Lengkapi dan setujui berkas terlebih dahulu.');
        }

        try {
            // 1. Pastikan Dokumen Pabean telah dibuat dari Shipment Items / Verified Packing List
            if (!$shipment->ceisaDocument || empty($shipment->ceisaDocument->raw_payload)) {
                $this->workflowService->generateCeisaDocFromShipment($shipment);
                $shipment->refresh();
            }

            // 2. Submit ke CEISA 4.0 & Dapatkan Kode Billing MPN G3
            $ceisaDoc = $this->workflowService->submitCeisaAndAdvanceToDo($shipment);
            $billingCode = $ceisaDoc->kode_billing ?? '028192847192841';

            return back()->with('success', "Dokumen Pabean {$ceisaDoc->nomor_aju} berhasil disubmit ke CEISA 4.0! Kode Billing MPN G3: {$billingCode}. Tahap Tim Dokumen selesai & otomatis dialihkan ke antrian Tim DO.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('CEISA Submission Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Gagal memproses pengajuan CEISA 4.0: ' . $e->getMessage());
        }
    }

    /**
     * Update / Lengkapi Berkas Dokumen Tambahan & Izin Lartas per Barang
     * Hak Akses: document.create
     */
    public function updateItemDocument(Request $request, string $id, string $docId)
    {
        Gate::authorize('document.create');

        $validated = $request->validate([
            'nomor_dokumen' => 'nullable|string|max:100',
            'tanggal_dokumen' => 'nullable|date',
            'catatan' => 'nullable|string|max:255',
            'status' => 'nullable|in:MISSING,UPLOADED,VERIFIED,REJECTED',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,xlsx,xls|max:10240',
        ]);

        $shipment = Shipment::findOrFail($id);
        $doc = \App\Models\ShipmentItemDocument::where('shipment_id', $shipment->id)->findOrFail($docId);

        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $fileName = $file->getClientOriginalName();
            $path = $file->store('shipment_item_docs', 'public');
            $doc->file_name = $fileName;
            $doc->file_path = $path;
            if (empty($validated['status'])) {
                $doc->status = 'UPLOADED';
            }
        }

        if (array_key_exists('nomor_dokumen', $validated)) {
            $doc->nomor_dokumen = $validated['nomor_dokumen'];
        }
        if (array_key_exists('tanggal_dokumen', $validated)) {
            $doc->tanggal_dokumen = $validated['tanggal_dokumen'];
        }
        if (array_key_exists('catatan', $validated)) {
            $doc->catatan = $validated['catatan'];
        }

        if (!empty($validated['status'])) {
            $doc->status = $validated['status'];
            if ($validated['status'] === 'VERIFIED') {
                $doc->verified_by_user_id = auth()->id();
                $doc->verified_at = now();
            }
        } elseif (!empty($doc->nomor_dokumen) && $doc->status === 'MISSING') {
            $doc->status = 'UPLOADED';
        }

        $doc->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Dokumen lampiran {$doc->nama_dokumen} ({$doc->kode_dokumen}) berhasil diperbarui.",
                'data' => $doc,
            ]);
        }

        return back()->with('success', "Dokumen lampiran {$doc->nama_dokumen} ({$doc->kode_dokumen}) berhasil diperbarui.");
    }

    /**
     * Verifikasi Dokumen Tambahan / Izin Lartas oleh Supervisor Dokumen
     * Hak Akses: document.verify
     */
    public function verifyItemDocument(Request $request, string $id, string $docId)
    {
        Gate::authorize('document.verify');

        $shipment = Shipment::findOrFail($id);
        $doc = \App\Models\ShipmentItemDocument::where('shipment_id', $shipment->id)->findOrFail($docId);

        $status = $request->input('status', 'VERIFIED');
        $doc->status = $status;
        $doc->verified_by_user_id = auth()->id();
        $doc->verified_at = now();
        if ($request->filled('catatan')) {
            $doc->catatan = $request->catatan;
        }
        $doc->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Status dokumen tambahan {$doc->nama_dokumen} berhasil diubah menjadi {$status}.",
                'data' => $doc,
            ]);
        }

        return back()->with('success', "Status dokumen tambahan {$doc->nama_dokumen} berhasil diubah menjadi {$status}.");
    }

    /**
     * Tambah Dokumen Khusus / Izin Lartas Manual untuk Item Kargo
     * Hak Akses: document.create
     */
    public function addItemCustomsDocument(Request $request, string $id)
    {
        Gate::authorize('document.create');

        $validated = $request->validate([
            'shipment_item_id' => 'required|uuid|exists:shipment_items,id',
            'kode_dokumen' => 'required|string|max:10',
            'nama_dokumen' => 'required|string|max:150',
            'instansi_penerbit' => 'nullable|string|max:150',
            'nomor_dokumen' => 'nullable|string|max:100',
            'tanggal_dokumen' => 'nullable|date',
            'is_mandatory' => 'nullable|boolean',
            'catatan' => 'nullable|string|max:255',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,xlsx,xls|max:10240',
        ]);

        $shipment = Shipment::findOrFail($id);
        
        $filePath = null;
        $fileName = null;
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('shipment_item_docs', 'public');
        }

        $doc = \App\Models\ShipmentItemDocument::create([
            'shipment_id' => $shipment->id,
            'shipment_item_id' => $validated['shipment_item_id'],
            'kode_dokumen' => $validated['kode_dokumen'],
            'nama_dokumen' => $validated['nama_dokumen'],
            'instansi_penerbit' => $validated['instansi_penerbit'] ?: 'Kementerian / Lembaga Terkait',
            'nomor_dokumen' => $validated['nomor_dokumen'] ?? null,
            'tanggal_dokumen' => $validated['tanggal_dokumen'] ?? null,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status' => $filePath ? 'UPLOADED' : (!empty($validated['nomor_dokumen']) ? 'UPLOADED' : 'MISSING'),
            'is_mandatory' => $request->boolean('is_mandatory', true),
            'catatan' => $validated['catatan'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Dokumen khusus [{$doc->kode_dokumen}] {$doc->nama_dokumen} berhasil ditambahkan.",
                'data' => $doc,
            ]);
        }

        return back()->with('success', "Dokumen khusus [{$doc->kode_dokumen}] {$doc->nama_dokumen} berhasil ditambahkan ke manifes kargo.");
    }

    /**
     * Hapus Dokumen Khusus / Lartas Item
     * Hak Akses: document.create
     */
    public function deleteItemDocument(string $id, string $docId)
    {
        Gate::authorize('document.create');

        $shipment = Shipment::findOrFail($id);
        $doc = \App\Models\ShipmentItemDocument::where('shipment_id', $shipment->id)->findOrFail($docId);
        $doc->delete();

        return back()->with('success', "Dokumen [{$doc->kode_dokumen}] {$doc->nama_dokumen} berhasil dihapus.");
    }

    /**
     * Membuka / Mengembalikan Alur Shipment ke Pos atau Tahap Sebelumnya
     * Hak Akses: workflow.rollback_stage (atau SuperAdmin/Owner/Supervisor)
     */
    public function rollbackStage(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasPermission('workflow.rollback_stage')) {
            abort(403, 'Akses Ditolak: Anda memerlukan hak akses izin "workflow.rollback_stage" untuk membuka atau mengembalikan alur proses shipment ke pos sebelumnya.');
        }

        $validated = $request->validate([
            'target_stage' => 'required|string',
            'alasan' => 'nullable|string|max:500',
            'reopen_ceisa_draft' => 'nullable|boolean',
        ]);

        $shipment = Shipment::findOrFail($id);
        $oldTahap = $shipment->status_tahap;
        $targetStage = $validated['target_stage'];

        // Definisi tahap yang didukung untuk pembukaan kembali
        $allowedStages = [
            'DOC_COLLECTING' => 'Pos 1: Pengumpulan & Verifikasi Berkas',
            'DOC_READY' => 'Pos 1: Berkas Lengkap (Doc Ready)',
            'HS_DETECTED' => 'Pos 2: Deteksi HS Code & Lartas',
            'DRAFT_REVIEW' => 'Pos 3: Review Draft Dokumen Pabean (PIB/PEB)',
            'CEISA_SUBMITTED' => 'Pos 3: Diajukan ke Gateway CEISA 4.0',
            'BILLING_ISSUED' => 'Pos 4: Billing Kepabeanan Terbit',
            'BILLING_PAID' => 'Pos 5: Pembayaran Billing Lunas',
            'DO_PROCESSING' => 'Pos 5: Proses Rilis DO Pelayaran',
            'DO_RELEASED' => 'Pos 5: Delivery Order (DO) Dirilis',
            'ARRIVED' => 'Pos 6: Kedatangan Kapal & Penentuan Jalur',
            'TRAFFIC_HOLD' => 'Pos 7: Hold / Pemeriksaan Fisik Bea Cukai',
            'SPPB_RELEASED' => 'Pos 8: SPPB / NPE Terbit',
            'GATE_OUT' => 'Pos 9: Pengeluaran Peti Kemas (Gate Out)',
            'DELIVERY' => 'Pos 10: Pengiriman Truk ke Gudang Tujuan',
        ];

        if (!array_key_exists($targetStage, $allowedStages)) {
            return back()->with('error', 'Tahap alur yang dipilih tidak valid.');
        }

        // Kunci alur jika sudah terbit billing: Tidak boleh rollback ke tahap sebelum billing (Pos 1, Pos 2, Pos 3)
        if ($shipment->isBillingLocked() && in_array($targetStage, ['DOC_COLLECTING', 'DOC_READY', 'HS_DETECTED', 'DRAFT_REVIEW', 'CEISA_SUBMITTED'])) {
            $billingCode = $shipment->billing_code ?? $shipment->ceisaDocument?->kode_billing ?? 'MPN G3';
            return back()->with('error', "Pembukaan alur ke tahap sebelum billing ditolak: Proyek telah menerbitkan Billing Kepabeanan (Kode Billing: {$billingCode}). Dokumen pabean terkunci dan tidak dapat diedit atau dikembalikan ke DRAFT. Namun Anda tetap dapat melihat seluruh data proses sebelumnya.");
        }

        $catatanTambahan = "[Buka Proses Sebelumnya: {$allowedStages[$targetStage]}] " 
            . ($validated['alasan'] ? $validated['alasan'] . " " : "")
            . "(Oleh: {$user->name} pada " . now()->format('d/m/Y H:i') . ")\n";

        // Update tahap shipment
        $shipment->update([
            'status_tahap' => $targetStage,
            'catatan' => $catatanTambahan . ($shipment->catatan ?? ''),
        ]);

        // Jika opsi reopen_ceisa_draft diaktifkan atau target adalah tahap pembuatan draft / review
        if ($shipment->ceisaDocument && !$shipment->ceisaDocument->isBillingLocked() && ($request->boolean('reopen_ceisa_draft') || in_array($targetStage, ['DOC_COLLECTING', 'DOC_READY', 'HS_DETECTED', 'DRAFT_REVIEW']))) {
            if ($user->isSuperAdmin() || $user->hasPermission('document.edit_after_ceisa')) {
                $shipment->ceisaDocument->update([
                    'status' => 'DRAFT',
                    'status_description' => "Dokumen pabean dikembalikan ke status DRAFT seiring pembukaan kembali proses ke {$allowedStages[$targetStage]} oleh {$user->name}.",
                    'is_schema_valid' => false,
                    'schema_validation_message' => 'Status dikembalikan ke DRAFT untuk penyesuaian data.',
                ]);
            }
        }

        return back()->with('success', "Alur shipment {$shipment->project_no} berhasil dikembalikan ke {$allowedStages[$targetStage]}. Anda kini dapat melakukan pengeditan atau verifikasi ulang data pada tahap ini.");
    }
}
