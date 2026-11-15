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
use ZipArchive;

class ExtractBatchZipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ImportBatch $batch;
    public string $zipFilePath;

    /**
     * Create a new job instance.
     */
    public function __construct(ImportBatch $batch, string $zipFilePath)
    {
        $this->batch = $batch;
        $this->zipFilePath = $zipFilePath;
    }

    /**
     * Execute the job.
     */
    public function handle(BatchImportService $importService): void
    {
        Log::info("Memulai ekstraksi berkas ZIP untuk ImportBatch: {$this->batch->batch_no}");

        if (!File::exists($this->zipFilePath)) {
            $this->batch->update(['status' => 'FAILED', 'error_count' => 1]);
            Log::error("Berkas ZIP tidak ditemukan: {$this->zipFilePath}");
            return;
        }

        $zip = new ZipArchive();
        if ($zip->open($this->zipFilePath) !== true) {
            $this->batch->update(['status' => 'FAILED', 'error_count' => 1]);
            Log::error("Gagal membuka arsip ZIP: {$this->zipFilePath}");
            return;
        }

        // Security Check: Zip Bomb Protection (Max 2GB uncompressed, Max 100:1 ratio)
        $totalUncompressed = 0;
        $compressedSize = filesize($this->zipFilePath) ?: 1;
        $maxUncompressed = 2 * 1024 * 1024 * 1024; // 2GB

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat && isset($stat['size'])) {
                $totalUncompressed += $stat['size'];
            }
        }

        $compressionRatio = $compressedSize > 0 ? ($totalUncompressed / $compressedSize) : 0;
        if ($totalUncompressed > $maxUncompressed || $compressionRatio > 100) {
            $zip->close();
            $this->batch->update(['status' => 'FAILED', 'error_count' => 1]);
            Log::error("Zip Bomb Ditolak: Ukuran tak terkompresi {$totalUncompressed} bytes, rasio {$compressionRatio}:1");
            throw new \InvalidArgumentException("Zip Bomb Terdeteksi: Rasio kompresi ({$compressionRatio}:1) atau ukuran (> 2GB) melebihi batas aman.");
        }

        $extractPath = storage_path('app/private/imports/extracted_' . $this->batch->id);
        if (!File::exists($extractPath)) {
            File::makeDirectory($extractPath, 0750, true);
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Cari seluruh berkas yang diekstrak (PDF, JPG, PNG)
        $extractedFiles = File::allFiles($extractPath);
        $this->batch->update([
            'total_files' => count($extractedFiles),
            'status' => 'PROCESSING',
        ]);

        foreach ($extractedFiles as $file) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'tif', 'tiff'])) {
                // Dispatch job OCR untuk masing-masing berkas secara asinkron
                ProcessDocumentOcrJob::dispatch($this->batch, $file->getRealPath(), $file->getFilename());
            }
        }

        Log::info("Ekstraksi ZIP selesai untuk batch {$this->batch->batch_no}. Total berkas: " . count($extractedFiles));
    }
}
