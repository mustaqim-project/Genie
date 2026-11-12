<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ImportBatch;
use App\Models\ImportDocument;
use App\Models\ImportField;
use App\Models\ImportFile;
use App\Models\Shipment;
use App\Models\StagePrerequisite;
use App\Services\Shipment\BatchImportService;
use App\Services\Shipment\StageJumpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BatchImportController extends Controller
{
    protected BatchImportService $importService;
    protected StageJumpService $jumpService;

    public function __construct(BatchImportService $importService, StageJumpService $jumpService)
    {
        $this->importService = $importService;
        $this->jumpService = $jumpService;
    }

    /**
     * Dashboard Batch Import & Antrean Dokumen
     * Hak Akses: document.view
     */
    public function index(Request $request)
    {
        Gate::authorize('document.view');

        $batches = ImportBatch::with(['uploader', 'client'])
            ->withCount(['files', 'documents'])
            ->latest()
            ->paginate(10);

        $clients = Client::orderBy('name')->get();
        $prerequisites = StagePrerequisite::orderBy('stage_number')->get();

        $stats = [
            'total_batches' => ImportBatch::count(),
            'total_files_processed' => ImportFile::count(),
            'pending_review_docs' => ImportDocument::where('review_status', 'PENDING')->count(),
            'applied_docs' => ImportDocument::where('review_status', 'APPROVED')->count(),
        ];

        return view('core.import.batch_index', compact('batches', 'clients', 'prerequisites', 'stats'));
    }

    /**
     * Unggah Berkas Massal (Multiple Files / Folder / ZIP)
     * Hak Akses: document.create
     */
    public function store(Request $request)
    {
        Gate::authorize('document.create');

        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'file|max:51200', // max 50 MB per file
            'client_id' => 'nullable|uuid|exists:clients,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $batch = $this->importService->createBatch(
            $request->file('files'),
            $request->client_id,
            $request->notes
        );

        return redirect()->route('core.import.show', $batch->id)
            ->with('success', "Batch {$batch->batch_no} berhasil diproses ({$batch->success_count} berhasil, {$batch->error_count} gagal).");
    }

    /**
     * Detail Berkas & Dokumen dalam Satu Batch
     * Hak Akses: document.view
     */
    public function show(string $id)
    {
        Gate::authorize('document.view');

        $batch = ImportBatch::with(['files.documents.fields', 'client', 'uploader'])->findOrFail($id);
        $documents = ImportDocument::with(['file', 'fields', 'validations', 'shipment'])
            ->where('batch_id', $batch->id)
            ->get();

        return view('core.import.batch_show', compact('batch', 'documents'));
    }

    /**
     * Layar Review Split-Screen: Form Hasil Ekstraksi (Kiri) & PDF Sumber (Kanan)
     * Hak Akses: document.view
     */
    public function review(string $docId)
    {
        Gate::authorize('document.view');

        $document = ImportDocument::with(['file', 'fields', 'items', 'validations', 'batch'])->findOrFail($docId);
        $prerequisites = StagePrerequisite::orderBy('stage_number')->get();

        return view('core.import.split_review', compact('document', 'prerequisites'));
    }

    /**
     * Simpan Koreksi Nilai Field via AJAX
     * Hak Akses: document.create
     */
    public function updateField(string $fieldId, Request $request)
    {
        Gate::authorize('document.create');

        $validated = $request->validate([
            'corrected_value' => 'required|string',
        ]);

        $field = ImportField::findOrFail($fieldId);
        $field->update([
            'corrected_value' => $validated['corrected_value'],
            'status' => 'EDITED',
            'edited_by_id' => auth()->id(),
            'edited_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Nilai field berhasil diperbarui.',
            'field' => $field,
        ]);
    }

    /**
     * Terapkan Dokumen ke Shipment Nyata
     * Hak Akses: document.create
     */
    public function applyToShipment(string $docId, Request $request)
    {
        Gate::authorize('document.create');

        $doc = ImportDocument::findOrFail($docId);
        $shipment = $this->importService->applyDocumentToShipment($doc, $request->input('shipment_id'));

        return redirect()->route('core.document.show', $shipment->id)
            ->with('success', "Data hasil impor dokumen {$doc->document_type} berhasil diterapkan ke proyek {$shipment->project_no}.");
    }

    /**
     * Eksekusi Lompat Tahap Proyek (Entry Stage)
     * Hak Akses: document.create
     */
    public function jumpStage(string $shipmentId, Request $request)
    {
        Gate::authorize('document.create');

        $validated = $request->validate([
            'target_stage' => 'required|string',
            'reason' => 'required|string|max:500',
            'financial_treatment' => 'required|in:OPENING_BALANCE,HISTORICAL_POSTING',
        ]);

        $shipment = Shipment::findOrFail($shipmentId);

        try {
            $jumpReq = $this->jumpService->requestJump(
                $shipment,
                $validated['target_stage'],
                $validated['reason'],
                $validated['financial_treatment']
            );

            $msg = $jumpReq->status === 'APPROVED'
                ? "Proyek {$shipment->project_no} berhasil melompat dan langsung aktif pada tahap {$validated['target_stage']}."
                : "Permintaan lompat tahap ke {$validated['target_stage']} diajukan dan menunggu persetujuan Supervisor/Pimpinan.";

            return redirect()->route('core.document.show', $shipment->id)->with('success', $msg);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
