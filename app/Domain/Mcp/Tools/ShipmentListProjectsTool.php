<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Shipment;

class ShipmentListProjectsTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'shipment_list_projects';
    }

    public function getDescription(): string
    {
        return 'Mencari dan memfilter daftar proyek pengapalan (impor/ekspor) berdasarkan status tahapan, nama importir, nomor BL/AWB, atau jalur kepabeanan.';
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
                'search' => [
                    'type' => 'string',
                    'description' => 'Kata kunci pencarian (nomor project, nomor BL, consignee, kapal).',
                ],
                'status_tahap' => [
                    'type' => 'string',
                    'description' => 'Filter status tahapan (misal: DRAFT, DOCUMENT_RECEIVED, EDI_SUBMITTED, CUSTOMS_HOLD, SPPB_APPROVED, DO_RELEASED, DELIVERY, CLOSED).',
                ],
                'customs_channel' => [
                    'type' => 'string',
                    'enum' => ['HIJAU', 'KUNING', 'MERAH', 'MITA', 'AEO'],
                    'description' => 'Filter warna jalur kepabeanan.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Jumlah maksimal data yang ditampilkan (default: 15).',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $query = Shipment::with(['client', 'shippingVendor'])->latest();

        if (!empty($arguments['search'])) {
            $s = trim($arguments['search']);
            $query->where(function ($q) use ($s) {
                $q->where('project_no', 'like', "%{$s}%")
                  ->orWhere('nomor_bl_awb', 'like', "%{$s}%")
                  ->orWhere('nama_kapal', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$s}%"));
            });
        }

        if (!empty($arguments['status_tahap'])) {
            $query->where('status_tahap', $arguments['status_tahap']);
        }

        if (!empty($arguments['customs_channel'])) {
            $query->where('customs_channel', $arguments['customs_channel']);
        }

        $limit = min((int) ($arguments['limit'] ?? 15), 50);
        $shipments = $query->take($limit)->get();

        return [
            'success' => true,
            'total_found' => $shipments->count(),
            'shipments' => $shipments->map(fn($s) => [
                'id' => $s->id,
                'project_no' => $s->project_no,
                'client_name' => $s->client?->name ?? 'Unknown',
                'service_type' => $s->jenis_layanan,
                'bl_awb_no' => $s->nomor_bl_awb,
                'vessel' => trim(($s->nama_kapal ?? '') . ' ' . ($s->voyage_no ?? '')),
                'status_tahap' => $s->status_tahap,
                'customs_channel' => $s->customs_channel ?? 'HIJAU',
                'status_do' => $s->status_do ?? 'PENDING',
                'eta' => $s->eta_date?->format('Y-m-d'),
                'containers' => $s->nomor_kontainer,
            ])->toArray(),
        ];
    }
}
