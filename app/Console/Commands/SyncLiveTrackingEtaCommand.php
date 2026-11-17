<?php

namespace App\Console\Commands;

use App\Models\Shipment;
use App\Services\Tracking\LiveRadarTrackingService;
use Illuminate\Console\Command;

class SyncLiveTrackingEtaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:sync-live-eta {--shipment= : ID atau nomor proyek pengapalan tertentu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deteksi otomatis realtime kedatangan (ETA & status sandar/ATA) kargo via Free Open APIs (OpenSky ADS-B & Marine AIS)';

    /**
     * Execute the console command.
     */
    public function handle(LiveRadarTrackingService $radarService): int
    {
        $this->info('Memulai sinkronisasi realtime kedatangan kargo via Free Radar APIs...');

        $query = Shipment::with('shippingVendor')
            ->whereNull('actual_arrival_date')
            ->whereIn('status_tahap', [
                'DOC_COLLECTING',
                'DOC_READY',
                'HS_DETECTED',
                'DRAFT_REVIEW',
                'CEISA_SUBMITTED',
                'BILLING_ISSUED',
                'DO_PROCESSING',
                'TRAFFIC_HOLD',
            ]);

        $targetShipment = $this->option('shipment');
        if ($targetShipment) {
            $query->where(function ($q) use ($targetShipment) {
                $q->where('id', $targetShipment)
                  ->orWhere('project_no', $targetShipment);
            });
        }

        $shipments = $query->get();
        $this->info("Ditemukan {$shipments->count()} pengapalan aktif yang perlu dipantau.");

        $detectedCount = 0;
        $arrivedCount = 0;

        foreach ($shipments as $s) {
            $identifier = $s->project_no . ' (' . ($s->is_air_freight ? 'Air: ' . ($s->voyage_no ?: $s->nomor_bl_awb) : 'Sea: ' . ($s->nama_kapal ?: $s->voyage_no)) . ')';
            $this->line("Memeriksa {$identifier}...");

            try {
                $res = $radarService->detectArrival($s);
                $status = $res['status'] ?? 'OK';
                $eta = $res['eta_display'] ?? '-';
                $source = $res['source'] ?? 'API';

                $this->info(" -> [{$status}] ETA/ATA: {$eta} (via {$source})");
                $detectedCount++;

                if (!empty($res['is_arrived'])) {
                    $arrivedCount++;
                    $this->warn(" -> Kargo telah terkonfirmasi SANDAR/TIBA (ATA)!");
                }
            } catch (\Throwable $e) {
                $this->error(" -> Gagal mendeteksi {$s->project_no}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Selesai! {$detectedCount} pengapalan berhasil diperbarui. ({$arrivedCount} kargo terkonfirmasi tiba).");

        return Command::SUCCESS;
    }
}
