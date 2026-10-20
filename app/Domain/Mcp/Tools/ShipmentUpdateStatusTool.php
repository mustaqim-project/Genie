<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Shipment;

class ShipmentUpdateStatusTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'shipment_update_status';
    }

    public function getDescription(): string
    {
        return 'Memperbarui milestone tahapan status proyek pengapalan (misal: dari EDI_SUBMITTED ke SPPB_APPROVED, DO_RELEASED, DELIVERY, atau CLOSED) beserta catatan operasional.';
    }

    public function getCategory(): string
    {
        return 'Operasional & Pengapalan (P01)';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'shipment_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nomor proyek pengapalan (misal: SHP-202610-0011).',
                ],
                'status_tahap' => [
                    'type' => 'string',
                    'enum' => [
                        'DRAFT',
                        'DOCUMENT_RECEIVED',
                        'EDI_SUBMITTED',
                        'CUSTOMS_HOLD',
                        'SPPB_APPROVED',
                        'DO_RELEASED',
                        'DELIVERY',
                        'CLOSED',
                    ],
                    'description' => 'Milestone status tahapan operasional baru.',
                ],
                'customs_channel' => [
                    'type' => 'string',
                    'enum' => ['HIJAU', 'KUNING', 'MERAH', 'MITA', 'AEO'],
                    'description' => 'Perbarui warna jalur pabean jika ada penetapan baru dari Bea Cukai.',
                ],
                'catatan' => [
                    'type' => 'string',
                    'description' => 'Catatan progres operasional pabean atau lapangan.',
                ],
            ],
            'required' => ['shipment_id', 'status_tahap'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $id = $arguments['shipment_id'] ?? null;
        $statusTahap = $arguments['status_tahap'] ?? null;

        if (!$id || !$statusTahap) {
            return ['success' => false, 'error' => 'Parameter shipment_id dan status_tahap wajib diisi.'];
        }

        $shipment = Shipment::where('id', $id)->orWhere('project_no', $id)->first();
        if (!$shipment) {
            return ['success' => false, 'error' => "Shipment '{$id}' tidak ditemukan."];
        }

        $oldStatus = $shipment->status_tahap;
        $updates = ['status_tahap' => $statusTahap];

        if (!empty($arguments['customs_channel'])) {
            $updates['customs_channel'] = $arguments['customs_channel'];
        }

        if (!empty($arguments['catatan'])) {
            $updates['catatan'] = trim($arguments['catatan']);
        }

        if ($statusTahap === 'CLOSED') {
            $updates['closed_at'] = now();
        }

        $shipment->update($updates);

        return [
            'success' => true,
            'message' => "Status pengapalan '{$shipment->project_no}' berhasil diperbarui dari '{$oldStatus}' menjadi '{$statusTahap}'.",
            'shipment_no' => $shipment->project_no,
            'new_status' => $statusTahap,
            'customs_channel' => $shipment->customs_channel,
            'updated_at' => now()->toDateTimeString(),
        ];
    }
}
