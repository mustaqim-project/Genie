<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Shipment;
use App\Services\Shipment\ShipmentWorkflowService;
use Illuminate\Http\Request;

class CustomsDetectShipmentTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'customs_detect_shipment';
    }

    public function getDescription(): string
    {
        return 'Memindai packing list pada proyek pengapalan tertentu di database GENIE, mengklasifikasikan HS code seluruh item komoditas, dan menyinkronkan slot tracking izin pabean/Lartas.';
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
                    'description' => 'UUID atau nomor proyek pengapalan di GENIE ERP.',
                ],
                'raw_text' => [
                    'type' => 'string',
                    'description' => 'Teks mentah packing list baru jika ingin menimpa dokumen yang ada.',
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

        $workflowService = app(ShipmentWorkflowService::class);
        $req = new Request($arguments);
        $result = $workflowService->detectPackingListCustoms($shipment->id, $req);

        return $result;
    }
}
