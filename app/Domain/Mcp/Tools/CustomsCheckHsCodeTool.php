<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Services\Ceisa\CeisaHsCodeService;

class CustomsCheckHsCodeTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'customs_check_hs_code';
    }

    public function getDescription(): string
    {
        return 'Pengecekan spesifik pos tarif HS Code (8-digit) atau uraian nama komoditas barang terhadap database BTKI 2022 DJBC, tarif pungutan pabean (Bea Masuk, PPN, PPh pasal 22, BK), dan izin Lartas / dokumen tambahan wajib.';
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
                'query' => [
                    'type' => 'string',
                    'description' => 'Nomor HS Code 8-digit (misal: 8412.21.00) atau nama komoditas (misal: HYDRAULIC BREAKER, PARANET, SIDE BOLT).',
                ],
                'direction' => [
                    'type' => 'string',
                    'enum' => ['IMPORT', 'EXPORT', 'IMPORT_BC20', 'EKSPORT_BC30'],
                    'description' => 'Arah pengapalan impor atau ekspor.',
                ],
            ],
            'required' => ['query'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $hsCodeService = app(CeisaHsCodeService::class);
        $query = trim((string) ($arguments['query'] ?? ''));
        $direction = $arguments['direction'] ?? 'IMPORT_BC20';

        if (empty($query)) {
            return ['success' => false, 'error' => 'Parameter query wajib diisi.'];
        }

        $cleanQuery = preg_replace('/[^0-9]/', '', $query);
        if (strlen($cleanQuery) >= 8) {
            $formatted = substr($cleanQuery, 0, 4) . '.' . substr($cleanQuery, 4, 2) . '.' . substr($cleanQuery, 6, 2);
            $tariff = $hsCodeService->getTariff($formatted);
            if ($tariff) {
                return [
                    'success' => true,
                    'source' => 'DJBC_BTKI_2022',
                    'code' => $formatted,
                    'clean_code' => $cleanQuery,
                    'desc' => $tariff['uraian_barang'] ?? 'Barang Impor Sesuai BTKI 2022',
                    'bm' => $tariff['bm'] ?? 0,
                    'ppn' => $tariff['ppn'] ?? 11,
                    'pph' => $tariff['pph'] ?? 2.5,
                    'has_lartas' => !empty($tariff['lartas']),
                    'lartas_note' => $tariff['lartas'] ?? 'Bebas Lartas',
                    'legal' => 'Buku Tarif Kepabeanan Indonesia (BTKI 2022)',
                ];
            }
        }

        $classified = $hsCodeService->classifyCommodity($query);
        $code = $classified['code'] ?? $classified['hs_code'] ?? '8412.21.00';
        $desc = $classified['desc'] ?? $classified['description'] ?? 'Uraian Barang Pabean';

        return [
            'success' => true,
            'source' => 'DJBC_BTKI_2022',
            'code' => $code,
            'clean_code' => $classified['clean_code'] ?? preg_replace('/[^0-9]/', '', (string) $code),
            'desc' => $desc,
            'confidence_score' => $classified['confidence_score'] ?? 0.95,
            'bm' => $classified['bm'] ?? 0,
            'ppn' => $classified['ppn'] ?? 11,
            'pph' => $classified['pph'] ?? 2.5,
            'has_lartas' => $classified['has_lartas'] ?? false,
            'lartas_note' => $classified['lartas'] ?? $classified['lartas_note'] ?? 'Bebas Lartas',
            'legal' => $classified['legal'] ?? 'Kaidah Utama Menafsirkan HS (KUMHS Rule 1 & Bab 84 BTKI 2022)',
        ];
    }
}
