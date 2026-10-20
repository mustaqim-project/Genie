<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Shipment;

class ShipmentGetDetailTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'shipment_get_detail';
    }

    public function getDescription(): string
    {
        return 'Mengambil rincian lengkap suatu proyek pengapalan, mencakup data kargo, kontainer, perizinan pabean, rincian barang packing list, status tebus DO pelayaran, dan status pengiriman armada truk.';
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
            ],
            'required' => ['shipment_id'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $id = $arguments['shipment_id'] ?? null;
        if (!$id) {
            return ['success' => false, 'error' => 'Parameter shipment_id wajib diisi.'];
        }

        $shipment = Shipment::with([
            'client',
            'shippingVendor',
            'shipmentItems.itemDocuments',
            'documents',
            'pics',
        ])->where('id', $id)->orWhere('project_no', $id)->first();

        if (!$shipment) {
            return ['success' => false, 'error' => "Shipment '{$id}' tidak ditemukan."];
        }

        return [
            'success' => true,
            'shipment' => [
                'id' => $shipment->id,
                'project_no' => $shipment->project_no,
                'client' => [
                    'name' => $shipment->client?->name,
                    'npwp' => $shipment->client?->npwp,
                    'nib' => $shipment->client?->nib,
                    'phone' => $shipment->client?->phone,
                ],
                'layanan' => $shipment->jenis_layanan,
                'bl_awb' => [
                    'nomor' => $shipment->nomor_bl_awb,
                    'tanggal' => $shipment->tgl_bl_awb?->format('Y-m-d'),
                ],
                'pelayaran' => [
                    'vendor' => $shipment->shippingVendor?->name,
                    'kapal' => $shipment->nama_kapal,
                    'voyage' => $shipment->voyage_no,
                    'pol' => $shipment->pelabuhan_muat,
                    'pod' => $shipment->pelabuhan_bongkar,
                    'eta' => $shipment->eta_date?->format('Y-m-d'),
                ],
                'kontainer' => [
                    'total' => $shipment->total_kontainer,
                    'nomor' => $shipment->nomor_kontainer,
                    'bruto' => $shipment->total_bruto,
                    'netto' => $shipment->total_netto,
                ],
                'nilai_pabean' => [
                    'valuta' => $shipment->kode_valuta ?? 'USD',
                    'nilai_valuta' => (float) $shipment->total_nilai_valuta,
                    'kurs_ndpbm' => (float) $shipment->ndbm_kurs,
                    'cif_idr' => (float) $shipment->total_nilai_idr,
                ],
                'status_operasional' => [
                    'tahap' => $shipment->status_tahap,
                    'customs_channel' => $shipment->customs_channel,
                    'status_do' => $shipment->status_do,
                    'nomor_do' => $shipment->nomor_do,
                    'do_expired' => $shipment->do_expired_at?->format('Y-m-d'),
                    'free_time_expired' => $shipment->free_time_expired_at?->format('Y-m-d'),
                ],
                'total_items' => $shipment->shipmentItems->count(),
                'sample_items' => $shipment->shipmentItems->take(5)->map(fn($item) => [
                    'desc' => $item->uraian_barang,
                    'hs_code' => $item->hs_code,
                    'qty' => $item->jumlah_kemasan . ' ' . $item->jenis_kemasan,
                    'netto' => $item->netto,
                    'bm' => $item->tarif_bm,
                ]),
            ],
        ];
    }
}
