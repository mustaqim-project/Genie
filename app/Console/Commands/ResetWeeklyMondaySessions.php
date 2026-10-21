<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ResetWeeklyMondaySessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:reset-weekly-monday {--force : Hapus seluruh sesi aktif tanpa memandang tanggal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset dan bersihkan sesi pengguna 1x setiap 7 hari tepat pada hari Senin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        if (!Schema::hasTable('sessions')) {
            $this->warn('Tabel sessions tidak ditemukan pada basis data.');
            return self::SUCCESS;
        }

        if ($force) {
            $deleted = DB::table('sessions')->delete();
            $this->info("Berhasil mereset seluruh {$deleted} sesi pengguna aktif (Force Reset).");
            Log::info("Force Reset: Seluruh {$deleted} sesi pengguna dihapus.");
            return self::SUCCESS;
        }

        // Ambil timestamp awal hari Senin minggu berjalan (00:00:00)
        $mondayStartEpoch = now()->startOfWeek(Carbon::MONDAY)->startOfDay()->timestamp;

        // Hapus sesi yang aktifitas terakhirnya terjadi sebelum hari Senin minggu ini
        $deleted = DB::table('sessions')
            ->where('last_activity', '<', $mondayStartEpoch)
            ->delete();

        $mondayDate = now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $this->info("Pembersihan sesi mingguan (Senin, {$mondayDate}): Berhasil menghapus {$deleted} sesi usang.");
        Log::info("Pembersihan Sesi Mingguan Senin ({$mondayDate}): {$deleted} sesi dihapus.");

        return self::SUCCESS;
    }
}
