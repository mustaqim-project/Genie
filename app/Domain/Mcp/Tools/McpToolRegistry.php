<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use InvalidArgumentException;

class McpToolRegistry
{
    /**
     * @var array<string, McpToolInterface>
     */
    protected array $tools = [];

    /**
     * @var array<string, string>
     */
    protected array $aliases = [
        'parse_packing_list' => 'customs_parse_packing_list',
        'check_hs_code' => 'customs_check_hs_code',
        'detect_shipment_customs' => 'customs_detect_shipment',
        'generate_customs_declaration' => 'customs_generate_declaration',
        'detect_shipment' => 'customs_detect_shipment',
        'generate_declaration' => 'customs_generate_declaration',
    ];

    public function __construct()
    {
        // 1. Kepabeanan & CEISA 4.0
        $this->register(new CustomsParsePackingListTool());
        $this->register(new CustomsCheckHsCodeTool());
        $this->register(new CustomsDetectShipmentTool());
        $this->register(new CustomsGenerateDeclarationTool());
        $this->register(new CustomsGetKursPajakTool());
        $this->register(new CustomsCheckCeisaStatusTool());

        // 2. Operasional Pengapalan (P01)
        $this->register(new ShipmentListProjectsTool());
        $this->register(new ShipmentGetDetailTool());
        $this->register(new ShipmentCreateProjectTool());
        $this->register(new ShipmentUpdateStatusTool());

        // 3. DO & Pelayaran
        $this->register(new DoManageWorkflowTool());

        // 4. Traffic & Truk Lapangan
        $this->register(new TrafficTrackContainersTool());
        $this->register(new TrafficAssignFleetTool());

        // 5. Keuangan, Piutang & Hutang
        $this->register(new FinanceGetSummaryTool());
        $this->register(new FinanceManageInvoiceTool());
        $this->register(new FinanceManageVendorBillTool());
        $this->register(new FinanceGetCashBankBalancesTool());
        $this->register(new FinanceRecordCashMutationTool());

        // 6. Akuntansi & Pajak
        $this->register(new AccountingGetProfitLossTool());
        $this->register(new TaxGetSummaryTool());

        // 7. CRM & Klien
        $this->register(new CrmSearchClientsTool());
        $this->register(new CrmSendWhatsappTool());

        // 8. SDM & Kepegawaian (HRM)
        $this->register(new HrmGetAttendanceSummaryTool());
        $this->register(new HrmGetPayrollSummaryTool());

        // 9. Approval, Tata Kelola & CRUD Actions
        $this->register(new McpApproveActionTool());
        $this->register(new McpListPendingApprovalsTool());
        $this->register(new McpDeleteRecordTool());
    }

    public function register(McpToolInterface $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    public function hasTool(string $name): bool
    {
        $resolved = $this->aliases[$name] ?? $name;
        return isset($this->tools[$resolved]);
    }

    public function getTool(string $name): McpToolInterface
    {
        $resolved = $this->aliases[$name] ?? $name;
        if (!isset($this->tools[$resolved])) {
            throw new InvalidArgumentException("Tool MCP '{$name}' tidak ditemukan dalam katalog sistem GENIE.");
        }

        return $this->tools[$resolved];
    }

    /**
     * @return array<string, McpToolInterface>
     */
    public function getAllTools(): array
    {
        return $this->tools;
    }

    /**
     * Format skema seluruh tools sesuai spesifikasi Anthropic MCP (2024-11-05).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFormattedTools(): array
    {
        $formatted = [];
        foreach ($this->tools as $tool) {
            $formatted[] = [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'category' => $tool->getCategory(),
                'inputSchema' => $tool->getInputSchema(),
            ];
        }

        // Include legacy aliases for backward compatibility in list
        foreach ($this->aliases as $alias => $target) {
            if (isset($this->tools[$target])) {
                $targetTool = $this->tools[$target];
                $formatted[] = [
                    'name' => $alias,
                    'description' => "[Legacy Alias] " . $targetTool->getDescription(),
                    'category' => $targetTool->getCategory(),
                    'inputSchema' => $targetTool->getInputSchema(),
                ];
            }
        }

        return $formatted;
    }

    /**
     * Kelompokkan tools berdasarkan kategori fitur.
     */
    public function getToolsGroupedByCategory(): array
    {
        $grouped = [];
        foreach ($this->tools as $tool) {
            $cat = $tool->getCategory();
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }
            $grouped[$cat][] = $tool;
        }
        return $grouped;
    }
}
