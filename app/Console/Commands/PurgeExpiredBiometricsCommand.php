<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use App\Models\BiometricConsent;
use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredBiometricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biometric:purge-expired 
                            {--dry-run : Simulasi tanpa menghapus berkas dan data nyata}
                            {--cutoff-years=2 : Jumlah tahun retensi maksimal data biometrik UU PDP}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pemusnahan berkas foto dan data biometrik yang telah melewati masa retensi 2 tahun (Kepatuhan UU PDP No. 27/2022)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $cutoffYears = (int) ($this->option('cutoff-years') ?: 2);
        $cutoffDate = now()->subYears($cutoffYears);

        $this->info("==========================================================================");
        $this->info(" GENIE ERP - UU PDP No. 27/2022 BIOMETRIC RETENTION PURGE ENGINE");
        $this->info("==========================================================================");
        $this->line("Batas Waktu Retensi (Cutoff) : {$cutoffDate->toDateTimeString()} ({$cutoffYears} Tahun)");
        $this->line("Mode Operasi                 : " . ($isDryRun ? '<fg=yellow;options=bold>SIMULASI (DRY-RUN)</>' : '<fg=green;options=bold>PRODUKSI (PERMANEN)</>'));
        $this->newLine();

        // ---------------------------------------------------------------------
        // 1. Purge Foto Presensi & Masking Skor Biometrik (Attendance Logs)
        // ---------------------------------------------------------------------
        $this->info("[1/2] Memproses log presensi melebihi masa retensi {$cutoffYears} tahun...");

        $query = AttendanceLog::where('at', '<', $cutoffDate)
            ->where(function ($q) {
                $q->whereNotNull('photo_url')
                  ->orWhereNotNull('face_match');
            });

        $deletedPhotoCount = 0;
        $maskedRecordCount = 0;

        $query->chunkById(100, function ($logs) use ($isDryRun, &$deletedPhotoCount, &$maskedRecordCount) {
            foreach ($logs as $log) {
                if (!empty($log->photo_url)) {
                    $filename = basename($log->photo_url);

                    if (!$isDryRun) {
                        // 1. Hapus dari disk private
                        try {
                            if (Storage::disk('private')->exists('attendances/' . $filename)) {
                                Storage::disk('private')->delete('attendances/' . $filename);
                            }
                        } catch (\Throwable $e) {
                            // Abaikan error disk driver jika fallback filesystem tersedia
                        }

                        // 2. Fallback cek filesystem lokal storage/app/private/attendances
                        $localPrivate = storage_path('app/private/attendances/' . $filename);
                        if (File::exists($localPrivate)) {
                            File::delete($localPrivate);
                        }

                        // 3. Fallback cek webroot public lama (uploads/attendances)
                        $legacyPublic = public_path('uploads/attendances/' . $filename);
                        if (File::exists($legacyPublic)) {
                            File::delete($legacyPublic);
                        }
                    }
                    $deletedPhotoCount++;
                }

                if (!$isDryRun) {
                    $log->update([
                        'photo_url' => null,
                        'face_match' => null, // Wajib dimask/nullify sesuai UU PDP No. 27/2022
                        'notes' => ($log->notes ? $log->notes . ' | ' : '') . '[Foto & Data Biometrik Dimusnahkan: UU PDP Retensi 2 Tahun]',
                    ]);
                }
                $maskedRecordCount++;
            }
        });

        $this->line("  - Berkas foto absensi dimusnahkan : {$deletedPhotoCount} berkas");
        $this->line("  - Baris data presensi dimasking   : {$maskedRecordCount} baris");

        // ---------------------------------------------------------------------
        // 2. Purge Template Biometrik Pegawai dengan Consent Kedaluwarsa / Dicabut
        // ---------------------------------------------------------------------
        $this->newLine();
        $this->info("[2/2] Memeriksa template biometrik wajah karyawan yang persetujuannya kedaluwarsa...");

        $expiredConsentEmployeeIds = BiometricConsent::where(function ($query) {
            $query->whereNotNull('retention_until')
                  ->where('retention_until', '<', now()->toDateString());
        })->orWhere(function ($query) {
            $query->whereNotNull('revoked_at')
                  ->where('revoked_at', '<', now()->subDays(30));
        })->pluck('employee_id')->unique();

        $purgedEmployeeProfiles = 0;

        if ($expiredConsentEmployeeIds->isNotEmpty()) {
            $employees = Employee::whereIn('id', $expiredConsentEmployeeIds)
                ->where(function ($q) {
                    $q->whereNotNull('face_data')
                      ->orWhereNotNull('photo_url');
                })
                ->get();

            foreach ($employees as $employee) {
                if (!empty($employee->photo_url)) {
                    $photoFile = basename($employee->photo_url);
                    if (!$isDryRun) {
                        try {
                            if (Storage::disk('local')->exists('private/biometrics/' . $photoFile)) {
                                Storage::disk('local')->delete('private/biometrics/' . $photoFile);
                            }
                            if (Storage::disk('private')->exists('biometrics/' . $photoFile)) {
                                Storage::disk('private')->delete('biometrics/' . $photoFile);
                            }
                        } catch (\Throwable $e) {
                            // Abaikan error disk jika berkas tidak ditemukan
                        }
                    }
                }

                if (!$isDryRun) {
                    $employee->update([
                        'face_data' => null,
                        'photo_url' => null,
                    ]);
                }
                $purgedEmployeeProfiles++;
            }
        }

        $this->line("  - Template wajah pegawai dicabut/dimusnahkan : {$purgedEmployeeProfiles} pegawai");

        // ---------------------------------------------------------------------
        // Ringkasan & Audit Log
        // ---------------------------------------------------------------------
        $this->newLine();
        $this->info("==========================================================================");
        $this->info(" PEMUSNAHAN SELESAI " . ($isDryRun ? '(SIMULASI BERHASIL)' : '(SUKSES TERTERAPKAN)'));
        $this->info("==========================================================================");

        if (!$isDryRun) {
            Log::info("UU PDP Biometric Purge Executed: {$deletedPhotoCount} photos deleted, {$maskedRecordCount} records masked, {$purgedEmployeeProfiles} employee templates wiped.");
        }

        return Command::SUCCESS;
    }
}
