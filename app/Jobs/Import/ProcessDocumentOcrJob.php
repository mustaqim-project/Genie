<?php

namespace App\Jobs\Import;

use App\Models\ImportBatch;
use App\Services\Shipment\BatchImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProcessDocumentOcrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ImportBatch $batch;
    public string $filePath;
    public string $originalName;

    /**
     * Create a new job instance.
     */
    public function __construct(ImportBatch $batch, string $filePath, string $originalName)
    {
        $this->batch = $batch;
        $this->filePath = $filePath;
        $this->originalName = $originalName;
    }

    /**
     * Execute the job.
     */
    public function handle(BatchImportService $importService): void
    {
        Log::info("Memproses OCR Dokumen Asinkron: {$this->originalName} untuk Batch {$this->batch->batch_no}");

        if (!File::exists($this->filePath)) {
            Log::warning("Berkas fisik tidak ditemukan untuk OCR: {$this->filePath}");
            $this->batch->increment('error_count');
            $this->batch->increment('processed_files');
            return;
        }

        try {
            $importService->processFileFromPath($this->batch, $this->filePath, $this->originalName);
        } catch (\Throwable $e) {
            Log::error("Error memproses OCR berkas {$this->originalName}: " . $e->getMessage());
        }
    }
}
