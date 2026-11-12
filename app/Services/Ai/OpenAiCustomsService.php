<?php

namespace App\Services\Ai;

use App\Services\Ceisa\CeisaHsCodeService;
use Illuminate\Support\Facades\Log;

class OpenAiCustomsService
{
    protected string $endpoint;
    protected string $apiKey;
    protected string $defaultModel;
    protected int $timeout;
    protected CeisaHsCodeService $hsCodeService;

    public function __construct(CeisaHsCodeService $hsCodeService)
    {
        $this->endpoint = rtrim(config('ai.endpoint', 'https://r4g77gv.abc-tunnel.us/v1'), '/');
        $this->apiKey = config('ai.api_key', 'sk-59807b51bc76063d-ge0agt-97ac1590');
        $this->defaultModel = config('ai.default_model', 'cx/gpt-6-astra');
        $this->timeout = (int) config('ai.timeout', 60);
        $this->hsCodeService = $hsCodeService;
    }

    /**
     * Dapatkan daftar seluruh model AI yang didukung sistem.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getSupportedModels(): array
    {
        return config('ai.models', []);
    }

    /**
     * Alias untuk getSupportedModels.
     */
    public function getAvailableModels(): array
    {
        return $this->getSupportedModels();
    }

    /**
     * Dapatkan model yang dikelompokkan berdasarkan kategori.
     */
    public function getCategorizedModels(): array
    {
        $all = $this->getSupportedModels();
        $categorized = [
            'OpenAI Codex Astra Series' => [],
            'OpenAI Codex Sol & Terra Series' => [],
            'OpenAI Codex 5.x Series' => [],
            'Google Antigravity Gemini Series' => [],
        ];

        foreach ($all as $id => $info) {
            $series = strtolower((string) ($info['series'] ?? ''));
            $provider = strtolower((string) ($info['provider'] ?? ''));

            if (str_contains($series, 'astra')) {
                $categorized['OpenAI Codex Astra Series'][$id] = $info;
            } elseif (str_contains($series, 'sol') || str_contains($series, 'terra') || str_contains($series, 'luna')) {
                $categorized['OpenAI Codex Sol & Terra Series'][$id] = $info;
            } elseif (str_contains($series, '5.') || str_contains($series, '5.x')) {
                $categorized['OpenAI Codex 5.x Series'][$id] = $info;
            } elseif (str_contains($series, 'gemini') || str_contains($provider, 'google') || str_contains($provider, 'antigravity')) {
                $categorized['Google Antigravity Gemini Series'][$id] = $info;
            } else {
                $categorized['Lainnya'][$id] = $info;
            }
        }

        return array_filter($categorized);
    }

    /**
     * Dapatkan model default.
     */
    public function getDefaultModel(): string
    {
        return $this->defaultModel;
    }

    /**
     * Alias ekstraksi JSON untuk testing dan utilitas.
     */
    public function cleanJsonResponse(string $text): ?array
    {
        return $this->extractJsonFromText($text);
    }

    /**
     * Panggil endpoint OpenAI-compatible dengan penanganan cerdas (JSON & SSE stream chunks)
     * serta fallback otomatis jika model tertentu sedang sibuk atau dibatasi.
     */
    public function chatCompletion(array $messages, ?string $model = null, float $temperature = 0.1): array
    {
        $requestedModel = $model ?: $this->defaultModel;
        $fallbackChain = config('ai.fallback_models', [
            'cx/gpt-6-astra',
            'cx/gpt-5.6-terra',
            'cx/gpt-6-astra-xhigh',
            'cx/gpt-5.6-sol',
            'cx/gpt-5.6-luna',
            'ag/gemini-3.8-flash-high',
            'ag/gemini-3.8-flash-low',
        ]);

        // Prioritaskan model yang diminta di urutan pertama
        $modelsToTry = array_unique(array_merge([$requestedModel], $fallbackChain));

        $lastError = null;
        foreach ($modelsToTry as $currentModel) {
            $result = $this->executeSingleChatCall($currentModel, $messages, $temperature);
            if ($result['success']) {
                $result['model_requested'] = $requestedModel;
                $result['model_used'] = $currentModel;
                return $result;
            }
            $lastError = $result['error'] ?? 'Unknown API error';
            Log::warning("Customs AI model '{$currentModel}' failed, trying fallback: {$lastError}");
        }

        return [
            'success' => false,
            'model_requested' => $requestedModel,
            'model_used' => null,
            'content' => null,
            'error' => "Seluruh model AI gagal dieksekusi. Error terakhir: {$lastError}",
        ];
    }

    /**
     * Eksekusi satu panggilan HTTP cURL ke OpenAI-compatible endpoint.
     */
    protected function executeSingleChatCall(string $model, array $messages, float $temperature): array
    {
        $url = "{$this->endpoint}/chat/completions";
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json",
            "Accept: application/json, text/event-stream",
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'error' => "cURL Error: {$curlError}",
            ];
        }

        if ($httpCode !== 200 || empty($response)) {
            $msg = "HTTP {$httpCode}: " . substr(strip_tags((string) $response), 0, 300);
            return [
                'success' => false,
                'error' => $msg,
            ];
        }

        // 1. Coba decode respon standar JSON OpenAI
        $json = json_decode($response, true);
        if (isset($json['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'content' => $json['choices'][0]['message']['content'],
                'raw' => $json,
            ];
        }

        // 2. Coba parse respon SSE stream chunks (misal model Gemini)
        if (str_contains($response, 'data:')) {
            $lines = explode("\n", $response);
            $fullContent = '';
            foreach ($lines as $line) {
                $line = trim($line);
                if (str_starts_with($line, 'data:')) {
                    $dataStr = trim(substr($line, 5));
                    if ($dataStr === '[DONE]') continue;
                    $chunk = json_decode($dataStr, true);
                    if (isset($chunk['choices'][0]['delta']['content'])) {
                        $fullContent .= $chunk['choices'][0]['delta']['content'];
                    } elseif (isset($chunk['choices'][0]['text'])) {
                        $fullContent .= $chunk['choices'][0]['text'];
                    }
                }
            }

            if (!empty($fullContent)) {
                return [
                    'success' => true,
                    'content' => $fullContent,
                    'raw' => $response,
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Format respon AI tidak dapat diparsing ke teks valid.',
            'raw' => substr($response, 0, 500),
        ];
    }

    /**
     * Mencocokkan isi Packing List dengan Pos Tarif HS Code BTKI 2022 secara komprehensif.
     * Mengembalikan item terperinci, akurasi, Bea Masuk, PPN, PPh, Bea Keluar, dan Audit Lartas.
     *
     * @param string $packingListText Teks dokumen atau hasil ekstraksi file
     * @param string|null $model Model AI pilihan
     * @param string $direction 'IMPORT_BC20' atau 'EKSPORT_BC30'
     * @return array<string, mixed>
     */
    public function matchPackingList(string $packingListText, ?string $model = null, string $direction = 'IMPORT_BC20'): array
    {
        $cleanText = trim($packingListText);
        if (empty($cleanText)) {
            return [
                'success' => false,
                'error' => 'Konten teks packing list kosong.',
            ];
        }

        $systemPrompt = <<<SYS
Anda adalah AI Ahli Kepabeanan Indonesia (Customs Specialist DJBC & CEISA 4.0) dan Klasifikator Pos Tarif BTKI (Buku Tarif Kepabeanan Indonesia 2022).
Tugas Anda adalah membedah dan menganalisis secara teliti dokumen Packing List / Commercial Invoice / Shipping Manifest, lalu mencocokkan SETIAP item barang kargo dengan pos tarif HS Code 8-digit yang paling presisi dan spesifik sesuai BTKI 2022 untuk pengajuan dokumen pabean Indonesia:
- Jika pengapalan impor (tujuan Indonesia) -> Rekomendasi Dokumen: PIB (BC 2.0 IMPOR)
- Jika pengapalan ekspor (asal Indonesia ke luar negeri) -> Rekomendasi Dokumen: PEB (BC 3.0 EKSPOR)

ATURAN KLASIFIKASI BTKI 2022:
1. Pos tarif HS Code WAJIB 8 digit dengan titik (contoh: 8452.29.00, 8471.30.20, 1006.30.30, 0901.11.10).
2. Tentukan nama uraian barang yang baku dan jelas (dalam Bahasa Indonesia atau Inggris).
3. Ekstrak rincian fisik: jumlah kemasan, jenis kemasan (CTN, BOX, PKG, SET, PCE, ROLL, PLT, DRUM, UNT, KGS), berat bruto (kg), berat netto (kg), serta nilai FOB dan valuta jika tertera.
4. Identifikasi apakah barang terkena Lartas (Larangan dan Pembatasan) di Indonesia, misalnya: Laporan Surveyor (LS Kemendag), SNI Wajib, SDPPI Postel, Karantina Hewan/Ikan/Tumbuhan, V-Legal Kayu, BPOM, atau Bebas Lartas.

KELUARKAN OUTPUT HANYA DALAM FORMAT JSON VALID MURNI (tanpa ```json, tanpa teks pembuka/penutup):
{
  "recommendation": "PIB" atau "PEB",
  "detected_type": "IMPORT" atau "EXPORT",
  "direction": "IMPORT_BC20" atau "EKSPORT_BC30",
  "shipper_name": "Nama Pengirim / Eksportir jika ada",
  "consignee_name": "Nama Penerima / Importir jika ada",
  "invoice_number": "Nomor invoice jika ada",
  "port_of_loading": "Pelabuhan Muat jika ada",
  "port_of_discharge": "Pelabuhan Bongkar jika ada",
  "analysis_summary": "Ringkasan analisis muatan kargo oleh AI",
  "items": [
    {
      "nomor_urut": 1,
      "uraian_barang": "NAMA BARANG LENGKAP & SPESIFIKASI",
      "kode_hs": "8452.29.00",
      "hs_code_clean": "84522900",
      "hs_confidence_score": 0.98,
      "hs_legal_basis": "Catatan Bab/Pos BTKI 2022 terkait alasan pemilihan HS",
      "hs_reasoning": "Penjelasan mengapa barang ini masuk pos tarif ini",
      "jumlah_kemasan": 20,
      "jenis_kemasan": "CTN",
      "berat_bruto": 1400.0,
      "berat_netto": 1200.0,
      "nilai_fob": 8000.0,
      "kode_valuta": "USD",
      "estimasi_bm_persen": 5.0,
      "estimasi_ppn_persen": 11.0,
      "estimasi_pph_persen": 2.5,
      "estimasi_bk_persen": 0.0,
      "has_lartas": false,
      "lartas_info": "Keterangan lartas atau Non-Lartas (Bebas)"
    }
  ]
}
SYS;

        $userPrompt = "Berikut adalah dokumen Packing List untuk dipindai dan dicocokkan pos tarif HS Code BTKI 2022:\n\n" . $cleanText;
        if ($direction) {
            $userPrompt .= "\n\nCatatan: Dokumen ini diproses untuk alur: {$direction}.";
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $aiResponse = $this->chatCompletion($messages, $model, 0.1);
        if (!$aiResponse['success']) {
            return [
                'success' => false,
                'error' => $aiResponse['error'] ?? 'Gagal menghubungi OpenAI engine.',
                'model_requested' => $aiResponse['model_requested'] ?? $model,
                'model_used' => $aiResponse['model_used'] ?? null,
            ];
        }

        $parsedData = $this->extractJsonFromText($aiResponse['content']);
        if (!$parsedData || !isset($parsedData['items']) || !is_array($parsedData['items'])) {
            return [
                'success' => false,
                'error' => 'AI berhasil merespon tetapi format JSON tidak sesuai struktur item.',
                'raw_content' => $aiResponse['content'],
                'model_used' => $aiResponse['model_used'],
            ];
        }

        // Pengayaan Cerdas (Enrichment) dengan Database Pos Tarif Resmi DJBC & CEISA
        $enrichedItems = [];
        $totalKemasan = 0;
        $totalBruto = 0.0;
        $totalNetto = 0.0;
        $totalFob = 0.0;
        $valuta = 'USD';
        $allLartasDocs = [];
        $hasAnyLartas = false;

        foreach ($parsedData['items'] as $idx => $item) {
            $no = $idx + 1;
            $rawHs = (string) ($item['kode_hs'] ?? '');
            $cleanHs = preg_replace('/[^0-9]/', '', $rawHs);
            $desc = trim((string) ($item['uraian_barang'] ?? 'BARANG TANPA URAIAN'));

            // Cross-reference dengan CeisaHsCodeService (DB + Built-in Knowledge Base)
            $officialTariff = $this->hsCodeService->getTariffAndLartas($cleanHs);

            $formattedHs = $rawHs;
            if (strlen($cleanHs) === 8 && !str_contains($rawHs, '.')) {
                $formattedHs = substr($cleanHs, 0, 4) . '.' . substr($cleanHs, 4, 2) . '.' . substr($cleanHs, 6, 2);
            }

            $bm = isset($officialTariff['bm']) && $officialTariff['success']
                ? (float) $officialTariff['bm']
                : (float) ($item['estimasi_bm_persen'] ?? 0.0);

            $ppn = isset($officialTariff['ppn']) && $officialTariff['success']
                ? (float) $officialTariff['ppn']
                : (float) ($item['estimasi_ppn_persen'] ?? 11.0);

            $pph = isset($officialTariff['pph']) && $officialTariff['success']
                ? (float) $officialTariff['pph']
                : (float) ($item['estimasi_pph_persen'] ?? 2.5);

            $bk = isset($officialTariff['bk']) && $officialTariff['success']
                ? (float) $officialTariff['bk']
                : (float) ($item['estimasi_bk_persen'] ?? 0.0);

            $hasLartas = !empty($officialTariff['has_lartas']) || !empty($item['has_lartas']);
            if ($hasLartas) {
                $hasAnyLartas = true;
            }

            $lartasInfo = $officialTariff['lartas'] ?? ($item['lartas_info'] ?? ($hasLartas ? 'Wajib Perizinan Lartas' : 'Bebas Lartas'));
            $requiredDocs = $officialTariff['required_documents'] ?? [];

            foreach ($requiredDocs as $rd) {
                $allLartasDocs[$rd['kode_dokumen']] = $rd;
            }

            $kemasanQty = (int) ($item['jumlah_kemasan'] ?? 1);
            $kemasanJenis = strtoupper(trim((string) ($item['jenis_kemasan'] ?? 'CTN')));
            $bruto = (float) ($item['berat_bruto'] ?? 0.0);
            $netto = (float) ($item['berat_netto'] ?? $bruto);
            $fob = (float) ($item['nilai_fob'] ?? 0.0);
            $itemValuta = strtoupper(trim((string) ($item['kode_valuta'] ?? 'USD')));
            if ($itemValuta) {
                $valuta = $itemValuta;
            }

            $totalKemasan += $kemasanQty;
            $totalBruto += $bruto;
            $totalNetto += $netto;
            $totalFob += $fob;

            $enrichedItems[] = [
                'nomor_urut' => $no,
                'uraian_barang' => strtoupper($desc),
                'kode_hs' => $formattedHs,
                'hs_code_clean' => $cleanHs,
                'hs_confidence_score' => (float) ($item['hs_confidence_score'] ?? 0.98),
                'hs_legal_basis' => $officialTariff['legal'] ?? ($item['hs_legal_basis'] ?? 'BTKI 2022 Pos Tarif DJBC'),
                'hs_reasoning' => $item['hs_reasoning'] ?? ($officialTariff['desc'] ?? null),
                'jumlah_kemasan' => max(1, $kemasanQty),
                'jenis_kemasan' => $kemasanJenis ?: 'CTN',
                'berat_bruto' => round($bruto, 2),
                'berat_netto' => round($netto, 2),
                'nilai_fob' => round($fob, 2),
                'kode_valuta' => $valuta,
                'tarif_bm_persen' => $bm,
                'tarif_ppn_persen' => $ppn,
                'tarif_pph_persen' => $pph,
                'tarif_bk_persen' => $bk,
                'has_lartas' => $hasLartas,
                'lartas_info' => $lartasInfo,
                'required_documents' => $requiredDocs,
            ];
        }

        $isExport = ($parsedData['recommendation'] === 'PEB' || $parsedData['detected_type'] === 'EXPORT' || $direction === 'EKSPORT_BC30');
        $recDoc = $isExport ? 'PEB (BC 3.0 EKSPOR)' : 'PIB (BC 2.0 IMPOR)';
        $declCode = $isExport ? '30' : '20';

        return [
            'success' => true,
            'model_requested' => $aiResponse['model_requested'],
            'model_used' => $aiResponse['model_used'],
            'recommendation' => $isExport ? 'PEB' : 'PIB',
            'declaration_name' => $recDoc,
            'jenis_dokumen' => $declCode,
            'detected_type' => $isExport ? 'EXPORT' : 'IMPORT',
            'direction' => $isExport ? 'EKSPORT_BC30' : 'IMPORT_BC20',
            'shipper_name' => $parsedData['shipper_name'] ?? null,
            'consignee_name' => $parsedData['consignee_name'] ?? null,
            'invoice_number' => $parsedData['invoice_number'] ?? null,
            'port_of_loading' => $parsedData['port_of_loading'] ?? null,
            'port_of_discharge' => $parsedData['port_of_discharge'] ?? null,
            'analysis_summary' => $parsedData['analysis_summary'] ?? 'Klasifikasi barang packing list dan pencocokan pos tarif BTKI 2022 berhasil dilakukan oleh AI.',
            'items_count' => count($enrichedItems),
            'items' => $enrichedItems,
            'totals' => [
                'total_kemasan' => $totalKemasan,
                'total_bruto' => round($totalBruto, 2),
                'total_netto' => round($totalNetto, 2),
                'total_fob' => round($totalFob, 2),
                'currency' => $valuta,
            ],
            'has_lartas' => $hasAnyLartas,
            'required_customs_documents' => array_values($allLartasDocs),
            'raw_ai_text' => $aiResponse['content'],
        ];
    }

    /**
     * Klasifikasi komoditas tunggal berbasis teks menggunakan AI.
     */
    public function classifyCommodity(string $text, ?string $model = null, ?string $direction = null): array
    {
        $cleanText = trim($text);
        if (empty($cleanText)) {
            return [
                'success' => false,
                'error' => 'Uraian barang tidak boleh kosong.',
            ];
        }

        $systemPrompt = <<<SYS
Anda adalah AI Ahli Klasifikasi Pos Tarif BTKI 2022 Kepabeanan Indonesia.
Tentukan pos tarif 8-digit BTKI 2022 yang paling tepat untuk komoditas yang diberikan.
Berikan respon HANYA dalam format JSON valid (tanpa markdown ```json):
{
  "code": "8452.29.00",
  "clean_code": "84522900",
  "desc": "Uraian resmi pos tarif BTKI 2022",
  "legal": "Dasar hukum / catatan Bab dan Pos BTKI",
  "reasoning": "Alasan spesifik mengapa komoditas masuk ke HS ini",
  "confidence_score": 0.99,
  "bm": 5.0,
  "ppn": 11.0,
  "pph": 2.5,
  "bk": 0.0,
  "satuan": "SET",
  "has_lartas": false,
  "lartas": "Bebas Lartas atau jenis perizinan wajib",
  "required_documents": []
}
SYS;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Klasifikasikan komoditas berikut ke HS code BTKI 2022: {$cleanText}"],
        ];

        $aiRes = $this->chatCompletion($messages, $model, 0.1);
        if (!$aiRes['success']) {
            // Fallback ke built-in rule service jika AI gagal
            $fallback = $this->hsCodeService->classifyCommodity($cleanText, $direction);
            $fallback['ai_model_used'] = 'FALLBACK_RULES';
            return $fallback;
        }

        $parsed = $this->extractJsonFromText($aiRes['content']);
        if (!$parsed || !isset($parsed['code'])) {
            $fallback = $this->hsCodeService->classifyCommodity($cleanText, $direction);
            $fallback['ai_model_used'] = 'FALLBACK_RULES';
            return $fallback;
        }

        // Cross-reference dengan data resmi CeisaHsCodeService
        $cleanCode = preg_replace('/[^0-9]/', '', (string) ($parsed['clean_code'] ?? $parsed['code']));
        $official = $this->hsCodeService->getTariffAndLartas($cleanCode);

        return [
            'success' => true,
            'code' => $parsed['code'],
            'clean_code' => $cleanCode,
            'desc' => $parsed['desc'] ?? ($official['desc'] ?? $cleanText),
            'legal' => $official['legal'] ?? ($parsed['legal'] ?? 'BTKI 2022'),
            'reasoning' => $parsed['reasoning'] ?? null,
            'confidence_score' => (float) ($parsed['confidence_score'] ?? 0.98),
            'bm' => isset($official['bm']) && $official['success'] ? (float) $official['bm'] : (float) ($parsed['bm'] ?? 0.0),
            'ppn' => isset($official['ppn']) && $official['success'] ? (float) $official['ppn'] : (float) ($parsed['ppn'] ?? 11.0),
            'pph' => isset($official['pph']) && $official['success'] ? (float) $official['pph'] : (float) ($parsed['pph'] ?? 2.5),
            'bk' => isset($official['bk']) && $official['success'] ? (float) $official['bk'] : (float) ($parsed['bk'] ?? 0.0),
            'satuan' => $official['satuan'] ?? ($parsed['satuan'] ?? 'PCE'),
            'has_lartas' => !empty($official['has_lartas']) || !empty($parsed['has_lartas']),
            'lartas' => $official['lartas'] ?? ($parsed['lartas'] ?? 'Non-Lartas'),
            'required_documents' => $official['required_documents'] ?? ($parsed['required_documents'] ?? []),
            'ai_model_used' => $aiRes['model_used'],
        ];
    }

    /**
     * Ekstraksi Cerdas Bundle Dokumen Ekspor/Impor (Multi-file Text + Vision Image OCR).
     * Mengklasifikasi masing-masing berkas sekaligus mengekstrak header, kontainer, dan item barang siap isi PIB/PEB.
     *
     * @param array<int, array{filename: string, text: string, image_data_url?: string|null}> $filesData
     */
    public function analyzeDocumentBundle(array $filesData, ?string $model = null, ?string $preferredType = null): array
    {
        if (empty($filesData)) {
            return ['success' => false, 'error' => 'Bundle file kosong.'];
        }

        $systemPrompt = <<<SYS
Anda adalah AI Ahli Kepabeanan Indonesia (DJBC CEISA 4.0) yang menganalisis BUNDLE DOKUMEN ekspor-impor (Commercial Invoice, Packing List, Bill of Lading/AWB, COO Form E/D/AK, Polis Asuransi, Sales Contract/PO, Booking Receipt, Lartas).
Tugas Anda:
1. Klasifikasikan SETIAP berkas ke salah satu kode slot: BL_AWB, INVOICE, PACKING_LIST, COO_FORME, SC_PO, INSURANCE, BOOKING_RECEIPT, PERIJINAN_LARTAS, ETC.
2. Tentukan jenis pengapalan: IMPORT (BC 2.0 / PIB, jika consignee di Indonesia) atau EXPORT (BC 3.0 / PEB, jika shipper di Indonesia).
3. Ekstrak seluruh informasi header dan item barang seakurat mungkin untuk pengisian otomatis formulir CEISA 4.0.

OUTPUT HANYA JSON VALID MURNI:
{
  "file_classifications": [
    {"filename": "nama_file.pdf", "slot": "INVOICE", "confidence": 98, "reason": "Memuat Commercial Invoice no ..."}
  ],
  "type": "IMPORT" atau "EXPORT",
  "client_name": "Nama perusahaan Indonesia (Importir saat IMPORT, Eksportir saat EXPORT)",
  "client_npwp": "15 atau 16 digit NPWP jika ada, atau null",
  "client_address": "Alamat perusahaan Indonesia atau null",
  "partner_name": "Nama mitra luar negeri (Shipper/Supplier saat IMPORT, Buyer/Consignee LN saat EXPORT)",
  "partner_address": "Alamat mitra luar negeri atau null",
  "partner_country": "Kode negara 2 huruf ISO (CN, SG, US, JP, dll)",
  "bl_number": "Nomor B/L atau AWB",
  "bl_date": "YYYY-MM-DD atau null",
  "invoice_number": "Nomor Commercial Invoice",
  "invoice_date": "YYYY-MM-DD atau null",
  "shipping_line": "Nama pelayaran (CMA CGM, MSC, OOCL, Maersk, dll)",
  "vessel_name": "Nama kapal / flight",
  "voyage_number": "Nomor voyage",
  "port_of_loading": "Kode atau nama pelabuhan muat (contoh: CNYTN / NINGBO)",
  "port_of_discharge": "Kode atau nama pelabuhan bongkar (contoh: IDTPP / TANJUNG PRIOK)",
  "eta": "YYYY-MM-DD atau null",
  "etd": "YYYY-MM-DD atau null",
  "total_kontainer": 1,
  "nomor_kontainer": "Daftar nomor kontainer ISO dipisah koma (contoh: TGCU5494395)",
  "ukuran_kontainer": "40HQ / 40GP / 20GP / LCL",
  "total_kemasan": 100,
  "jenis_kemasan": "CTN / PKG / PLT / SET",
  "total_bruto": 1000.0,
  "total_netto": 900.0,
  "currency": "USD / CNY / EUR / IDR",
  "incoterm": "CIF / FOB / CNF / EXW",
  "total_fob": 10000.0,
  "freight": 0.0,
  "insurance": 0.0,
  "total_cif": 10000.0,
  "commodity_summary": "Ringkasan komoditas utama",
  "items": [
    {
      "nomor_urut": 1,
      "uraian_barang": "Deskripsi barang",
      "kode_hs": "8504.40.90",
      "negara_asal": "CN",
      "jumlah_kemasan": 10,
      "jenis_kemasan": "CTN",
      "berat_bruto": 100.0,
      "berat_netto": 90.0,
      "nilai_fob": 1000.0
    }
  ]
}
Jangan mengarang angka; isi null jika tidak tertera di dokumen.
SYS;

        $contentParts = [];
        $promptText = "Analisis bundle dokumen berikut" . ($preferredType ? " (Konteks arah layanan: {$preferredType})" : "") . ":\n";

        foreach ($filesData as $idx => $fileInfo) {
            $num = $idx + 1;
            $fname = $fileInfo['filename'] ?? "file_{$num}";
            $txt = trim((string) ($fileInfo['text'] ?? ''));
            $promptText .= "\n=== BERKAS #{$num}: {$fname} ===\n";
            if ($txt !== '') {
                $promptText .= mb_substr($txt, 0, 6000) . "\n";
            } else {
                $promptText .= "(Dokumen berupa gambar/scan - lihat lampiran gambar di bawah)\n";
            }
        }

        $contentParts[] = ['type' => 'text', 'text' => $promptText];
        foreach ($filesData as $fileInfo) {
            if (!empty($fileInfo['image_data_url'])) {
                $contentParts[] = [
                    'type' => 'image_url',
                    'image_url' => ['url' => $fileInfo['image_data_url']],
                ];
            }
        }

        $hasImages = count($contentParts) > 1;
        $userContent = $hasImages ? $contentParts : $promptText;
        $targetModel = $model ?: ($hasImages ? 'ag/gemini-3.8-flash-high' : $this->defaultModel);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userContent],
        ];

        $aiRes = $this->chatCompletion($messages, $targetModel, 0.1);
        if (!$aiRes['success']) {
            return [
                'success' => false,
                'error' => $aiRes['error'] ?? 'AI Bundle API gagal diakses.',
            ];
        }

        $parsed = $this->extractJsonFromText($aiRes['content'] ?? '');
        if (!$parsed || !is_array($parsed)) {
            return [
                'success' => false,
                'error' => 'Format respon AI bundle tidak valid JSON.',
            ];
        }

        $parsed['success'] = true;
        $parsed['ai_model_used'] = $aiRes['model_used'] ?? $targetModel;

        return $parsed;
    }

    /**
     * Ekstrak JSON array/object dari teks mentah respon LLM secara aman.
     */
    protected function extractJsonFromText(string $text): ?array
    {
        $clean = trim($text);

        // Hapus bungkus markdown code block jika ada
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        // Coba decode langsung
        $data = json_decode($clean, true);
        if (is_array($data)) {
            return $data;
        }

        // Cari substring antara kurung kurawal terluar { ... }
        $firstBrace = strpos($clean, '{');
        $lastBrace = strrpos($clean, '}');
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $sub = substr($clean, $firstBrace, $lastBrace - $firstBrace + 1);
            $data = json_decode($sub, true);
            if (is_array($data)) {
                return $data;
            }
        }

        return null;
    }
}
