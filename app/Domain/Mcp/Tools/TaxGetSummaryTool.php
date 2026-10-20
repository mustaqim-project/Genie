<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\ClientInvoice;

class TaxGetSummaryTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'tax_get_summary';
    }

    public function getDescription(): string
    {
        return 'Rekapitulasi perpajakan pabean & freight forwarding: PPN Keluaran 11% jasa PPJK, estimasi PPh 23 withholding tax, dan pajak impor pabean (PPh 22 & PPN Impor).';
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
                'period' => [
                    'type' => 'string',
                    'enum' => ['this_month', 'this_quarter', 'this_year'],
                    'description' => 'Periode rekap pajak.',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $invoices = ClientInvoice::get();
        $totalPpnKeluaran = (float) $invoices->sum('ppn_jasa_idr');
        $totalJasa = (float) $invoices->sum('jasa_ppjk_idr');
        $estimasiPph23DipungutKlien = round($totalJasa * 0.02); // 2% jasa handling freight
        $totalTalanganPabean = (float) $invoices->sum('talangan_pabean_idr');

        return [
            'success' => true,
            'summary_period' => $arguments['period'] ?? 'this_month',
            'pajak_perusahaan' => [
                'total_dpp_jasa_ppjk_idr' => $totalJasa,
                'total_ppn_keluaran_11_idr' => $totalPpnKeluaran,
                'estimasi_pph_23_potong_klien_2_idr' => $estimasiPph23DipungutKlien,
            ],
            'dana_talangan_pabean_reimburse' => [
                'total_pajak_pabean_cukai_idr' => $totalTalanganPabean,
                'keterangan' => 'Reimbursement murni (non-objek PPN perusahaan freight forwarding)',
            ],
        ];
    }
}
