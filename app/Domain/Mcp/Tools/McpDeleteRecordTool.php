<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Client;
use App\Models\ClientInvoice;
use App\Models\Shipment;
use App\Models\VendorBill;

class McpDeleteRecordTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'mcp_delete_record';
    }

    public function getDescription(): string
    {
        return 'Menghapus atau membatalkan (DELETE / VOID) data operasional atau keuangan: membatalkan proyek pengapalan draf, menghapus invoice belum bayar, tagihan vendor yang keliru, atau data klien non-aktif dengan protokol keamanan ganda.';
    }

    public function getCategory(): string
    {
        return 'Approval & Tata Kelola (Governance)';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'entity_type' => [
                    'type' => 'string',
                    'enum' => ['SHIPMENT', 'INVOICE', 'VENDOR_BILL', 'CLIENT'],
                    'description' => 'Jenis entitas data yang akan dihapus / dibatalkan.',
                ],
                'entity_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nomor identitas dokumen yang akan dihapus.',
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Alasan penghapusan data untuk audit log keamanan.',
                ],
            ],
            'required' => ['entity_type', 'entity_id', 'reason'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $type = strtoupper((string) ($arguments['entity_type'] ?? ''));
        $id = trim((string) ($arguments['entity_id'] ?? ''));
        $reason = trim((string) ($arguments['reason'] ?? 'Permintaan penghapusan melalui MCP'));

        if (!$type || !$id) {
            return ['success' => false, 'error' => 'Parameter entity_type dan entity_id wajib diisi.'];
        }

        switch ($type) {
            case 'SHIPMENT':
                $shipment = Shipment::where('id', $id)->orWhere('project_no', $id)->first();
                if (!$shipment) {
                    return ['success' => false, 'error' => "Shipment '{$id}' tidak ditemukan."];
                }
                if ($shipment->status_tahap === 'SPPB_APPROVED' || $shipment->status_tahap === 'CLOSED') {
                    return ['success' => false, 'error' => "Shipment '{$shipment->project_no}' sudah memiliki status {$shipment->status_tahap} dan tidak boleh dihapus secara permanen."];
                }
                $no = $shipment->project_no;
                $shipment->delete();
                return [
                    'success' => true,
                    'message' => "Proyek pengapalan '{$no}' berhasil dihapus (soft delete).",
                    'reason' => $reason,
                ];

            case 'INVOICE':
                $invoice = ClientInvoice::where('id', $id)->orWhere('invoice_no', $id)->first();
                if (!$invoice) {
                    return ['success' => false, 'error' => "Invoice '{$id}' tidak ditemukan."];
                }
                if ($invoice->status === 'PAID') {
                    return ['success' => false, 'error' => "Invoice '{$invoice->invoice_no}' sudah lunas (PAID) dan tidak dapat dihapus."];
                }
                $no = $invoice->invoice_no;
                $invoice->delete();
                return [
                    'success' => true,
                    'message' => "Invoice '{$no}' berhasil dibatalkan dan dihapus.",
                    'reason' => $reason,
                ];

            case 'VENDOR_BILL':
                $bill = VendorBill::where('id', $id)->orWhere('ref_no', $id)->first();
                if (!$bill) {
                    return ['success' => false, 'error' => "Tagihan vendor '{$id}' tidak ditemukan."];
                }
                if ($bill->status === 'PAID') {
                    return ['success' => false, 'error' => "Tagihan vendor '{$bill->ref_no}' sudah berstatus PAID dan tidak dapat dihapus."];
                }
                $no = $bill->ref_no;
                $bill->delete();
                return [
                    'success' => true,
                    'message' => "Tagihan vendor '{$no}' berhasil dihapus.",
                    'reason' => $reason,
                ];

            case 'CLIENT':
                $client = Client::where('id', $id)->orWhere('code', $id)->first();
                if (!$client) {
                    return ['success' => false, 'error' => "Klien '{$id}' tidak ditemukan."];
                }
                $name = $client->name;
                $client->delete();
                return [
                    'success' => true,
                    'message' => "Data klien '{$name}' berhasil dinonaktifkan/dihapus.",
                    'reason' => $reason,
                ];

            default:
                return ['success' => false, 'error' => "Jenis entitas '{$type}' tidak didukung."];
        }
    }
}
