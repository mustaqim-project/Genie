<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

interface McpToolInterface
{
    /**
     * Nama unik tool MCP (e.g. customs_check_hs_code, finance_manage_invoice).
     */
    public function getName(): string;

    /**
     * Deskripsi detail tujuan dan kemampuan tool untuk model AI.
     */
    public function getDescription(): string;

    /**
     * Skema JSON input parameter tool (JSON Schema 2024-11-05).
     *
     * @return array<string, mixed>
     */
    public function getInputSchema(): array;

    /**
     * Kategori modul tool (Pabean, Shipment, DO, Traffic, Finance, Akuntansi, CRM, HRM).
     */
    public function getCategory(): string;

    /**
     * Eksekusi tool dengan argumen yang diberikan dan kembalikan struktur data hasil.
     *
     * @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    public function execute(array $arguments = []): array;
}
