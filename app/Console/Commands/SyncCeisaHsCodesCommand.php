<?php

namespace App\Console\Commands;

use App\Services\Ceisa\CeisaBtkiTariffSyncService;
use Illuminate\Console\Command;

class SyncCeisaHsCodesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ceisa:sync-hs-codes 
                            {--source=btki : Sumber sinkronisasi (btki, api, all)}
                            {--force : Paksa menimpa data yang telah ada di database}
                            {--hs= : Kode HS spesifik untuk sinkronisasi via live API}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi master data HS Code BTKI 2022 & Regulasi Lartas dari Bea Cukai (DJBC / CEISA 4.0 Open API) ke Database Lokal';

    /**
     * Execute the console command.
     */
    public function handle(CeisaBtkiTariffSyncService $syncService): int
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>===============================================================</>');
        $this->line('<fg=cyan;options=bold>  PATS CEISA 4.0 - SINKRONISASI HS CODE & REGULASI LARTAS      </>');
        $this->line('<fg=cyan;options=bold>===============================================================</>');
        $this->newLine();

        $source = strtolower($this->option('source'));
        $force = (bool) $this->option('force');
        $specificHs = $this->option('hs');

        // Jika user meminta HS Code spesifik via API
        if ($specificHs) {
            $this->info("Menghubungi Live Gateway CEISA 4.0 untuk Pos Tarif: {$specificHs}...");
            $record = $syncService->syncFromCeisaApi($specificHs);

            if ($record) {
                $this->info("✓ Berhasil menyimpan Pos Tarif {$record->code} ke database!");
                $this->table(
                    ['Kode HS', 'Uraian Barang', 'BM (%)', 'PPN (%)', 'PPh (%)', 'Lartas', 'Sumber'],
                    [[
                        $record->code,
                        substr($record->description, 0, 50) . '...',
                        $record->duty_bm . '%',
                        $record->duty_ppn . '%',
                        $record->duty_pph . '%',
                        $record->has_lartas ? 'YA' : 'TIDAK',
                        $record->source,
                    ]]
                );
                return Command::SUCCESS;
            } else {
                $this->error("Gagal mengambil data dari API untuk HS: {$specificHs}");
                return Command::FAILURE;
            }
        }

        // Sinkronisasi Full Registry BTKI 2022
        $this->info("Memulai sinkronisasi master data BTKI 2022 Bea Cukai (PMK 26/PMK.010/2022)...");
        $result = $syncService->syncFromBtkiRegistry($force);

        if (!$result['success']) {
            $this->error("Gagal sinkronisasi: " . ($result['error'] ?? 'Terjadi kesalahan sistem'));
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info("✓ SINKRONISASI SELESAI!");
        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Total Pos Tarif Resmi BTKI', $result['total_registry']],
                ['Data Baru Ditambahkan', $result['inserted']],
                ['Data Diperbarui', $result['updated']],
                ['Regulasi Dokumen Lartas Terhubung', $result['regulations_synced']],
                ['Total HS Code Aktif di Database', $result['total_in_db']],
            ]
        );

        $stats = $syncService->getStats();
        $this->comment("Total Komoditas Lartas Terdeteksi: {$stats['total_lartas_codes']}");
        $this->comment("Total Aturan Regulasi Lartas: {$stats['total_regulations']}");
        $this->newLine();

        return Command::SUCCESS;
    }
}
