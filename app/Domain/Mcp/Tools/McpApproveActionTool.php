<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Approval;
use App\Models\CashAdvance;
use App\Models\ClientInvoice;
use App\Models\LeaveRequest;
use App\Models\Shipment;
use App\Models\VendorBill;

class McpApproveActionTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'mcp_approve_action';
    }

    public function getDescription(): string
    {
        return 'Menjalankan persetujuan (APPROVAL) atau penolakan (REJECT) berwenang terhadap transaksi atau dokumen: draf deklarasi pabean CEISA (PIB/PEB), tagihan hutang vendor (AP), diskon/pembatalan invoice, uang muka operasional, atau pengajuan cuti.';
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
                'target_type' => [
                    'type' => 'string',
                    'enum' => [
                        'CUSTOMS_DECLARATION',
                        'VENDOR_BILL',
                        'CLIENT_INVOICE',
                        'CASH_ADVANCE',
                        'LEAVE_REQUEST',
                        'AI_ACTION_QUEUE',
                    ],
                    'description' => 'Jenis entitas yang diajukan persetujuan.',
                ],
                'target_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nomor referensi dokumen/transaksi (misal: SHP-202610-0011, INV-202610-0001, atau VB-1729482).',
                ],
                'decision' => [
                    'type' => 'string',
                    'enum' => ['APPROVE', 'REJECT'],
                    'description' => 'Keputusan: APPROVE (Disetujui) atau REJECT (Ditolak).',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Catatan persetujuan atau alasan penolakan.',
                ],
            ],
            'required' => ['target_type', 'target_id', 'decision'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $type = strtoupper(trim((string) ($arguments['target_type'] ?? '')));
        $targetId = trim((string) ($arguments['target_id'] ?? ''));
        $decision = strtoupper(trim((string) ($arguments['decision'] ?? 'APPROVE')));
        $notes = trim((string) ($arguments['notes'] ?? ($decision === 'APPROVE' ? 'Approved by AI Authorized Agent via MCP' : 'Rejected via MCP')));

        if (!$type || !$targetId) {
            return ['success' => false, 'error' => 'Parameter target_type dan target_id wajib diisi.'];
        }

        switch ($type) {
            case 'CUSTOMS_DECLARATION':
                $shipment = Shipment::where('id', $targetId)->orWhere('project_no', $targetId)->first();
                if (!$shipment) {
                    return ['success' => false, 'error' => "Shipment '{$targetId}' tidak ditemukan."];
                }
                $newStatus = ($decision === 'APPROVE') ? 'EDI_SUBMITTED' : 'DOCUMENT_RECEIVED';
                $shipment->update([
                    'status_tahap' => $newStatus,
                    'catatan' => $notes,
                ]);
                return [
                    'success' => true,
                    'message' => "Deklarasi pabean untuk '{$shipment->project_no}' berhasil diproses: {$decision}.",
                    'decision' => $decision,
                    'shipment_no' => $shipment->project_no,
                    'status_tahap' => $newStatus,
                    'notes' => $notes,
                ];

            case 'VENDOR_BILL':
                $bill = VendorBill::where('id', $targetId)->orWhere('ref_no', $targetId)->first();
                if (!$bill) {
                    return ['success' => false, 'error' => "Tagihan vendor '{$targetId}' tidak ditemukan."];
                }
                $newStatus = ($decision === 'APPROVE') ? 'APPROVED_FOR_PAYMENT' : 'REJECTED';
                $bill->update([
                    'status' => $newStatus,
                    'approved_by_id' => auth()->id() ?? \App\Models\User::first()?->id,
                ]);
                return [
                    'success' => true,
                    'message' => "Tagihan vendor '{$bill->ref_no}' berhasil {$decision}.",
                    'decision' => $decision,
                    'ref_no' => $bill->ref_no,
                    'status' => $newStatus,
                ];

            case 'CLIENT_INVOICE':
                $invoice = ClientInvoice::where('id', $targetId)->orWhere('invoice_no', $targetId)->first();
                if (!$invoice) {
                    return ['success' => false, 'error' => "Invoice '{$targetId}' tidak ditemukan."];
                }
                $invoice->update([
                    'catatan_pembayaran' => ($invoice->catatan_pembayaran ? $invoice->catatan_pembayaran . ' | ' : '') . "[{$decision}]: {$notes}",
                ]);
                return [
                    'success' => true,
                    'message' => "Invoice klien '{$invoice->invoice_no}' status persetujuan berhasil dicatat: {$decision}.",
                    'invoice_no' => $invoice->invoice_no,
                    'decision' => $decision,
                ];

            case 'CASH_ADVANCE':
                $advance = CashAdvance::where('id', $targetId)->first();
                if ($advance) {
                    $advance->update([
                        'status' => ($decision === 'APPROVE') ? 'APPROVED' : 'REJECTED',
                    ]);
                }
                return [
                    'success' => true,
                    'message' => "Uang muka operasional '{$targetId}' berhasil {$decision}.",
                    'decision' => $decision,
                ];

            case 'LEAVE_REQUEST':
                $leave = LeaveRequest::where('id', $targetId)->first();
                if ($leave) {
                    $leave->update([
                        'status' => ($decision === 'APPROVE') ? 'APPROVED' : 'REJECTED',
                    ]);
                }
                return [
                    'success' => true,
                    'message' => "Pengajuan cuti '{$targetId}' berhasil {$decision}.",
                    'decision' => $decision,
                ];

            case 'AI_ACTION_QUEUE':
                $queue = \Illuminate\Support\Facades\DB::table('ai_action_queue')->where('id', $targetId)->first();
                if ($queue) {
                    \Illuminate\Support\Facades\DB::table('ai_action_queue')->where('id', $targetId)->update([
                        'status' => ($decision === 'APPROVE') ? 'APPROVED' : 'REJECTED',
                        'reason' => $notes,
                        'executed_at' => ($decision === 'APPROVE') ? now() : null,
                    ]);
                }
                return [
                    'success' => true,
                    'message' => "Proposal usulan AI '{$targetId}' berhasil {$decision}.",
                    'decision' => $decision,
                ];

            default:
                return ['success' => false, 'error' => "Tipe persetujuan '{$type}' tidak didukung."];
        }
    }
}
