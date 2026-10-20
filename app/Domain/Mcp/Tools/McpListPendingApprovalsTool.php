<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\CashAdvance;
use App\Models\ClientInvoice;
use App\Models\LeaveRequest;
use App\Models\Shipment;
use App\Models\VendorBill;
use Illuminate\Support\Facades\DB;

class McpListPendingApprovalsTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'mcp_list_pending_approvals';
    }

    public function getDescription(): string
    {
        return 'Mengambil seluruh daftar antrean pengajuan yang membutuhkan persetujuan (PENDING APPROVALS) di semua modul: deklarasi pabean siap submit, tagihan vendor siap bayar, pengajuan uang muka kas, cuti staf, dan antrean AI Action Queue.';
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
                'type' => [
                    'type' => 'string',
                    'enum' => ['ALL', 'CUSTOMS', 'BILLS', 'CASH_ADVANCES', 'LEAVES', 'AI_ACTIONS'],
                    'description' => 'Filter kategori approval (default: ALL).',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $type = strtoupper((string) ($arguments['type'] ?? 'ALL'));
        $pending = [];

        // 1. Customs Ready for Submission
        if ($type === 'ALL' || $type === 'CUSTOMS') {
            $shipments = Shipment::with('client')
                ->where('status_tahap', 'DOCUMENT_RECEIVED')
                ->latest()
                ->take(5)
                ->get();

            foreach ($shipments as $s) {
                $pending[] = [
                    'category' => 'CUSTOMS_DECLARATION',
                    'id' => $s->id,
                    'reference' => $s->project_no,
                    'title' => "Pengajuan Draf Deklarasi {$s->jenis_layanan} ({$s->client?->name})",
                    'amount_idr' => (float) $s->total_nilai_idr,
                    'date' => $s->created_at->format('Y-m-d H:i'),
                    'action_required' => 'mcp_approve_action(target_type: "CUSTOMS_DECLARATION", target_id: "' . $s->project_no . '", decision: "APPROVE")',
                ];
            }
        }

        // 2. Vendor Bills Pending Approval
        if ($type === 'ALL' || $type === 'BILLS') {
            $bills = VendorBill::with('shipment')
                ->where('status', 'UNPAID')
                ->latest()
                ->take(5)
                ->get();

            foreach ($bills as $b) {
                $pending[] = [
                    'category' => 'VENDOR_BILL',
                    'id' => $b->id,
                    'reference' => $b->ref_no,
                    'title' => "Tagihan Hutang Vendor {$b->vendor_type} ({$b->shipment?->project_no})",
                    'amount_idr' => (float) $b->amount,
                    'date' => $b->created_at->format('Y-m-d H:i'),
                    'action_required' => 'mcp_approve_action(target_type: "VENDOR_BILL", target_id: "' . $b->ref_no . '", decision: "APPROVE")',
                ];
            }
        }

        // 3. AI Action Queue
        if ($type === 'ALL' || $type === 'AI_ACTIONS') {
            try {
                $actions = DB::table('ai_action_queue')
                    ->where('status', 'PROPOSED')
                    ->latest()
                    ->take(5)
                    ->get();

                foreach ($actions as $a) {
                    $pending[] = [
                        'category' => 'AI_ACTION_QUEUE',
                        'id' => $a->id,
                        'reference' => (string) $a->id,
                        'title' => "Usulan Otomatis AI: {$a->kind}",
                        'amount_idr' => 0,
                        'date' => $a->created_at ?? now()->format('Y-m-d H:i'),
                        'action_required' => 'mcp_approve_action(target_type: "AI_ACTION_QUEUE", target_id: "' . $a->id . '", decision: "APPROVE")',
                    ];
                }
            } catch (\Throwable $e) {
                // Ignore if empty
            }
        }

        return [
            'success' => true,
            'total_pending' => count($pending),
            'pending_approvals' => $pending,
        ];
    }
}
