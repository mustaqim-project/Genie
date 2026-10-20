<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Shipment;

class DoManageWorkflowTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'do_manage_workflow';
    }

    public function getDescription(): string
    {
        return 'Mengelola alur Delivery Order (DO) pelayaran: cek masa free-time demurrage/detention, status pembayaran invoice pelayaran, dan mencatat nomor DO rilis atau penukaran SP2 terminal.';
    }

    public function getCategory(): string
    {
        return 'DO & Pelayaran';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'shipment_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nomor proyek pengapalan.',
                ],
                'action' => [
                    'type' => 'string',
                    'enum' => ['check_status', 'request_do', 'mark_paid', 'release_do'],
                    'description' => 'Tindakan: check_status (cek status), request_do (ajukan permohonan), mark_paid (catat bayar pelayaran), release_do (rilis DO).',
                ],
                'nomor_do' => [
                    'type' => 'string',
                    'description' => 'Nomor DO resmi yang diterbitkan pelayaran (wajib untuk action release_do).',
                ],
                'do_expired_at' => [
                    'type' => 'string',
                    'description' => 'Tanggal kedaluwarsa DO (format YYYY-MM-DD).',
                ],
            ],
            'required' => ['shipment_id'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $id = $arguments['shipment_id'] ?? null;
        $action = $arguments['action'] ?? 'check_status';

        $shipment = Shipment::where('id', $id)->orWhere('project_no', $id)->first();
        if (!$shipment) {
            return ['success' => false, 'error' => "Shipment '{$id}' tidak ditemukan."];
        }

        if ($action === 'release_do') {
            $nomorDo = $arguments['nomor_do'] ?? ('DO-' . strtoupper(substr(md5((string) time()), 0, 8)));
            $expiredAt = !empty($arguments['do_expired_at']) ? $arguments['do_expired_at'] : now()->addDays(5)->format('Y-m-d');

            $shipment->update([
                'status_do' => 'RELEASED',
                'nomor_do' => $nomorDo,
                'do_expired_at' => $expiredAt,
            ]);

            return [
                'success' => true,
                'message' => "Delivery Order (DO) untuk '{$shipment->project_no}' berhasil dirilis dengan nomor '{$nomorDo}'.",
                'nomor_do' => $nomorDo,
                'status_do' => 'RELEASED',
                'do_expired_at' => $expiredAt,
            ];
        }

        if ($action === 'mark_paid') {
            $shipment->update(['status_do' => 'PAID']);
            return [
                'success' => true,
                'message' => "Biaya pelayaran DO untuk '{$shipment->project_no}' telah ditandai LUNAS.",
                'status_do' => 'PAID',
            ];
        }

        if ($action === 'request_do') {
            $shipment->update(['status_do' => 'REQUESTED']);
            return [
                'success' => true,
                'message' => "Permohonan DO untuk '{$shipment->project_no}' telah diajukan ke pelayaran.",
                'status_do' => 'REQUESTED',
            ];
        }

        // Action check_status
        return [
            'success' => true,
            'shipment_no' => $shipment->project_no,
            'pelayaran' => $shipment->shippingVendor?->name ?? 'Pelayaran',
            'nomor_bl' => $shipment->nomor_bl_awb,
            'status_do' => $shipment->status_do ?? 'PENDING',
            'nomor_do' => $shipment->nomor_do ?? 'Belum Rilis',
            'do_expired_at' => $shipment->do_expired_at?->format('Y-m-d') ?? '-',
            'free_time_days' => $shipment->free_time_days ?? 7,
            'free_time_expired_at' => $shipment->free_time_expired_at?->format('Y-m-d') ?? '-',
            'is_expired' => $shipment->do_expired_at ? $shipment->do_expired_at->isPast() : false,
        ];
    }
}
