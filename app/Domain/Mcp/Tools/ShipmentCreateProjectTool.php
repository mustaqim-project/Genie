<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Client;
use App\Models\Shipment;
use Illuminate\Support\Str;

class ShipmentCreateProjectTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'shipment_create_project';
    }

    public function getDescription(): string
    {
        return 'Membuat proyek pengapalan baru (Job Shipment P01) untuk impor (PIB) atau ekspor (PEB) lengkap dengan data klien, dokumen pengapalan, pelayaran, dan muatan kontainer.';
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
                'client_name' => [
                    'type' => 'string',
                    'description' => 'Nama perusahaan importir/eksportir.',
                ],
                'client_id' => [
                    'type' => 'string',
                    'description' => 'UUID klien jika sudah terdaftar.',
                ],
                'service_type' => [
                    'type' => 'string',
                    'enum' => ['IMPORT', 'EXPORT'],
                    'description' => 'Tipe pengapalan (default: IMPORT).',
                ],
                'bl_awb_no' => [
                    'type' => 'string',
                    'description' => 'Nomor Bill of Lading (B/L) atau Airway Bill (AWB).',
                ],
                'vessel_name' => [
                    'type' => 'string',
                    'description' => 'Nama kapal pengangkut (misal: WAN HAI 312).',
                ],
                'voyage_no' => [
                    'type' => 'string',
                    'description' => 'Nomor voyage pelayaran.',
                ],
                'pol' => [
                    'type' => 'string',
                    'description' => 'Pelabuhan muat (Port of Loading).',
                ],
                'pod' => [
                    'type' => 'string',
                    'description' => 'Pelabuhan bongkar (Port of Discharge, misal: Tanjung Priok, Samarinda).',
                ],
                'containers' => [
                    'type' => 'string',
                    'description' => 'Daftar nomor kontainer dipisahkan koma (misal: WHSU5219482, TEMU9182371).',
                ],
                'total_gross_weight' => [
                    'type' => 'number',
                    'description' => 'Total berat kotor (KGS).',
                ],
            ],
            'required' => ['bl_awb_no'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $clientId = $arguments['client_id'] ?? null;
        if (!$clientId && !empty($arguments['client_name'])) {
            $client = Client::where('name', 'like', '%' . trim($arguments['client_name']) . '%')->first();
            if (!$client) {
                $client = Client::create([
                    'code' => 'CLI-' . strtoupper(Str::random(4)),
                    'name' => trim($arguments['client_name']),
                    'is_active' => true,
                ]);
            }
            $clientId = $client->id;
        }

        if (!$clientId) {
            $client = Client::first();
            $clientId = $client?->id;
        }

        $projectNo = 'SHP-' . date('Ym') . '-' . str_pad((string) (Shipment::count() + 1), 4, '0', STR_PAD_LEFT);

        $shipment = Shipment::create([
            'project_no' => $projectNo,
            'client_id' => $clientId,
            'jenis_layanan' => $arguments['service_type'] ?? 'IMPORT',
            'nomor_bl_awb' => $arguments['bl_awb_no'],
            'tgl_bl_awb' => now(),
            'nama_kapal' => $arguments['vessel_name'] ?? 'Vessel TBD',
            'voyage_no' => $arguments['voyage_no'] ?? 'V.01',
            'pelabuhan_muat' => $arguments['pol'] ?? 'SHANGHAI',
            'pelabuhan_bongkar' => $arguments['pod'] ?? 'IDTPP - TANJUNG PRIOK',
            'total_kontainer' => !empty($arguments['containers']) ? count(explode(',', $arguments['containers'])) : 1,
            'nomor_kontainer' => $arguments['containers'] ?? null,
            'total_bruto' => $arguments['total_gross_weight'] ?? 1000.0,
            'status_tahap' => 'DOCUMENT_RECEIVED',
            'status_do' => 'PENDING',
            'customs_channel' => 'HIJAU',
        ]);

        return [
            'success' => true,
            'message' => "Proyek pengapalan '{$projectNo}' berhasil dibuat.",
            'shipment' => [
                'id' => $shipment->id,
                'project_no' => $shipment->project_no,
                'client_id' => $shipment->client_id,
                'nomor_bl_awb' => $shipment->nomor_bl_awb,
                'status_tahap' => $shipment->status_tahap,
            ],
        ];
    }
}
