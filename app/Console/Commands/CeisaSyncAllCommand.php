<?php

namespace App\Console\Commands;

use App\Services\Ceisa\CeisaDocumentSyncService;
use Illuminate\Console\Command;

class CeisaSyncAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ceisa:sync-all
                            {--nomor-aju= : Tarik dokumen tertentu berdasarkan Nomor Aju (26 digit)}
                            {--skip-kurs : Lewati sinkronisasi kurs pajak}
                            {--skip-hs : Lewati sinkronisasi master HS Code BTKI}';

    /**
     * The console command description.
     */
    protected $description = 'Tarik semua data dari CEISA 4.0 ke Genie (Dokumen Pabean, Kurs Pajak, dan Pos Tarif BTKI) secara aman tanpa mengirim data ke CEISA';

    public function handle(CeisaDocumentSyncService $syncService): int
    {
        $this->info('Memulai penarikan data CEISA 4.0 (Mode Read-Only: ' . (config('ceisa.api.read_only', true) ? 'AKTIF' : 'NONAKTIF') . ', Mode Gateway: ' . (config('ceisa.api.mock_mode') ? 'SANDBOX' : 'LIVE') . ')...');

        $nomorAju = $this->option('nomor-aju');
        if ($nomorAju) {
            $res = $syncService->syncByNomorAju((string) $nomorAju, true);
            $this->line(json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return ($res['success'] ?? false) ? self::SUCCESS : self::FAILURE;
        }

        $res = $syncService->syncAllFromCeisa(
            !$this->option('skip-kurs'),
            !$this->option('skip-hs')
        );

        $this->table(
            ['Komponen', 'Hasil', 'Keterangan'],
            [
                [
                    'Dokumen Pabean & Shipment',
                    ($res['documents']['synced'] ?? 0) . ' / ' . ($res['documents']['total_active'] ?? 0),
                    'Gagal: ' . ($res['documents']['failed'] ?? 0),
                ],
                [
                    'Kurs Pajak (NDPBM)',
                    ($res['kurs']['synced'] ?? 0) . ' valuta',
                    'Sumber: ' . implode(', ', $res['kurs']['sources'] ?? []),
                ],
                [
                    'Master Pos Tarif BTKI & Lartas',
                    ($res['hs_codes']['total_in_db'] ?? 0) . ' pos tarif',
                    'Regulasi Lartas tersinkron: ' . ($res['hs_codes']['regulations_synced'] ?? 0),
                ],
            ]
        );

        return self::SUCCESS;
    }
}
