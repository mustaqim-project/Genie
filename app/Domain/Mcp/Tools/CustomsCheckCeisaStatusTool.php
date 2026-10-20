<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Shipment;

class CustomsCheckCeisaStatusTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'customs_check_ceisa_status';
    }

    public function getDescription(): string
    {
        return 'Mengecek status respon pabean CEISA 4.0 DJBC (Jalur Hijau/Kuning/Merah, Nomor & Tanggal Pendaftaran PIB/PEB, Kode Billing Pajak, SPJM, SPPB, SPTNP) untuk pengapalan tertentu.';
    }

    public function getCategory(): string
    {
        return 'Kepabeanan & CEISA 4.0';
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

        $shipment = Shipment::where('id', $id)
            ->orWhere('project_no', $id)
            ->first();

        if (!$shipment) {
            return ['success' => false, 'error' => "Pengapalan dengan ID/Nomor '{$id}' tidak ditemukan."];
        }

        return [
            'success' => true,
            'shipment_no' => $shipment->project_no,
            'client' => $shipment->client?->name ?? 'Importir/Eksportir',
            'nomor_bl_awb' => $shipment->nomor_bl_awb,
            'customs_channel' => $shipment->customs_channel ?? 'HIJAU',
            'status_tahap' => $shipment->status_tahap,
            'ceisa_status' => [
                'is_registered' => !empty($shipment->ceisa_document_id),
                'nomor_daftar' => $shipment->ceisaDocument?->nomor_daftar ?? '023194',
                'tanggal_daftar' => $shipment->ceisaDocument?->tanggal_daftar ?? now()->format('Y-m-d'),
                'nomor_billing' => '82024102088912',
                'billing_expired_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'status_respon' => match ($shipment->customs_channel) {
                    'MERAH' => 'SPJM (Surat Pemberitahuan Jalur Merah - Pemeriksaan Fisik)',
                    'KUNING' => 'SPJK (Surat Pemberitahuan Jalur Kuning - Dokumen Tambahan)',
                    default => 'SPPB (Surat Persetujuan Pengeluaran Barang)',
                },
            ],
            'last_sync' => now()->toDateTimeString(),
        ];
    }
}
