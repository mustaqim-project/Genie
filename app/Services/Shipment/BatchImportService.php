<?php

namespace App\Services\Shipment;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\ImportApplication;
use App\Models\ImportBatch;
use App\Models\ImportDocument;
use App\Models\ImportField;
use App\Models\ImportFile;
use App\Models\ImportItem;
use App\Models\ImportValidation;
use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Models\ShipmentItem;
use App\Models\ShippingVendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BatchImportService
{
    protected DocumentExtractionService $extractionService;

    public function __construct(DocumentExtractionService $extractionService)
    {
        $this->extractionService = $extractionService;
    }

    /**
     * Inisialisasi Batch Import Baru dari Unggahan File / Folder / ZIP
     */
    public function createBatch(array $uploadedFiles, ?string $clientId = null, ?string $notes = null, ?string $type = null): ImportBatch
    {
        $normalizedType = strtoupper(trim((string) $type));
        if (!in_array($normalizedType, ['IMPORT', 'EXPORT'], true)) {
            throw new \InvalidArgumentException('Tipe kepabeanan (IMPORT atau EXPORT) wajib dipilih terlebih dahulu.');
        }

        $batchNo = 'BATCH-' . date('Ymd-His') . '-' . strtoupper(Str::random(4));

        $batch = ImportBatch::create([
            'batch_no' => $batchNo,
            'uploaded_by_id' => auth()->id(),
            'client_id' => $clientId,
            'type' => $normalizedType,
            'status' => 'PROCESSING',
            'total_files' => count($uploadedFiles),
            'processed_files' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'notes' => $notes,
        ]);

        foreach ($uploadedFiles as $file) {
            $this->processUploadedFile($batch, $file);
        }

        // Semua hasil bulk upload masuk sebagai DRAFT untuk direview & diedit sebelum diterapkan / kirim CEISA
        $batch->update([
            'status' => $batch->error_count > 0 && $batch->success_count === 0 ? 'FAILED' : 'DRAFT',
        ]);

        AuditLog::record('BATCH_IMPORT_CREATED', 'ImportBatch', $batch->id, null, [
            'batch_no' => $batch->batch_no,
            'total_files' => $batch->total_files,
            'success' => $batch->success_count,
            'errors' => $batch->error_count,
        ]);

        return $batch;
    }

    /**
     * Memproses satu berkas dalam batch (Pemeriksaan keamanan, SHA-256, klasifikasi, ekstraksi)
     */
    public function processUploadedFile(ImportBatch $batch, UploadedFile $file): ImportFile
    {
        $origName = $file->getClientOriginalName();
        $fileSizeKb = round($file->getSize() / 1024);
        $fileHash = hash_file('sha256', $file->getRealPath());
        $mime = $file->getMimeType();

        // 1. Deteksi Duplikat (SHA-256)
        $isDuplicate = ImportFile::where('file_hash', $fileHash)->exists();

        $storagePath = $file->storeAs('import_batches/' . $batch->id, time() . '_' . $origName, 'public');

        $importFile = ImportFile::create([
            'batch_id' => $batch->id,
            'original_name' => $origName,
            'file_path' => $storagePath,
            'file_size_kb' => $fileSizeKb,
            'file_hash' => $fileHash,
            'mime_type' => $mime,
            'status' => $isDuplicate ? 'DUPLICATE' : 'UPLOADED',
        ]);

        if ($isDuplicate) {
            $batch->increment('processed_files');
            $importFile->update(['error_message' => 'Berkas identik (hash duplikat) sudah pernah diunggah sebelumnya.']);
            return $importFile;
        }

        try {
            // 2. Ekstraksi teks & Deteksi Tipe Dokumen
            $text = $this->extractionService->extractTextFromFile($file);
            $detectedType = $this->classifyDocumentType($origName, $text);

            $importFile->update([
                'detected_type' => $detectedType['type'],
                'type_confidence' => $detectedType['confidence'],
                'status' => 'EXTRACTED',
            ]);

            // 3. Buat logical document
            $doc = ImportDocument::create([
                'file_id' => $importFile->id,
                'batch_id' => $batch->id,
                'document_type' => $detectedType['type'],
                'page_range' => '1',
                'language' => 'id',
                'review_status' => 'PENDING',
            ]);

            // 4. Ekstrak Field Kunci
            $this->extractDocumentFields($doc, $text, $detectedType['type']);

            // 5. Validasi silang awal
            $this->validateDocumentRules($doc);

            $importFile->update(['status' => 'READY']);
            $batch->increment('processed_files');
            $batch->increment('success_count');
        } catch (\Throwable $e) {
            $importFile->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage(),
            ]);
            $batch->increment('processed_files');
            $batch->increment('error_count');
        }

        return $importFile;
    }

    /**
     * Klasifikasi Cerdas Jenis Dokumen Ekspor-Impor
     */
    protected function classifyDocumentType(string $filename, string $text): array
    {
        $fn = strtolower($filename);
        $t = strtoupper($text);

        if (str_contains($fn, 'invoice') || str_contains($t, 'COMMERCIAL INVOICE')) {
            return ['type' => 'INVOICE', 'confidence' => 96.5];
        }
        if (str_contains($fn, 'packing') || str_contains($t, 'PACKING LIST')) {
            return ['type' => 'PACKING_LIST', 'confidence' => 95.0];
        }
        if (str_contains($fn, 'bl') || str_contains($fn, 'bill') || str_contains($t, 'BILL OF LADING')) {
            return ['type' => 'BL_AWB', 'confidence' => 98.0];
        }
        if (str_contains($fn, 'coo') || str_contains($t, 'CERTIFICATE OF ORIGIN') || str_contains($t, 'FORM E')) {
            return ['type' => 'COO_FORME', 'confidence' => 92.0];
        }
        if (str_contains($fn, 'pib') || str_contains($t, 'PEMBERITAHUAN IMPOR BARANG')) {
            return ['type' => 'PIB', 'confidence' => 99.0];
        }
        if (str_contains($fn, 'sppb') || str_contains($t, 'SURAT PERSETUJUAN PENGELUARAN')) {
            return ['type' => 'SPPB', 'confidence' => 99.0];
        }
        if (str_contains($fn, 'do') || str_contains($t, 'DELIVERY ORDER')) {
            return ['type' => 'DO_ORIGINAL', 'confidence' => 94.0];
        }

        return ['type' => 'OTHER', 'confidence' => 60.0];
    }

    /**
     * Ekstraksi Nilai Field Kunci dan Posisi Bounding Box
     */
    protected function extractDocumentFields(ImportDocument $doc, string $text, string $type): void
    {
        $fields = [];

        // Deteksi Nomor B/L
        if (preg_match('/(?:B\/L|BL|WAYBILL)\s*(?:NO|NUMBER)?\s*[:\.]?\s*([A-Z0-9\-\/]{6,30})/i', $text, $m)) {
            $fields[] = [
                'field_key' => 'nomor_bl_awb',
                'field_label' => 'Nomor Bill of Lading (B/L)',
                'original_value' => trim($m[1]),
                'confidence_score' => 98.0,
                'bounding_box' => ['x' => 120, 'y' => 140, 'width' => 200, 'height' => 24],
            ];
        }

        // Deteksi Nomor Invoice
        if (preg_match('/(?:INVOICE)\s*(?:NO|NUMBER)?\s*[:\.]?\s*([A-Z0-9\-\/]{4,30})/i', $text, $m)) {
            $fields[] = [
                'field_key' => 'nomor_invoice',
                'field_label' => 'Nomor Commercial Invoice',
                'original_value' => trim($m[1]),
                'confidence_score' => 95.0,
                'bounding_box' => ['x' => 380, 'y' => 140, 'width' => 180, 'height' => 24],
            ];
        }

        // Deteksi Valuta & Nilai Total
        if (preg_match('/(?:TOTAL\s*(?:AMOUNT)?|AMOUNT|CIF|FOB)\s*[:\.]?\s*(USD|CNY|EUR|SGD|IDR|JPY)?\s*([0-9\.\,]+)/i', $text, $m)) {
            $currency = !empty($m[1]) ? strtoupper($m[1]) : 'USD';
            $val = (float) str_replace(',', '', $m[2]);

            $fields[] = [
                'field_key' => 'kode_valuta',
                'field_label' => 'Mata Uang (Valuta)',
                'original_value' => $currency,
                'confidence_score' => 92.0,
                'bounding_box' => ['x' => 450, 'y' => 680, 'width' => 60, 'height' => 20],
            ];
            $fields[] = [
                'field_key' => 'total_nilai_valuta',
                'field_label' => 'Total Nilai Komersial (CIF/FOB)',
                'original_value' => (string) $val,
                'confidence_score' => 94.0,
                'bounding_box' => ['x' => 520, 'y' => 680, 'width' => 140, 'height' => 20],
            ];
        }

        // Deteksi Kontainer
        if (preg_match('/([A-Z]{4}\s*\d{7})/i', $text, $m)) {
            $fields[] = [
                'field_key' => 'nomor_kontainer',
                'field_label' => 'Nomor Peti Kemas (Container)',
                'original_value' => preg_replace('/\s+/', '', strtoupper($m[1])),
                'confidence_score' => 96.0,
                'bounding_box' => ['x' => 120, 'y' => 320, 'width' => 160, 'height' => 22],
            ];
        }

        foreach ($fields as $f) {
            ImportField::create(array_merge($f, [
                'document_id' => $doc->id,
                'page_number' => 1,
                'status' => 'DETECTED',
            ]));
        }
    }

    /**
     * Evaluasi Aturan Validasi Silang (V01 - V10)
     */
    protected function validateDocumentRules(ImportDocument $doc): void
    {
        $validator = app(CrossDocumentValidationService::class);
        $validator->validateDocument($doc);
    }

    /**
     * Terapkan Data Dokumen Impor ke Shipment Nyata (Apply to Project)
     */
    public function applyDocumentToShipment(ImportDocument $document, ?string $targetShipmentId = null): Shipment
    {
        return DB::transaction(function () use ($document, $targetShipmentId) {
            $fields = $document->fields->keyBy('field_key');

            $blNumber = $fields->get('nomor_bl_awb')?->effective_value ?? 'BL-' . strtoupper(Str::random(8));

            $shipment = $targetShipmentId
                ? Shipment::findOrFail($targetShipmentId)
                : Shipment::where('nomor_bl_awb', $blNumber)->first();

            if (!$shipment) {
                $resolvedType = strtoupper(trim((string) ($document->batch?->type ?? '')));
                if (!in_array($resolvedType, ['IMPORT', 'EXPORT'], true)) {
                    throw new \InvalidArgumentException('Tipe kepabeanan batch (IMPORT atau EXPORT) belum dipilih.');
                }

                // Buat shipment baru dalam status draft pengumpulan dokumen
                $projectNo = Shipment::generateProjectNo();

                $shipment = Shipment::create([
                    'project_no' => $projectNo,
                    'client_id' => $document->batch->client_id ?? Client::first()?->id,
                    'jenis_layanan' => $resolvedType === 'EXPORT' ? 'EKSPORT_BC30' : 'IMPORT_BC20',
                    'nomor_bl_awb' => $blNumber,
                    'tgl_bl_awb' => now()->toDateString(),
                    'shipping_vendor_id' => ShippingVendor::first()?->id,
                    'nomor_kontainer' => $fields->get('nomor_kontainer')?->effective_value,
                    'kode_valuta' => $fields->get('kode_valuta')?->effective_value ?? 'USD',
                    'total_nilai_valuta' => (float) ($fields->get('total_nilai_valuta')?->effective_value ?? 0),
                    'total_cif' => (float) ($fields->get('total_nilai_valuta')?->effective_value ?? 0),
                    'status_tahap' => 'DOC_COLLECTING',
                ]);
            }

            // Tautkan berkas sebagai ShipmentDocument versi terkini
            ShipmentDocument::create([
                'shipment_id' => $shipment->id,
                'jenis_dokumen' => $document->document_type,
                'nama_file' => $document->file->original_name,
                'file_path' => $document->file->file_path,
                'file_size_kb' => $document->file->file_size_kb,
                'status' => 'VERIFIED',
                'version' => 1,
                'is_current' => true,
                'verified_by_user_id' => auth()->id(),
                'verified_at' => now(),
                'catatan' => "Diimpor secara otomatis via Batch Import {$document->batch->batch_no}",
            ]);

            // Catat record aplikasi batch import
            ImportApplication::create([
                'batch_id' => $document->batch_id,
                'shipment_id' => $shipment->id,
                'idempotency_key' => "APP-{$document->id}-{$shipment->id}",
                'applied_data_payload' => $fields->map(fn($f) => $f->effective_value)->toArray(),
                'applied_by_id' => auth()->id(),
                'can_rollback_until' => now()->addHours(24),
            ]);

            $document->update([
                'shipment_id' => $shipment->id,
                'review_status' => 'APPROVED',
                'reviewed_by_id' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Tetap DRAFT selama masih ada dokumen yang belum diterapkan
            if ($document->batch && !$document->batch->documents()->whereNull('shipment_id')->exists()) {
                $document->batch->update(['status' => 'COMPLETED']);
            }

            AuditLog::record('APPLY_IMPORT_TO_SHIPMENT', 'Shipment', $shipment->id, null, [
                'batch_no' => $document->batch->batch_no,
                'document_id' => $document->id,
                'bl_number' => $shipment->nomor_bl_awb,
            ]);

            return $shipment;
        });
    }

    /**
     * Cek apakah batch draft dapat dihapus (tidak terhubung ke shipment non-draft / terkirim CEISA).
     */
    public function isBatchDeletable(ImportBatch $batch): bool
    {
        $linkedShipmentIds = $batch->documents()
            ->whereNotNull('shipment_id')
            ->pluck('shipment_id')
            ->unique()
            ->all();

        if (empty($linkedShipmentIds)) {
            return true;
        }

        $shipments = Shipment::withoutGlobalScope('client_access')
            ->withTrashed()
            ->whereIn('id', $linkedShipmentIds)
            ->get();

        foreach ($shipments as $shipment) {
            if (!$shipment->isDraftDeletable()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Hapus batch import draft beserta seluruh aset file fisik dan record dokumen terkait.
     */
    public function deleteBatchAndAssets(ImportBatch $batch): array
    {
        if (!$this->isBatchDeletable($batch)) {
            throw new \RuntimeException('Batch tidak dapat dihapus karena terdapat dokumen yang telah diterapkan ke proyek aktif/terkirim CEISA.');
        }

        $workflowService = app(ShipmentWorkflowService::class);

        return DB::transaction(function () use ($batch, $workflowService) {
            $batch->loadMissing(['files', 'documents']);

            $fileIds = $batch->files->pluck('id')->all();
            $deletedFiles = 0;

            foreach ($batch->files as $file) {
                if ($workflowService->deleteStorageFileIfUnreferenced($file->file_path, [], $fileIds)) {
                    $deletedFiles++;
                }
            }

            $batchDir = "import_batches/{$batch->id}";
            $disk = Storage::disk('public');
            if ($disk->exists($batchDir) && empty($disk->allFiles($batchDir))) {
                $disk->deleteDirectory($batchDir);
            }

            $batchNo = $batch->batch_no;
            AuditLog::record('DELETE_DRAFT_IMPORT_BATCH', 'ImportBatch', $batch->id, [
                'batch_no' => $batchNo,
                'status' => $batch->status,
                'deleted_files' => $deletedFiles,
            ], null);

            foreach ($batch->documents as $doc) {
                $doc->validations()->delete();
                $doc->fields()->delete();
                $doc->items()->delete();
                $doc->delete();
            }

            ImportApplication::where('batch_id', $batch->id)->delete();
            $batch->files()->delete();
            $batch->delete();

            return [
                'batch_no' => $batchNo,
                'deleted_files' => $deletedFiles,
            ];
        });
    }

    /**
     * Hapus satu dokumen hasil ekstraksi draft dalam batch beserta aset file fisiknya.
     */
    public function deleteDocumentAndAssets(ImportDocument $document): array
    {
        if ($document->shipment_id) {
            $shipment = Shipment::withoutGlobalScope('client_access')
                ->withTrashed()
                ->find($document->shipment_id);

            if ($shipment && !$shipment->isDraftDeletable()) {
                throw new \RuntimeException('Dokumen batch tidak dapat dihapus karena proyek terkait sudah melewati tahap draft / terkirim CEISA.');
            }
        }

        $workflowService = app(ShipmentWorkflowService::class);

        return DB::transaction(function () use ($document, $workflowService) {
            $document->loadMissing(['file', 'batch']);
            $batch = $document->batch;
            $file = $document->file;
            $deletedFiles = 0;

            AuditLog::record('DELETE_DRAFT_IMPORT_DOCUMENT', 'ImportDocument', $document->id, [
                'batch_no' => $batch?->batch_no,
                'document_type' => $document->document_type,
                'file_name' => $file?->original_name,
            ], null);

            $document->validations()->delete();
            $document->fields()->delete();
            $document->items()->delete();
            $document->delete();

            if ($file && !$file->documents()->exists()) {
                if ($workflowService->deleteStorageFileIfUnreferenced($file->file_path, [], [$file->id])) {
                    $deletedFiles++;
                }
                $file->delete();
            }

            if ($batch) {
                $remainingFiles = $batch->files()->count();
                $remainingDocs = $batch->documents()->count();
                $batch->update([
                    'total_files' => $remainingFiles,
                    'processed_files' => $remainingFiles,
                    'success_count' => $remainingDocs,
                ]);
            }

            return [
                'batch_id' => $batch?->id,
                'deleted_files' => $deletedFiles,
            ];
        });
    }
}
