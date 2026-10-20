<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Client;
use App\Models\ClientInvoice;
use App\Models\Shipment;

class FinanceManageInvoiceTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'finance_manage_invoice';
    }

    public function getDescription(): string
    {
        return 'Mengelola tagihan invoice klien (P19): mencari invoice, melihat rincian biaya jasa PPJK & reimburse dana talangan pabean, membuat draft invoice baru, atau mencatat pelunasan tagihan.';
    }

    public function getCategory(): string
    {
        return 'Keuangan & Akuntansi';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'get', 'create', 'mark_paid'],
                    'description' => 'Aksi yang dilakukan: list (daftar invoice), get (rincian), create (buat invoice), mark_paid (pelunasan).',
                ],
                'invoice_no' => [
                    'type' => 'string',
                    'description' => 'Nomor invoice (misal: INV-202610-001).',
                ],
                'shipment_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nomor proyek pengapalan untuk pembuatan invoice.',
                ],
                'jasa_ppjk_idr' => [
                    'type' => 'number',
                    'description' => 'Komponen biaya jasa kepabeanan / handling PPJK (IDR).',
                ],
                'talangan_pabean_idr' => [
                    'type' => 'number',
                    'description' => 'Komponen dana talangan bea masuk / pajak pabean yang di-reimburse (IDR).',
                ],
                'biaya_trucking_idr' => [
                    'type' => 'number',
                    'description' => 'Biaya angkutan trucking (IDR).',
                ],
                'payment_notes' => [
                    'type' => 'string',
                    'description' => 'Catatan rekening bank atau tanggal transfer pelunasan.',
                ],
            ],
            'required' => ['action'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $action = $arguments['action'] ?? 'list';

        if ($action === 'list') {
            $invoices = ClientInvoice::with('client')->latest()->take(15)->get();
            return [
                'success' => true,
                'total' => $invoices->count(),
                'invoices' => $invoices->map(fn($inv) => [
                    'id' => $inv->id,
                    'invoice_no' => $inv->invoice_no,
                    'client_name' => $inv->client?->name ?? 'Klien',
                    'tanggal' => $inv->tanggal?->format('Y-m-d'),
                    'jatuh_tempo' => $inv->jatuh_tempo?->format('Y-m-d'),
                    'total_tagihan_idr' => (float) $inv->total_tagihan_idr,
                    'status' => $inv->status,
                ])->toArray(),
            ];
        }

        if ($action === 'get') {
            $invNo = $arguments['invoice_no'] ?? null;
            $invoice = ClientInvoice::with(['client', 'shipment', 'lines'])
                ->where('invoice_no', $invNo)
                ->orWhere('id', $invNo)
                ->first();

            if (!$invoice) {
                return ['success' => false, 'error' => "Invoice '{$invNo}' tidak ditemukan."];
            }

            return [
                'success' => true,
                'invoice' => [
                    'invoice_no' => $invoice->invoice_no,
                    'client' => $invoice->client?->name,
                    'project_no' => $invoice->shipment?->project_no,
                    'tanggal' => $invoice->tanggal?->format('Y-m-d'),
                    'jatuh_tempo' => $invoice->jatuh_tempo?->format('Y-m-d'),
                    'rincian' => [
                        'talangan_pabean_idr' => (float) $invoice->talangan_pabean_idr,
                        'biaya_pelayaran_idr' => (float) $invoice->biaya_pelayaran_idr,
                        'jasa_ppjk_idr' => (float) $invoice->jasa_ppjk_idr,
                        'biaya_trucking_idr' => (float) $invoice->biaya_trucking_idr,
                        'subtotal_idr' => (float) $invoice->subtotal_idr,
                        'ppn_jasa_idr' => (float) $invoice->ppn_jasa_idr,
                        'total_tagihan_idr' => (float) $invoice->total_tagihan_idr,
                    ],
                    'status' => $invoice->status,
                    'paid_at' => $invoice->paid_at?->toDateTimeString(),
                ],
            ];
        }

        if ($action === 'mark_paid') {
            $invNo = $arguments['invoice_no'] ?? null;
            $invoice = ClientInvoice::where('invoice_no', $invNo)->orWhere('id', $invNo)->first();
            if (!$invoice) {
                return ['success' => false, 'error' => "Invoice '{$invNo}' tidak ditemukan."];
            }

            $invoice->update([
                'status' => 'PAID',
                'paid_at' => now(),
                'catatan_pembayaran' => $arguments['payment_notes'] ?? 'Lunas via transfer bank',
            ]);

            return [
                'success' => true,
                'message' => "Invoice '{$invoice->invoice_no}' telah ditandai LUNAS.",
                'invoice_no' => $invoice->invoice_no,
                'status' => 'PAID',
                'paid_at' => now()->toDateTimeString(),
            ];
        }

        if ($action === 'create') {
            $shipmentId = $arguments['shipment_id'] ?? null;
            $shipment = Shipment::where('id', $shipmentId)->orWhere('project_no', $shipmentId)->first();
            if (!$shipment) {
                return ['success' => false, 'error' => "Shipment '{$shipmentId}' tidak ditemukan."];
            }

            $jasaPpjk = (float) ($arguments['jasa_ppjk_idr'] ?? 1500000);
            $talangan = (float) ($arguments['talangan_pabean_idr'] ?? 0);
            $trucking = (float) ($arguments['biaya_trucking_idr'] ?? 2500000);
            $subtotal = $jasaPpjk + $talangan + $trucking;
            $ppnJasa = round($jasaPpjk * 0.11);
            $total = $subtotal + $ppnJasa;

            $invNo = 'INV-' . date('Ym') . '-' . str_pad((string) (ClientInvoice::count() + 1), 4, '0', STR_PAD_LEFT);

            $invoice = ClientInvoice::create([
                'invoice_no' => $invNo,
                'shipment_id' => $shipment->id,
                'client_id' => $shipment->client_id,
                'tanggal' => now(),
                'jatuh_tempo' => now()->addDays(14),
                'jasa_ppjk_idr' => $jasaPpjk,
                'talangan_pabean_idr' => $talangan,
                'biaya_trucking_idr' => $trucking,
                'subtotal_idr' => $subtotal,
                'ppn_jasa_idr' => $ppnJasa,
                'total_tagihan_idr' => $total,
                'status' => 'UNPAID',
            ]);

            return [
                'success' => true,
                'message' => "Draft invoice '{$invNo}' berhasil dibuat.",
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'total_tagihan_idr' => $total,
                    'jatuh_tempo' => $invoice->jatuh_tempo->format('Y-m-d'),
                ],
            ];
        }

        return ['success' => false, 'error' => "Aksi '{$action}' tidak dikenal."];
    }
}
