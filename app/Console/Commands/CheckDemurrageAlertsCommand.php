<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Shipment;
use App\Models\SystemSetting;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckDemurrageAlertsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipment:check-demurrage {--dry-run : Simulasi tanpa membuat to-do/notifikasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pemeriksaan harian sisa Free Time kontainer & penerbitan alert mitigasi Demurrage (H-3, H-2, H-1)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info("Menjalankan audit sisa Free Time kontainer pelabuhan...");

        // Ambil pengapalan aktif yang belum Gate Out / Selesai
        $activeShipments = Shipment::whereNotNull('free_time_expired_at')
            ->whereNotIn('status_tahap', ['GATE_OUT', 'DELIVERED_POD', 'COMPLETED'])
            ->with(['client', 'vendor'])
            ->get();

        $warnCount = 0;
        $criticalCount = 0;

        $opsManager = User::whereHas('roles', function ($q) {
            $q->whereIn('code', ['R-MGR', 'R-DO', 'R-ADM']);
        })->first();

        $thresholdConfig = SystemSetting::get('demurrage_threshold_config', []);
        $alertDays = $thresholdConfig['alert_days'] ?? [3, 2, 1];
        $maxAlertDay = !empty($alertDays) ? max($alertDays) : 3;

        foreach ($activeShipments as $shipment) {
            $daysLeft = (int) now()->diffInDays($shipment->free_time_expired_at, false);

            if ($daysLeft <= $maxAlertDay) {
                $urgency = $daysLeft <= 1 ? 'CRITICAL' : ($daysLeft <= 2 ? 'HIGH' : 'MEDIUM');
                $statusText = $daysLeft < 0 ? "KEDALUWARSA (" . abs($daysLeft) . " hari lalu)" : "Sisa {$daysLeft} hari";

                $this->warn("Shipment [{$shipment->project_no}] BL: {$shipment->nomor_bl_awb} - {$statusText} (Batas: {$shipment->free_time_expired_at->format('d/m/Y')})");

                if (!$isDryRun) {
                    // Buat tugas To-Do eskalasi ke Tim DO & Operasional jika belum ada
                    if ($opsManager) {
                        Todo::firstOrCreate(
                            [
                                'title' => "ALERT DEMURRAGE: {$shipment->nomor_kontainer} ({$statusText})",
                                'user_id' => $opsManager->id,
                            ],
                            [
                                'description' => "Kontainer {$shipment->nomor_kontainer} (BL: {$shipment->nomor_bl_awb}) mendekati batas Free Time ({$shipment->free_time_expired_at->format('d/m/Y')}). Segera selesaikan penebusan DO dan penarikan armada trucking.",
                                'due_date' => $shipment->free_time_expired_at->toDateString(),
                                'priority' => $urgency === 'CRITICAL' ? 'URGENT' : 'HIGH',
                                'status' => 'PENDING',
                                'category' => 'DO_OPERATIONS',
                            ]
                        );
                    }

                    AuditLog::record('DEMURRAGE_ALERT_TRIGGERED', 'Shipment', $shipment->id, null, [
                        'project_no' => $shipment->project_no,
                        'nomor_bl_awb' => $shipment->nomor_bl_awb,
                        'nomor_kontainer' => $shipment->nomor_kontainer,
                        'days_left' => $daysLeft,
                        'free_time_expired_at' => $shipment->free_time_expired_at->toDateTimeString(),
                    ]);
                }

                if ($daysLeft <= 1) {
                    $criticalCount++;
                } else {
                    $warnCount++;
                }
            }
        }

        $this->info("Pemeriksaan selesai. Terdeteksi {$criticalCount} kontainer kritis (<= H-1) dan {$warnCount} kontainer peringatan (H-2 s.d H-3).");

        return Command::SUCCESS;
    }
}
