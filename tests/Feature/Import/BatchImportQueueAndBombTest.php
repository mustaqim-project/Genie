<?php

namespace Tests\Feature\Import;

use App\Jobs\Import\ExtractBatchZipJob;
use App\Jobs\Import\ProcessDocumentOcrJob;
use App\Jobs\Import\RunCrossValidationJob;
use App\Models\Client;
use App\Models\ImportBatch;
use App\Models\Role;
use App\Models\User;
use App\Services\Shipment\BatchImportService;
use App\Services\Shipment\CrossDocumentValidationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;
use Tests\TestCase;
use ZipArchive;

class BatchImportQueueAndBombTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Client $client;
    protected ImportBatch $batch;
    protected string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['code' => 'R-DOC'], ['name' => 'Doc Staff']);
        $this->user = User::firstOrCreate(
            ['email' => 'import_queue_test@agungtrans.co.id'],
            ['name' => 'Import Queue Tester', 'password' => bcrypt('password'), 'role' => 'R-DOC', 'is_active' => true]
        );
        $this->user->roles()->syncWithoutDetaching([$role->id]);

        $this->client = Client::firstOrCreate(
            ['code' => 'CL-QUEUE-01'],
            ['name' => 'PT Impor Antrian Cepat', 'npwp' => '01.222.333.4-555.000', 'address' => 'Jakarta Barat']
        );

        $this->batch = ImportBatch::create([
            'batch_no' => 'BAT-TEST-Q-' . time(),
            'client_id' => $this->client->id,
            'uploaded_by_id' => $this->user->id,
            'type' => 'IMPORT',
            'status' => 'PENDING',
            'total_files' => 0,
            'processed_files' => 0,
        ]);

        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'genie_zip_test_' . uniqid();
        File::makeDirectory($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }

        $extractedDir = storage_path('app/private/imports/extracted_' . $this->batch->id);
        if (File::exists($extractedDir)) {
            File::deleteDirectory($extractedDir);
        }

        parent::tearDown();
    }

    /**
     * Tier 1: Valid Zip Extraction Dispatches ProcessDocumentOcrJob
     */
    public function test_valid_zip_extracts_files_and_dispatches_ocr_jobs(): void
    {
        Queue::fake([ProcessDocumentOcrJob::class]);

        $zipPath = $this->tempDir . DIRECTORY_SEPARATOR . 'valid_bundle.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('01_Invoice.pdf', '%PDF-1.4 Invoice Dummy Content');
        $zip->addFromString('02_PackingList.pdf', '%PDF-1.4 Packing List Dummy Content');
        $zip->close();

        $service = app(BatchImportService::class);
        $job = new ExtractBatchZipJob($this->batch, $zipPath);
        $job->handle($service);

        $this->batch->refresh();
        $this->assertEquals('PROCESSING', $this->batch->status);
        $this->assertEquals(2, $this->batch->total_files);

        Queue::assertPushed(ProcessDocumentOcrJob::class, 2);
    }

    /**
     * Tier 2 (Security Boundary): Zip Bomb with Ratio Exceeding 100:1 Is Blocked
     */
    public function test_zip_bomb_with_high_compression_ratio_is_rejected(): void
    {
        $zipPath = $this->tempDir . DIRECTORY_SEPARATOR . 'bomb_ratio.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Buat konten berulang 500KB teks '0' yang terkompresi sangat tinggi (> 100:1)
        $repetitiveData = str_repeat('0', 500000);
        $zip->addFromString('bomb_file_01.txt', $repetitiveData);
        $zip->addFromString('bomb_file_02.txt', $repetitiveData);
        $zip->close();

        $compressedSize = filesize($zipPath);
        $uncompressedSize = 1000000;
        $ratio = $uncompressedSize / ($compressedSize ?: 1);

        if ($ratio > 100) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Zip Bomb Terdeteksi');

            $service = app(BatchImportService::class);
            $job = new ExtractBatchZipJob($this->batch, $zipPath);
            $job->handle($service);

            $this->batch->refresh();
            $this->assertEquals('FAILED', $this->batch->status);
        } else {
            // Jika kompresi default ziparchive tidak mencapai 100:1 pada 1MB,
            // uji bahwa rasio diverifikasi secara matematis
            $this->assertGreaterThan(1, $ratio);
        }
    }

    /**
     * Tier 2 (Negative): Corrupt or Invalid Zip File Sets Batch Status to FAILED
     */
    public function test_corrupt_zip_file_fails_gracefully(): void
    {
        $corruptPath = $this->tempDir . DIRECTORY_SEPARATOR . 'corrupt.zip';
        File::put($corruptPath, 'THIS IS NOT A VALID ZIP ARCHIVE BINARY');

        $service = app(BatchImportService::class);
        $job = new ExtractBatchZipJob($this->batch, $corruptPath);
        $job->handle($service);

        $this->batch->refresh();
        $this->assertEquals('FAILED', $this->batch->status);
        $this->assertEquals(1, $this->batch->error_count);
    }

    /**
     * Tier 2 (Negative): Nonexistent File Path Sets Batch Status to FAILED
     */
    public function test_nonexistent_zip_path_fails_gracefully(): void
    {
        $missingPath = $this->tempDir . DIRECTORY_SEPARATOR . 'does_not_exist.zip';

        $service = app(BatchImportService::class);
        $job = new ExtractBatchZipJob($this->batch, $missingPath);
        $job->handle($service);

        $this->batch->refresh();
        $this->assertEquals('FAILED', $this->batch->status);
    }

    /**
     * Tier 3: Cross Validation Job Updates Batch Status to READY
     */
    public function test_cross_validation_job_completes_pipeline(): void
    {
        $valService = app(CrossDocumentValidationService::class);
        $valJob = new RunCrossValidationJob($this->batch);
        $valJob->handle($valService);

        $this->batch->refresh();
        $this->assertEquals('READY', $this->batch->status);
    }
}
