<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Services\Shipment\DocumentExtractionService;

class CustomsParsePackingListTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'customs_parse_packing_list';
    }

    public function getDescription(): string
    {
        return 'Mengekstrak berkas Packing List (teks mentah atau base64). Mengklasifikasikan pos tarif HS Code BTKI 2022 tiap barang secara otomatis, memeriksa kepatuhan perizinan Lartas, dan mendeteksi kebutuhan dokumen perizinan Bea Cukai.';
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
                'raw_text' => [
                    'type' => 'string',
                    'description' => 'Teks mentah hasil OCR atau isi dokumen packing list.',
                ],
                'direction' => [
                    'type' => 'string',
                    'enum' => ['IMPORT', 'EXPORT', 'IMPORT_BC20', 'EKSPORT_BC30'],
                    'description' => 'Arah pengapalan (impor PIB BC 2.0 atau ekspor PEB BC 3.0).',
                ],
                'file_base64' => [
                    'type' => 'string',
                    'description' => 'Konten berkas dokumen dalam format base64.',
                ],
                'file_name' => [
                    'type' => 'string',
                    'description' => 'Nama file dokumen (contoh: PACKING_LIST_SAMARINDA.pdf).',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $extractionService = app(DocumentExtractionService::class);
        $rawText = $arguments['raw_text'] ?? null;
        $direction = $arguments['direction'] ?? 'IMPORT_BC20';

        if (!empty($arguments['file_base64'])) {
            $base64 = $arguments['file_base64'];
            $fileName = $arguments['file_name'] ?? ('upload_' . time() . '.pdf');
            $tmpPath = sys_get_temp_dir() . '/' . uniqid('mcp_pl_') . '_' . $fileName;
            file_put_contents($tmpPath, base64_decode($base64));

            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tmpPath,
                $fileName,
                mime_content_type($tmpPath) ?: 'application/octet-stream',
                null,
                true
            );

            $result = $extractionService->parsePackingListContent($uploadedFile, $direction);
            @unlink($tmpPath);
            return $result;
        }

        if (empty($rawText)) {
            return [
                'success' => false,
                'error' => 'Parameter raw_text atau file_base64 wajib diisi.',
            ];
        }

        return $extractionService->parsePackingListText($rawText, $direction);
    }
}
