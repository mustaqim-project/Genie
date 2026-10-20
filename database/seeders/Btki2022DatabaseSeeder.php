<?php

namespace Database\Seeders;

use App\Services\Ceisa\CeisaBtkiTariffSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class Btki2022DatabaseSeeder extends Seeder
{
    /**
     * Seed BTKI 2022 Master Tariffs and Customs Regulations.
     */
    public function run(CeisaBtkiTariffSyncService $syncService): void
    {
        $this->command->info('Memulai sinkronisasi master data HS Code BTKI 2022 Bea Cukai...');
        
        $result = $syncService->syncFromBtkiRegistry(true);

        if ($result['success']) {
            $this->command->info("Berhasil sinkronisasi BTKI 2022!");
            $this->command->info("- Total Pos Tarif: {$result['total_registry']}");
            $this->command->info("- Baru Disimpan: {$result['inserted']}");
            $this->command->info("- Diperbarui: {$result['updated']}");
            $this->command->info("- Regulasi Lartas Terhubung: {$result['regulations_synced']}");
            $this->command->info("- Total di Database: {$result['total_in_db']}");
        } else {
            $this->command->error("Gagal sinkronisasi BTKI 2022: " . ($result['error'] ?? 'Unknown error'));
        }
    }
}
