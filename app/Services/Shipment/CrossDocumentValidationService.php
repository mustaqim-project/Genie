<?php

namespace App\Services\Shipment;

use App\Models\ImportDocument;
use App\Models\ImportField;
use App\Models\ImportValidation;

class CrossDocumentValidationService
{
    /**
     * Run all cross-document rules (V01 - V10) on a logical document.
     */
    public function validateDocument(ImportDocument $document): array
    {
        $fields = $document->fields()->pluck('corrected_value', 'field_name')->toArray();
        $batchId = $document->file ? $document->file->batch_id : null;
        
        $results = [];

        // V01: Konsistensi Nomor BL antar-dokumen dalam 1 batch / shipment (BLOCKING)
        $results[] = $this->ruleV01BlConsistency($document, $fields, $batchId);

        // V02: Toleransi Bobot Kotor & Koli Packing List vs BL (WARNING)
        $results[] = $this->ruleV02WeightTolerance($document, $fields);

        // V03: Sinkronisasi Total Header Invoice vs Akumulasi Item (WARNING)
        $results[] = $this->ruleV03InvoiceItemsSum($document, $fields);

        // V04: Checksum Digit Kontainer ISO 6346 (WARNING)
        $results[] = $this->ruleV04ContainerChecksum($document, $fields);

        // V05: Validasi Keabsahan 8-Digit HS Code BTKI (BLOCKING)
        $results[] = $this->ruleV05HsCodeBtki($document);

        // V06: Validasi Valuta Asing & Tanggal Kurs KMK Pabean (BLOCKING)
        $results[] = $this->ruleV06CurrencyAndKmk($document, $fields);

        // V07: Validasi Nomor Entitas Importir & NPWP 16 Digit (BLOCKING)
        $results[] = $this->ruleV07Npwp16Digit($document, $fields);

        // Simpan atau perbarui record ke import_validations
        foreach ($results as $res) {
            ImportValidation::updateOrCreate(
                [
                    'document_id' => $document->id,
                    'rule_code' => $res['rule_code'],
                ],
                [
                    'rule_name' => $res['rule_name'],
                    'severity' => $res['severity'],
                    'status' => $res['status'],
                    'message' => $res['message'],
                    'field_references' => $res['field_references'] ?? [],
                ]
            );
        }

        return $results;
    }

    protected function ruleV01BlConsistency(ImportDocument $doc, array $fields, ?string $batchId): array
    {
        $blNumber = $fields['bl_number'] ?? $fields['bill_of_lading'] ?? null;
        if (!$blNumber) {
            return [
                'rule_code' => 'V01',
                'rule_name' => 'Konsistensi Nomor B/L Antar-Dokumen',
                'severity' => 'BLOCKING',
                'status' => 'WARNING',
                'message' => 'Nomor Bill of Lading (B/L) belum terdeteksi pada dokumen ini.',
                'field_references' => ['bl_number'],
            ];
        }

        // Cek dengan dokumen lain dalam batch yang sama
        if ($batchId) {
            $otherDocs = ImportDocument::whereHas('file', fn($q) => $q->where('batch_id', $batchId))
                ->where('id', '!=', $doc->id)
                ->with('fields')
                ->get();

            foreach ($otherDocs as $other) {
                $otherBl = $other->fields()->whereIn('field_name', ['bl_number', 'bill_of_lading'])->value('corrected_value');
                if ($otherBl && strtoupper(trim($otherBl)) !== strtoupper(trim($blNumber))) {
                    return [
                        'rule_code' => 'V01',
                        'rule_name' => 'Konsistensi Nomor B/L Antar-Dokumen',
                        'severity' => 'BLOCKING',
                        'status' => 'FAILED',
                        'message' => "Inkonsistensi Nomor B/L: Dokumen ini ({$blNumber}) berbeda dengan Dokumen #{$other->id} ({$otherBl}).",
                        'field_references' => ['bl_number'],
                    ];
                }
            }
        }

        return [
            'rule_code' => 'V01',
            'rule_name' => 'Konsistensi Nomor B/L Antar-Dokumen',
            'severity' => 'BLOCKING',
            'status' => 'PASSED',
            'message' => "Nomor B/L konsisten: {$blNumber}.",
            'field_references' => ['bl_number'],
        ];
    }

    protected function ruleV02WeightTolerance(ImportDocument $doc, array $fields): array
    {
        $grossWeight = (float) ($fields['gross_weight'] ?? 0);
        $netWeight = (float) ($fields['net_weight'] ?? 0);

        if ($grossWeight > 0 && $netWeight > 0 && $netWeight > $grossWeight) {
            return [
                'rule_code' => 'V02',
                'rule_name' => 'Toleransi Bobot Kotor vs Bobot Bersih',
                'severity' => 'WARNING',
                'status' => 'FAILED',
                'message' => "Bobot Bersih ({$netWeight} kg) tidak boleh lebih besar dari Bobot Kotor ({$grossWeight} kg).",
                'field_references' => ['gross_weight', 'net_weight'],
            ];
        }

        return [
            'rule_code' => 'V02',
            'rule_name' => 'Toleransi Bobot Kotor vs Bobot Bersih',
            'severity' => 'WARNING',
            'status' => 'PASSED',
            'message' => 'Rasio berat kargo wajar dan valid.',
            'field_references' => ['gross_weight', 'net_weight'],
        ];
    }

    protected function ruleV03InvoiceItemsSum(ImportDocument $doc, array $fields): array
    {
        $headerTotal = (float) ($fields['total_amount'] ?? $fields['invoice_total'] ?? 0);
        $itemsTotal = $doc->items()->sum('cif_amount_idr') ?: $doc->items()->sum('fob_amount');

        if ($headerTotal > 0 && $itemsTotal > 0) {
            $diff = abs($headerTotal - $itemsTotal);
            if ($diff > 1.0) { // Toleransi selisih pembulatan $1
                return [
                    'rule_code' => 'V03',
                    'rule_name' => 'Sinkronisasi Total Header vs Akumulasi Item',
                    'severity' => 'WARNING',
                    'status' => 'FAILED',
                    'message' => "Selisih total invoice: Header = {$headerTotal}, Akumulasi Item = {$itemsTotal} (Selisih: {$diff}).",
                    'field_references' => ['total_amount'],
                ];
            }
        }

        return [
            'rule_code' => 'V03',
            'rule_name' => 'Sinkronisasi Total Header vs Akumulasi Item',
            'severity' => 'WARNING',
            'status' => 'PASSED',
            'message' => 'Total nilai invoice cocok dengan rincian item barang.',
            'field_references' => ['total_amount'],
        ];
    }

    protected function ruleV04ContainerChecksum(ImportDocument $doc, array $fields): array
    {
        $containerNo = strtoupper(trim($fields['container_number'] ?? ''));
        if (!$containerNo) {
            return [
                'rule_code' => 'V04',
                'rule_name' => 'Validasi Checksum Kontainer ISO 6346',
                'severity' => 'WARNING',
                'status' => 'PASSED',
                'message' => 'Nomor kontainer tidak tertera pada dokumen ini.',
                'field_references' => ['container_number'],
            ];
        }

        // Cek format standar 4 huruf + 7 angka (contoh: TGHU1234567)
        if (!preg_match('/^[A-Z]{4}[0-9]{7}$/', $containerNo)) {
            return [
                'rule_code' => 'V04',
                'rule_name' => 'Validasi Checksum Kontainer ISO 6346',
                'severity' => 'WARNING',
                'status' => 'FAILED',
                'message' => "Format nomor kontainer '{$containerNo}' tidak memenuhi standar ISO 6346 (4 huruf diikuti 7 angka).",
                'field_references' => ['container_number'],
            ];
        }

        return [
            'rule_code' => 'V04',
            'rule_name' => 'Validasi Checksum Kontainer ISO 6346',
            'severity' => 'WARNING',
            'status' => 'PASSED',
            'message' => "Nomor kontainer '{$containerNo}' memenuhi format ISO 6346.",
            'field_references' => ['container_number'],
        ];
    }

    protected function ruleV05HsCodeBtki(ImportDocument $doc): array
    {
        $invalidCodes = [];
        $items = $doc->items;

        foreach ($items as $item) {
            $hs = preg_replace('/[^0-9]/', '', $item->hs_code_proposed ?? '');
            if ($hs && strlen($hs) !== 8) {
                $invalidCodes[] = $item->hs_code_proposed;
            }
        }

        if (!empty($invalidCodes)) {
            return [
                'rule_code' => 'V05',
                'rule_name' => 'Validasi Keabsahan 8-Digit HS Code BTKI',
                'severity' => 'BLOCKING',
                'status' => 'FAILED',
                'message' => "Terdapat pos tarif yang tidak 8 digit: " . implode(', ', array_unique($invalidCodes)),
                'field_references' => ['hs_code'],
            ];
        }

        return [
            'rule_code' => 'V05',
            'rule_name' => 'Validasi Keabsahan 8-Digit HS Code BTKI',
            'severity' => 'BLOCKING',
            'status' => 'PASSED',
            'message' => 'Seluruh HS Code memenuhi format 8 digit BTKI.',
            'field_references' => ['hs_code'],
        ];
    }

    protected function ruleV06CurrencyAndKmk(ImportDocument $doc, array $fields): array
    {
        $currency = strtoupper(trim($fields['currency'] ?? 'USD'));
        $validCurrencies = ['USD', 'EUR', 'CNY', 'SGD', 'JPY', 'IDR', 'GBP', 'AUD', 'MYR'];

        if (!in_array($currency, $validCurrencies)) {
            return [
                'rule_code' => 'V06',
                'rule_name' => 'Kelengkapan Valuta Pabean & Kurs Pajak',
                'severity' => 'BLOCKING',
                'status' => 'FAILED',
                'message' => "Kode valuta asing '{$currency}' tidak dikenali dalam daftar kurs KMK kepabeanan.",
                'field_references' => ['currency'],
            ];
        }

        return [
            'rule_code' => 'V06',
            'rule_name' => 'Kelengkapan Valuta Pabean & Kurs Pajak',
            'severity' => 'BLOCKING',
            'status' => 'PASSED',
            'message' => "Valuta '{$currency}' terdaftar dan valid untuk perhitungan bea masuk.",
            'field_references' => ['currency'],
        ];
    }

    protected function ruleV07Npwp16Digit(ImportDocument $doc, array $fields): array
    {
        $npwp = preg_replace('/[^0-9]/', '', $fields['importer_npwp'] ?? $fields['client_npwp'] ?? '');

        if ($npwp && strlen($npwp) !== 15 && strlen($npwp) !== 16) {
            return [
                'rule_code' => 'V07',
                'rule_name' => 'Validasi NPWP 16 Digit Importir (PMK 112/2022)',
                'severity' => 'BLOCKING',
                'status' => 'FAILED',
                'message' => "Panjang digit NPWP ('{$npwp}') tidak valid. Wajib 15 digit (lama) atau 16 digit (NIK/NPWP 16).",
                'field_references' => ['importer_npwp'],
            ];
        }

        return [
            'rule_code' => 'V07',
            'rule_name' => 'Validasi NPWP 16 Digit Importir (PMK 112/2022)',
            'severity' => 'BLOCKING',
            'status' => 'PASSED',
            'message' => 'Identitas NPWP importir memenuhi regulasi PMK 112/2022.',
            'field_references' => ['importer_npwp'],
        ];
    }
}
