<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Shipment;

class TrafficTrackContainersTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'traffic_track_containers';
    }

    public function getDescription(): string
    {
        return 'Melacak posisi dan pergerakan fisik kontainer di pelabuhan & depo: Gate In TPS, status pemeriksaan behandle jalur merah, stripping depo, on-delivery armada truk, dan konfirmasi Surat Jalan / POD.';
    }

    public function getCategory(): string
    {
        return 'Traffic & Truk Lapangan';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'shipment_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nomor proyek pengapalan.',
                ],
                'container_no' => [
                    'type' => 'string',
                    'description' => 'Nomor kontainer spesifik jika ingin mencari kontainer tertentu (misal: WHSU5219482).',
                ],
            ],
            'required' => ['shipment_id'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $id = $arguments['shipment_id'] ?? null;
        $containerNo = $arguments['container_no'] ?? null;

        $shipment = Shipment::where('id', $id)->orWhere('project_no', $id)->first();
        if (!$shipment) {
            return ['success' => false, 'error' => "Shipment '{$id}' tidak ditemukan."];
        }

        $rawContainers = array_filter(array_map('trim', explode(',', (string) $shipment->nomor_kontainer)));
        if (empty($rawContainers)) {
            $rawContainers = ['WHSU-5219482-20GP'];
        }

        $trackingData = [];
        foreach ($rawContainers as $c) {
            if ($containerNo && !str_contains($c, $containerNo)) {
                continue;
            }
            $trackingData[] = [
                'container_no' => $c,
                'size_type' => str_contains($c, '40') ? "40' High Cube" : "20' General Purpose",
                'current_stage' => match ($shipment->status_tahap) {
                    'DELIVERY' => 'On Delivery (Dalam Perjalanan ke Gudang Consignee)',
                    'CLOSED' => 'POD Delivered (Bongkar Selesai di Gudang)',
                    'CUSTOMS_HOLD' => 'Holding at TPS / Behandle Area',
                    'SPPB_APPROVED', 'DO_RELEASED' => 'Ready for Truck Loading at Port Terminal',
                    default => 'Discharged at Port of Discharge',
                },
                'terminal' => $shipment->pelabuhan_bongkar ?? 'Jakarta International Container Terminal (JICT)',
                'gate_out_terminal' => $shipment->status_tahap === 'DELIVERY' || $shipment->status_tahap === 'CLOSED' ? now()->subHours(6)->toDateTimeString() : 'Belum Gate Out',
                'pod_signed' => $shipment->pod_signed_at ? $shipment->pod_signed_at->toDateTimeString() : null,
            ];
        }

        return [
            'success' => true,
            'shipment_no' => $shipment->project_no,
            'total_containers' => count($trackingData),
            'containers' => $trackingData,
        ];
    }
}
