<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Shipment;

class TrafficAssignFleetTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'traffic_assign_fleet';
    }

    public function getDescription(): string
    {
        return 'Menugaskan armada angkutan trucking lapangan untuk pengiriman kontainer pabean: tentukan vendor trucking, nomor polisi truk, nama & nomor HP driver, serta alamat bongkar consignee.';
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
                'trucking_vendor' => [
                    'type' => 'string',
                    'description' => 'Nama perusahaan vendor trucking (misal: PT Agung Trans Mandiri, Dunex Express).',
                ],
                'police_number' => [
                    'type' => 'string',
                    'description' => 'Nomor polisi truk trailer (misal: B 9281 UEM).',
                ],
                'driver_name' => [
                    'type' => 'string',
                    'description' => 'Nama pengemudi truk.',
                ],
                'driver_phone' => [
                    'type' => 'string',
                    'description' => 'Nomor kontak WhatsApp/telepon pengemudi.',
                ],
                'destination_address' => [
                    'type' => 'string',
                    'description' => 'Alamat lengkap pabrik / gudang tujuan bongkar kargo.',
                ],
            ],
            'required' => ['shipment_id', 'police_number'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $id = $arguments['shipment_id'] ?? null;
        if (!$id) {
            return ['success' => false, 'error' => 'Parameter shipment_id wajib diisi.'];
        }

        $shipment = Shipment::where('id', $id)->orWhere('project_no', $id)->first();
        if (!$shipment) {
            return ['success' => false, 'error' => "Shipment '{$id}' tidak ditemukan."];
        }

        $truckingNotes = "Trucking: " . ($arguments['trucking_vendor'] ?? 'Internal Fleet')
            . " | Nopol: " . $arguments['police_number']
            . " | Driver: " . ($arguments['driver_name'] ?? 'Driver Assigned')
            . " (" . ($arguments['driver_phone'] ?? '-') . ")"
            . (!empty($arguments['destination_address']) ? " | Dest: " . $arguments['destination_address'] : "");

        $shipment->update([
            'status_tahap' => 'DELIVERY',
            'trucking_vendor_reason' => $truckingNotes,
        ]);

        return [
            'success' => true,
            'message' => "Penugasan armada truk untuk '{$shipment->project_no}' berhasil disimpan dan status diubah menjadi DELIVERY.",
            'shipment_no' => $shipment->project_no,
            'fleet_assignment' => [
                'vendor' => $arguments['trucking_vendor'] ?? 'Internal Fleet',
                'police_no' => $arguments['police_number'],
                'driver' => $arguments['driver_name'] ?? 'Driver Assigned',
                'phone' => $arguments['driver_phone'] ?? '-',
                'status' => 'DELIVERY_DISPATCHED',
            ],
        ];
    }
}
