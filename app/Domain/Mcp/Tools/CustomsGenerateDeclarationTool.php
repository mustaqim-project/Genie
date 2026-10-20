<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Shipment;
use App\Services\Shipment\ShipmentWorkflowService;

class CustomsGenerateDeclarationTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'customs_generate_declaration';
    }

    public function getDescription(): string
    {
        return 'Menghasilkan rancangan draft dokumen pabean resmi BC 2.0 (PIB Impor) atau BC 3.0 (PEB Ekspor) siap impor ke CEISA 4.0 beserta rincian lembar lanjutan barang.';
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
                    'description' => 'UUID atau nomor proyek pengapalan.',
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['BC20', 'BC30'],
                    'description' => 'Tipe deklarasi (BC20 = PIB Impor, BC30 = PEB Ekspor).',
                ],
            ],
            'required' => ['shipment_id'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $shipmentId = $arguments['shipment_id'] ?? null;
        if (!$shipmentId) {
            return ['success' => false, 'error' => 'Parameter shipment_id wajib diisi.'];
        }

        $shipment = Shipment::where('id', $shipmentId)->orWhere('project_no', $shipmentId)->first();
        if (!$shipment) {
            return ['success' => false, 'error' => "Shipment '{$shipmentId}' tidak ditemukan."];
        }

        $type = $arguments['type'] ?? ($shipment->jenis_layanan === 'EXPORT' ? 'BC30' : 'BC20');
        $workflowService = app(ShipmentWorkflowService::class);
        $result = $workflowService->generateDeclarationDraft($shipment->id, $type);

        return $result;
    }
}
