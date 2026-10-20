<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Shipment;
use App\Models\VendorBill;

class FinanceManageVendorBillTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'finance_manage_vendor_bill';
    }

    public function getDescription(): string
    {
        return 'Mengelola tagihan hutang vendor operasional (AP) seperti pelayaran (Ocean Freight/THC), terminal/depo TPS (Storage/Lift On-Off), dan vendor angkutan trucking kontainer.';
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
                    'enum' => ['list', 'create', 'mark_paid'],
                    'description' => 'Aksi: list (daftar hutang vendor), create (catat tagihan vendor baru), mark_paid (pelunasan hutang).',
                ],
                'bill_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau ref_no tagihan vendor.',
                ],
                'vendor_type' => [
                    'type' => 'string',
                    'enum' => ['SHIPPING_LINE', 'PORT_TERMINAL', 'TRUCKING', 'DEPO_TPS', 'OTHER'],
                    'description' => 'Jenis vendor rekanan.',
                ],
                'ref_no' => [
                    'type' => 'string',
                    'description' => 'Nomor tagihan / invoice resmi dari vendor.',
                ],
                'shipment_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nomor proyek pengapalan terkait.',
                ],
                'amount' => [
                    'type' => 'number',
                    'description' => 'Nominal tagihan dalam IDR.',
                ],
                'due_date' => [
                    'type' => 'string',
                    'description' => 'Tanggal jatuh tempo tagihan (YYYY-MM-DD).',
                ],
            ],
            'required' => ['action'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $action = $arguments['action'] ?? 'list';

        if ($action === 'list') {
            $bills = VendorBill::with('shipment')->latest()->take(15)->get();
            return [
                'success' => true,
                'total' => $bills->count(),
                'bills' => $bills->map(fn($b) => [
                    'id' => $b->id,
                    'vendor_type' => $b->vendor_type,
                    'ref_no' => $b->ref_no,
                    'project_no' => $b->shipment?->project_no,
                    'amount' => (float) $b->amount,
                    'due_date' => $b->due_date?->format('Y-m-d'),
                    'status' => $b->status,
                ])->toArray(),
            ];
        }

        if ($action === 'create') {
            $shipmentId = $arguments['shipment_id'] ?? null;
            if ($shipmentId) {
                $shipment = Shipment::where('id', $shipmentId)->orWhere('project_no', $shipmentId)->first();
                $shipmentId = $shipment?->id;
            }

            $bill = VendorBill::create([
                'vendor_type' => $arguments['vendor_type'] ?? 'SHIPPING_LINE',
                'shipment_id' => $shipmentId,
                'ref_no' => $arguments['ref_no'] ?? ('VB-' . time()),
                'amount' => (float) ($arguments['amount'] ?? 0),
                'due_date' => !empty($arguments['due_date']) ? $arguments['due_date'] : now()->addDays(7)->format('Y-m-d'),
                'status' => 'UNPAID',
            ]);

            return [
                'success' => true,
                'message' => "Tagihan vendor '{$bill->ref_no}' berhasil dicatat.",
                'bill' => [
                    'id' => $bill->id,
                    'ref_no' => $bill->ref_no,
                    'amount' => (float) $bill->amount,
                    'due_date' => $bill->due_date?->format('Y-m-d'),
                ],
            ];
        }

        if ($action === 'mark_paid') {
            $billId = $arguments['bill_id'] ?? null;
            $bill = VendorBill::where('id', $billId)->orWhere('ref_no', $billId)->first();
            if (!$bill) {
                return ['success' => false, 'error' => "Tagihan vendor '{$billId}' tidak ditemukan."];
            }

            $bill->update(['status' => 'PAID']);
            return [
                'success' => true,
                'message' => "Tagihan vendor '{$bill->ref_no}' telah ditandai LUNAS.",
                'ref_no' => $bill->ref_no,
                'status' => 'PAID',
            ];
        }

        return ['success' => false, 'error' => "Aksi '{$action}' tidak dikenal."];
    }
}
