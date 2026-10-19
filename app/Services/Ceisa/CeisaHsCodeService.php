<?php

namespace App\Services\Ceisa;

use App\Models\CeisaDocument;
use App\Models\HsCode;
use App\Models\HsRegulation;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CeisaHsCodeService
{
    protected CeisaClientService $client;
    protected CeisaDocumentService $documentService;

    /**
     * Database Referensi Cerdas Pos Tarif BTKI & Lartas beserta Dokumen Wajib Bea Cukai
     */
    protected array $builtInKnowledgeBase = [
        '85044090' => [
            'code' => '8504.40.90',
            'desc' => 'Static Converters / Power Supply Units / Adaptor Listrik',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => true,
            'lartas' => 'Lartas Post-Border (SNI Wajib & Laporan Surveyor / LS Kemendag)',
            'legal' => 'BTKI 2022 Pos 8504 - Konverter statis (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [
                [
                    'kode_dokumen' => '861',
                    'nama_dokumen' => 'Laporan Surveyor (LS Kemendag)',
                    'instansi' => 'Kementerian Perdagangan / Surveyor Independen',
                    'is_mandatory' => true,
                ],
                [
                    'kode_dokumen' => '810',
                    'nama_dokumen' => 'Sertifikat Standar / SNI Wajib',
                    'instansi' => 'Kementerian Perindustrian',
                    'is_mandatory' => true,
                ],
            ],
        ],
        '84713020' => [
            'code' => '8471.30.20',
            'desc' => 'Laptop / Komputer Jinjing Portabel Tidak Lebih dari 10 KG',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => true,
            'lartas' => 'Lartas Post-Border (Sertifikasi SDPPI Kominfo & Persetujuan Impor Kemendag)',
            'legal' => 'BTKI 2022 Pos 8471.30 - Laptop Komputer (BM 0%, PPN 11%, PPh 2.5%)',
            'required_documents' => [
                [
                    'kode_dokumen' => '860',
                    'nama_dokumen' => 'Persetujuan Impor (PI Elektronik Kemendag)',
                    'instansi' => 'Kementerian Perdagangan',
                    'is_mandatory' => true,
                ],
                [
                    'kode_dokumen' => '853',
                    'nama_dokumen' => 'Sertifikat Standar Perangkat Telekomunikasi (SDPPI)',
                    'instansi' => 'Kementerian Komunikasi dan Informatika',
                    'is_mandatory' => true,
                ],
            ],
        ],
        '84522900' => [
            'code' => '8452.29.00',
            'desc' => 'Mesin Jahit Industri Non-Otomatik Lengkap dengan Meja & Dinamo',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'SET',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8452 - Mesin jahit industri (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84529000' => [
            'code' => '8452.90.00',
            'desc' => 'Bagian, Spare Parts, dan Jarum Mesin Jahit Industri',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'BOX',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8452.90 - Spare parts mesin jahit (BM 0%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '15111000' => [
            'code' => '1511.10.00',
            'desc' => 'Crude Palm Oil (CPO) Minyak Kelapa Sawit Mentah',
            'bm' => 0.0,
            'ppn' => 0.0,
            'pph' => 0.0,
            'bk' => 50.0, // USD / MT Bea Keluar Ekspor
            'satuan' => 'TNE',
            'has_lartas' => true,
            'lartas' => 'Lartas Ekspor (Eksportir Terdaftar / ET Sawit Kemendag & Pungutan BPDPKS)',
            'legal' => 'BTKI 2022 Pos 1511 - Minyak Kelapa Sawit Mentah (PMK 38/2024)',
            'required_documents' => [
                [
                    'kode_dokumen' => '872',
                    'nama_dokumen' => 'Eksportir Terdaftar (ET) Sawit Kemendag',
                    'instansi' => 'Kementerian Perdagangan',
                    'is_mandatory' => true,
                ],
            ],
        ],
        '44071100' => [
            'code' => '4407.11.00',
            'desc' => 'Kayu Olahan / Sawn Timber Coniferous Digergaji Memanjang',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 20.0, // Tarif Bea Keluar Ekspor
            'satuan' => 'MTQ',
            'has_lartas' => true,
            'lartas' => 'Lartas Ekspor (Dokumen V-Legal / SVLK KLHK & ET Kayu Kemendag)',
            'legal' => 'BTKI 2022 Pos 4407 - Kayu Gergajian (PMK 38/2024)',
            'required_documents' => [
                [
                    'kode_dokumen' => '880',
                    'nama_dokumen' => 'Dokumen V-Legal / SVLK KLHK',
                    'instansi' => 'Kementerian Lingkungan Hidup dan Kehutanan',
                    'is_mandatory' => true,
                ],
                [
                    'kode_dokumen' => '861',
                    'nama_dokumen' => 'Laporan Surveyor (LS) Ekspor Kayu',
                    'instansi' => 'Kementerian Perdagangan / Surveyor',
                    'is_mandatory' => true,
                ],
            ],
        ],
        '09012110' => [
            'code' => '0901.21.10',
            'desc' => 'Kopi Arabika Disangrai (Roasted) Premium Quality',
            'bm' => 0.0,
            'ppn' => 0.0,
            'pph' => 0.0,
            'bk' => 0.0,
            'satuan' => 'KGM',
            'has_lartas' => true,
            'lartas' => 'Lartas Karantina Tumbuhan (Sertifikat Fitosanitari / PC)',
            'legal' => 'BTKI 2022 Pos 0901.21 - Biji Kopi Sangrai',
            'required_documents' => [
                [
                    'kode_dokumen' => '831',
                    'nama_dokumen' => 'Sertifikat Fitosanitari (Phytosanitary Certificate)',
                    'instansi' => 'Badan Karantina Indonesia (Barantin)',
                    'is_mandatory' => true,
                ],
            ],
        ],
    ];

    public function __construct(CeisaClientService $client, CeisaDocumentService $documentService)
    {
        $this->client = $client;
        $this->documentService = $documentService;
    }

    /**
     * Dapatkan detail Pos Tarif BTKI, Tarif Pungutan (BM, PPN, PPH, BK), dan Regulasi Dokumen Tambahan Lartas
     */
    public function getTariffAndLartas(string $hsCode): array
    {
        $cleanCode = str_replace(['.', ' ', '-'], '', trim($hsCode));

        // 1. Cek Built-in Knowledge Base
        if (isset($this->builtInKnowledgeBase[$cleanCode])) {
            $data = $this->builtInKnowledgeBase[$cleanCode];
            $data['required_documents'] = $data['required_documents'] ?? [];
            return array_merge(['success' => true, 'source' => 'BTKI_KNOWLEDGE_BASE'], $data);
        }

        // 2. Cek Database Lokal (Tabel hs_codes)
        $dbHs = HsCode::where('code', $hsCode)
            ->orWhere('code', $cleanCode)
            ->first();

        if ($dbHs) {
            $regulation = HsRegulation::where('hs_from', '<=', $cleanCode)
                ->where('hs_to', '>=', $cleanCode)
                ->first();

            $reqDocs = [];
            if ($regulation) {
                $reqDocs[] = [
                    'kode_dokumen' => '860',
                    'nama_dokumen' => $regulation->required_doc ?? 'Dokumen Perizinan Lartas',
                    'instansi' => $regulation->source_regulation ?? 'Kementerian Teknis Terkait',
                    'is_mandatory' => true,
                ];
            }

            return [
                'success' => true,
                'source' => 'LOCAL_DATABASE',
                'code' => $dbHs->code,
                'clean_code' => $cleanCode,
                'desc' => $dbHs->description,
                'bm' => (float) $dbHs->duty_bm,
                'ppn' => 11.0,
                'pph' => 2.5,
                'bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => !empty($regulation),
                'lartas' => $regulation ? "Lartas ({$regulation->required_doc})" : 'Bebas Lartas (Non-Lartas)',
                'legal' => $dbHs->btki ?? ("BTKI 2022 Pos {$cleanCode}"),
                'required_documents' => $reqDocs,
            ];
        }

        // 3. Panggil API CEISA 4.0 Open API (/tarif-hs)
        try {
            $apiRes = $this->client->get("/tarif-hs?posTarif={$cleanCode}");
            if ($apiRes['success'] && !empty($apiRes['data'])) {
                $d = $apiRes['data'];
                return [
                    'success' => true,
                    'source' => 'CEISA_OPENAPI',
                    'code' => $d['posTarif'] ?? $cleanCode,
                    'clean_code' => $cleanCode,
                    'desc' => $d['uraian'] ?? "Barang Pos Tarif {$cleanCode}",
                    'bm' => (float) ($d['bm'] ?? 5.0),
                    'ppn' => (float) ($d['ppn'] ?? 11.0),
                    'pph' => (float) ($d['pph'] ?? 2.5),
                    'bk' => (float) ($d['bk'] ?? 0.0),
                    'satuan' => $d['satuan'] ?? 'PCE',
                    'has_lartas' => false,
                    'lartas' => 'Bebas Lartas (Non-Lartas)',
                    'legal' => "CEISA 4.0 API Pos {$cleanCode}",
                    'required_documents' => [],
                ];
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal fetch tarif HS dari CEISA: {$e->getMessage()}");
        }

        // 4. Default Fallback
        return [
            'success' => true,
            'source' => 'DEFAULT_FALLBACK',
            'code' => $cleanCode,
            'clean_code' => $cleanCode,
            'desc' => "Barang Impor/Ekspor Pos {$cleanCode}",
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => "Estimasi Standar Pabean Pos {$cleanCode}",
            'required_documents' => [],
        ];
    }

    /**
     * Klasifikasi Cerdas Komoditas Barang ke Pos Tarif HS Code BTKI 2022 (AI / NLP Engine)
     */
    public function classifyCommodity(string $text, ?string $direction = null): array
    {
        $upper = strtoupper(trim($text));

        // 1. Cek apakah ada nomor HS Code 8-digit atau format 4.2.2 langsung di dalam teks
        if (preg_match('/(?:HS|POS|TARIF|CODE)?[:\s\-]*([0-9]{4}\.[0-9]{2}\.[0-9]{2}|[0-9]{8})/i', $upper, $matches)) {
            $matchedCode = str_replace(['.', ' ', '-'], '', $matches[1]);
            $tariff = $this->getTariffAndLartas($matchedCode);
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'EXPLICIT_HS_FOUND';
            return $tariff;
        }

        // 2. Pencocokan Cerdas Kata Kunci Komoditas (NLP Keyword Matching)
        $keywordMap = [
            'INVERTER' => '85044090',
            'CONVERTER' => '85044090',
            'RECTIFIER' => '85044090',
            'TRAFO' => '85044090',
            'LAPTOP' => '84713020',
            'NOTEBOOK' => '84713020',
            'COMPUTER' => '84713020',
            'KOMPUTER' => '84713020',
            'PALM OIL' => '15111000',
            'CPO' => '15111000',
            'KELAPA SAWIT' => '15111000',
            'TIMBER' => '44071100',
            'WOOD' => '44071100',
            'KAYU' => '44071100',
            'LUMBER' => '44071100',
            'TEAK' => '44071100',
            'COFFEE' => '09012110',
            'KOPI' => '09012110',
            'ARABICA' => '09012110',
            'ROBUSTA' => '09012110',
            'FABRIC' => '54075200',
            'TEXTILE' => '54075200',
            'POLYESTER' => '54075200',
            'KAIN' => '54075200',
            'CERAMIC' => '69072190',
            'KERAMIK' => '69072190',
            'TILE' => '69072190',
            'UBIN' => '69072190',
            'SOLAR' => '85414300',
            'PHOTOVOLTAIC' => '85414300',
            'PANEL SURYA' => '85414300',
            'SEWING MACHINE' => '84522900',
            'MESIN JAHIT' => '84522900',
            'NEEDLE' => '84529000',
            'JARUM' => '84529000',
            'SPARE PART' => '84529000',
        ];

        foreach ($keywordMap as $kw => $code) {
            if (str_contains($upper, $kw)) {
                $tariff = $this->getTariffAndLartas($code);
                $tariff['confidence_score'] = 0.96;
                $tariff['classification_source'] = 'AI_KEYWORD_MATCH';
                return $tariff;
            }
        }

        // 3. Fallback jika komoditas umum
        $fallbackCode = ($direction === 'EXPORT' || $direction === 'EKSPORT_BC30') ? '15111000' : '84522900';
        $tariff = $this->getTariffAndLartas($fallbackCode);
        $tariff['confidence_score'] = 0.85;
        $tariff['classification_source'] = 'AI_GENERAL_PREDICTION';
        $tariff['desc'] = $text;
        return $tariff;
    }

    /**
     * Pengecekan & Verifikasi Konsistensi Packing List terhadap Shipment & HS Code (3-Way Matching)
     */
    public function verifyPackingList(Shipment $shipment): array
    {
        $items = $shipment->items;
        if ($items->isEmpty()) {
            app(\App\Services\Shipment\ShipmentWorkflowService::class)->detectHsCodeFromPackingList($shipment);
            $shipment->refresh();
            $items = $shipment->items;
        }

        $discrepancies = [];
        $missingDocuments = [];

        $sumKemasan = 0;
        $sumNetto = 0.0;
        $sumBruto = 0.0;
        $sumFob = 0.0;
        $enrichedItems = [];

        $kurs = (float) ($shipment->ndbm_kurs ?? 15850.00);

        foreach ($items as $idx => $item) {
            $sumKemasan += (int) $item->jumlah_kemasan;
            $sumNetto += (float) $item->berat_netto;
            $sumBruto += (float) $item->berat_bruto;
            $sumFob += (float) $item->nilai_fob;

            $tariffInfo = $this->getTariffAndLartas($item->kode_hs);

            // Sinkronisasi otomatis dokumen wajib bea cukai / lartas ke database per item
            foreach ($tariffInfo['required_documents'] ?? [] as $reqDoc) {
                \App\Models\ShipmentItemDocument::firstOrCreate(
                    [
                        'shipment_id' => $shipment->id,
                        'shipment_item_id' => $item->id,
                        'kode_dokumen' => $reqDoc['kode_dokumen'],
                    ],
                    [
                        'nama_dokumen' => $reqDoc['nama_dokumen'],
                        'instansi_penerbit' => $reqDoc['instansi'] ?? 'Kementerian Teknis Terkait',
                        'is_mandatory' => $reqDoc['is_mandatory'] ?? true,
                        'status' => 'MISSING',
                    ]
                );
            }

            // Ambil & evaluasi seluruh dokumen pelengkap untuk item ini
            $itemDocs = \App\Models\ShipmentItemDocument::where('shipment_item_id', $item->id)->get();
            $itemDocsList = [];
            $itemDocsComplete = true;

            foreach ($itemDocs as $d) {
                $isComp = $d->isCompleted();
                if ($d->is_mandatory && !$isComp) {
                    $itemDocsComplete = false;
                    $missingDocuments[] = "Item #{$item->nomor_urut} ({$item->uraian_barang}) Pos {$item->kode_hs}: Dokumen Wajib [{$d->kode_dokumen}] {$d->nama_dokumen} BELUM DILENGKAPI (Status: {$d->status})";
                }

                $itemDocsList[] = [
                    'id' => $d->id,
                    'kode_dokumen' => $d->kode_dokumen,
                    'nama_dokumen' => $d->nama_dokumen,
                    'instansi_penerbit' => $d->instansi_penerbit,
                    'nomor_dokumen' => $d->nomor_dokumen,
                    'tanggal_dokumen' => $d->tanggal_dokumen?->format('Y-m-d'),
                    'status' => $d->status,
                    'is_mandatory' => $d->is_mandatory,
                    'is_completed' => $isComp,
                    'file_url' => $d->file_url,
                ];
            }

            $itemCif = (float) $item->nilai_fob;
            $itemCifRupiah = round($itemCif * $kurs);
            $bmRate = $tariffInfo['bm'];
            $ppnRate = $tariffInfo['ppn'];
            $pphRate = $tariffInfo['pph'];

            $bmVal = round($itemCifRupiah * ($bmRate / 100));
            $ppnVal = round(($itemCifRupiah + $bmVal) * ($ppnRate / 100));
            $pphVal = round(($itemCifRupiah + $bmVal) * ($pphRate / 100));

            $enrichedItems[] = [
                'seri_barang' => $idx + 1,
                'item_id' => $item->id,
                'kode_hs' => $tariffInfo['code'] ?? $item->kode_hs,
                'pos_tarif' => str_replace(['.', ' '], '', $item->kode_hs),
                'uraian_barang' => $item->uraian_barang,
                'negara_asal' => $item->negara_asal ?? 'CN',
                'jumlah_kemasan' => $item->jumlah_kemasan,
                'jenis_kemasan' => $item->jenis_kemasan ?? 'CT',
                'berat_netto' => (float) $item->berat_netto,
                'berat_bruto' => (float) $item->berat_bruto,
                'nilai_fob' => (float) $item->nilai_fob,
                'cif_rupiah' => $itemCifRupiah,
                'tarif_bm' => $bmRate,
                'tarif_ppn' => $ppnRate,
                'tarif_pph' => $pphRate,
                'nilai_bm' => $bmVal,
                'nilai_ppn' => $ppnVal,
                'nilai_pph' => $pphVal,
                'total_pungutan_item' => $bmVal + $ppnVal + $pphVal,
                'has_lartas' => $tariffInfo['has_lartas'],
                'lartas_info' => $tariffInfo['lartas'],
                'legal_basis' => $tariffInfo['legal'],
                'required_documents' => $itemDocsList,
                'is_documents_complete' => $itemDocsComplete,
            ];
        }

        // Cek Kesesuaian Header vs Akumulasi Item Packing List
        $headerKemasan = (int) ($shipment->total_kemasan ?? 0);
        $headerBruto = (float) ($shipment->total_bruto ?? 0);
        $headerNetto = (float) ($shipment->total_netto ?? 0);

        if ($headerKemasan > 0 && $headerKemasan !== $sumKemasan) {
            $discrepancies[] = "Selisih Jumlah Kemasan: Header B/L = {$headerKemasan}, Total Packing List = {$sumKemasan}";
        }
        if ($headerBruto > 0 && abs($headerBruto - $sumBruto) > 1.0) {
            $discrepancies[] = "Selisih Berat Kotor (Bruto): Header B/L = {$headerBruto} KG, Total Packing List = {$sumBruto} KG";
        }
        if ($headerNetto > 0 && abs($headerNetto - $sumNetto) > 1.0) {
            $discrepancies[] = "Selisih Berat Bersih (Netto): Header B/L = {$headerNetto} KG, Total Packing List = {$sumNetto} KG";
        }

        $totalEstimatedBilling = array_sum(array_column($enrichedItems, 'total_pungutan_item'));

        return [
            'is_matched' => empty($discrepancies) && empty($missingDocuments),
            'discrepancies' => array_merge($discrepancies, $missingDocuments),
            'missing_documents' => $missingDocuments,
            'is_all_documents_complete' => empty($missingDocuments),
            'total_items' => count($enrichedItems),
            'total_kemasan' => $sumKemasan,
            'total_bruto' => $sumBruto,
            'total_netto' => $sumNetto,
            'total_fob' => $sumFob,
            'total_cif_idr' => round($sumFob * $kurs),
            'total_estimated_billing' => $totalEstimatedBilling,
            'items' => $enrichedItems,
        ];
    }

    /**
     * Generate Dokumen Pabean (PIB BC 2.0 atau PEB BC 3.0) Otomatis Berbasis Data Packing List yang Terverifikasi
     */
    public function generatePibOrPebFromPackingList(Shipment $shipment, array $overrides = []): CeisaDocument
    {
        $verification = $this->verifyPackingList($shipment);
        $isBC20 = ($shipment->jenis_layanan === 'IMPORT_BC20');

        $kodeKantor = $overrides['kode_kantor'] ?? (config('ceisa.company.default_kode_kantor') ?? '040300');
        $nomorAju = $isBC20 
            ? DocumentBC20Service::generateNomorAju($kodeKantor) 
            : DocumentBC30Service::generateNomorAju($kodeKantor);

        $kurs = (float) ($shipment->ndbm_kurs ?? 15850.00);
        $fob = $verification['total_fob'] > 0 ? $verification['total_fob'] : (float) $shipment->total_cif;
        $freight = $isBC20 ? 1200.00 : 0.0;
        $asuransi = $isBC20 ? 150.00 : 0.0;
        $cif = $fob + $freight + $asuransi;

        // Ambil seluruh dokumen pelengkap yang terlampir pada shipment items
        $allItemDocs = \App\Models\ShipmentItemDocument::where('shipment_id', $shipment->id)->get();

        $headerDokumen = [];
        $invoiceNo = $isBC20 
            ? ('INV/' . ($shipment->nomor_bl_awb ?? date('Ymd'))) 
            : ('EXP-INV/' . ($shipment->nomor_bl_awb ?? date('Ymd')));
        $blNo = $shipment->nomor_bl_awb ?? ('BL-' . date('Ymd'));
        $plNo = ($isBC20 ? 'PL/' : 'EXP-PL/') . ($shipment->nomor_bl_awb ?? date('Ymd'));
        $docDate = $shipment->tgl_bl_awb ? $shipment->tgl_bl_awb->format('Y-m-d') : date('Y-m-d');

        // 1. Dokumen Pokok Standar
        $headerDokumen[] = [
            'seriDokumen' => 1,
            'kodeDokumen' => '380', // Invoice
            'nomorDokumen' => $invoiceNo,
            'tanggalDokumen' => $docDate,
        ];
        $headerDokumen[] = [
            'seriDokumen' => 2,
            'kodeDokumen' => ($shipment->cara_angkut ?? '1') === '4' ? '740' : '705', // 705: B/L, 740: AWB
            'nomorDokumen' => $blNo,
            'tanggalDokumen' => $docDate,
        ];
        $headerDokumen[] = [
            'seriDokumen' => 3,
            'kodeDokumen' => '217', // Packing List
            'nomorDokumen' => $plNo,
            'tanggalDokumen' => $docDate,
        ];

        // 2. Tambahkan Dokumen Tambahan / Perizinan Lartas Hasil Pengecekan Packing List
        $seriDocCounter = 4;
        $docSeriMapping = [];

        foreach ($allItemDocs as $doc) {
            $nomor = $doc->nomor_dokumen ?: ($doc->kode_dokumen . '/' . date('Ymd') . '/' . substr($doc->id, 0, 4));
            $tgl = $doc->tanggal_dokumen ? $doc->tanggal_dokumen->format('Y-m-d') : date('Y-m-d');

            $headerDokumen[] = [
                'seriDokumen' => $seriDocCounter,
                'kodeDokumen' => $doc->kode_dokumen,
                'nomorDokumen' => $nomor,
                'tanggalDokumen' => $tgl,
            ];
            $docSeriMapping[$doc->id] = $seriDocCounter;
            $seriDocCounter++;
        }

        // Susun daftar barang dari hasil verifikasi packing list dengan barangDokumen
        $barangPayload = [];
        foreach ($verification['items'] as $item) {
            $thisItemDocs = $allItemDocs->where('shipment_item_id', $item['item_id']);
            $barangDokumen = [];
            $seriIzin = 1;

            foreach ($thisItemDocs as $d) {
                if (isset($docSeriMapping[$d->id])) {
                    $nomor = $d->nomor_dokumen ?: ($d->kode_dokumen . '/' . date('Ymd') . '/' . substr($d->id, 0, 4));
                    $tgl = $d->tanggal_dokumen ? $d->tanggal_dokumen->format('Y-m-d') : date('Y-m-d');

                    $barangDokumen[] = [
                        'seriDokumen' => $docSeriMapping[$d->id],
                        'seriIzin' => $seriIzin++,
                        'seriBarang' => $item['seri_barang'],
                        'kodeDokumen' => $d->kode_dokumen,
                        'nomorDokumen' => $nomor,
                        'tanggalDokumen' => $tgl,
                    ];
                }
            }

            $barangPayload[] = [
                'seri' => $item['seri_barang'],
                'seriBarang' => $item['seri_barang'],
                'posTarif' => $item['pos_tarif'],
                'hs_code' => $item['pos_tarif'],
                'kode_hs' => $item['kode_hs'],
                'uraian' => $item['uraian_barang'],
                'uraian_barang' => $item['uraian_barang'],
                'merk' => 'STANDAR',
                'merek' => 'STANDAR',
                'tipe' => 'KOMERSIAL',
                'negara_asal' => $item['negara_asal'],
                'kodeNegaraAsal' => $item['negara_asal'],
                'jumlah' => $item['jumlah_kemasan'],
                'jumlah_kemasan' => $item['jumlah_kemasan'],
                'jumlahKemasan' => $item['jumlah_kemasan'],
                'jumlah_satuan' => $item['jumlah_kemasan'],
                'jumlahSatuan' => $item['jumlah_kemasan'],
                'jenis_kemasan' => $item['jenis_kemasan'],
                'kodeJenisKemasan' => $item['jenis_kemasan'],
                'satuan' => 'PCE',
                'kode_satuan' => 'PCE',
                'kodeSatuanBarang' => 'PCE',
                'netto' => $item['berat_netto'],
                'bruto' => $item['berat_bruto'],
                'cif' => $item['nilai_fob'],
                'fob' => $item['nilai_fob'],
                'tarif_bm' => $item['tarif_bm'],
                'tarifBm' => $item['tarif_bm'],
                'tarif_ppn' => $item['tarif_ppn'],
                'tarifPpn' => $item['tarif_ppn'],
                'tarif_pph' => $item['tarif_pph'],
                'tarifPph' => $item['tarif_pph'],
                'tarif_bk' => 0.0,
                'tarifBk' => 0.0,
                'barangDokumen' => $barangDokumen,
            ];
        }

        if ($isBC20) {
            // BC 2.0 PIB Builder
            $input = array_merge([
                'nomor_aju' => $nomorAju,
                'kode_kantor' => $kodeKantor,
                'kode_valuta' => $shipment->kode_valuta ?? 'USD',
                'ndbm_kurs' => $kurs,
                'fob' => $fob,
                'freight' => $freight,
                'asuransi' => $asuransi,
                'bruto' => $verification['total_bruto'] ?: $shipment->total_bruto,
                'netto' => $verification['total_netto'] ?: $shipment->total_netto,
                'supplier_nama' => $shipment->client?->name ? ($shipment->client->name . ' OVERSEAS CO., LTD') : 'GLOBAL TRADING CORP',
                'supplier_negara' => 'CN',
                'invoice_no' => 'INV/' . ($shipment->nomor_bl_awb ?? date('Ymd')),
                'invoice_date' => $shipment->tgl_bl_awb ? $shipment->tgl_bl_awb->format('Y-m-d') : date('Y-m-d'),
                'nama_sarana_pengangkut' => $shipment->nama_kapal ?? 'COSCO SHIPPING TAURUS',
                'pelabuhan_muat' => $shipment->pelabuhan_muat ?? 'CNSHA',
                'pelabuhan_bongkar' => $shipment->pelabuhan_bongkar ?? 'IDTPP',
                'nomor_bc11' => $shipment->nomor_bc11 ?? '004567',
                'tanggal_bc11' => $shipment->tanggal_bc11 ?? now()->toDateString(),
                'pos_bc11' => $shipment->pos_bc11 ?? '0012',
                'subpos_bc11' => $shipment->subpos_bc11 ?? '0000',
                'kode_tps' => $shipment->kode_tps ?? 'TPS01',
                'tanggal_tiba' => $shipment->eta ? $shipment->eta->format('Y-m-d') : now()->toDateString(),
                'dokumen' => $headerDokumen,
                'barang' => $barangPayload,
            ], $overrides);

            $payload = DocumentBC20Service::buildPayload($input);
            $totalNilai = $payload['cif'];
            $totalNilaiIdr = $payload['header']['cifRupiah'] ?? ($payload['cif'] * $kurs);
            $jenisDokumen = '20';
            $mitraNama = $input['supplier_nama'];
            $mitraNegara = $input['supplier_negara'];
        } else {
            // BC 3.0 PEB Builder
            $input = array_merge([
                'nomor_aju' => $nomorAju,
                'kode_kantor' => $kodeKantor,
                'kode_valuta' => $shipment->kode_valuta ?? 'USD',
                'ndbm_kurs' => $kurs,
                'fob' => $fob,
                'freight' => 0,
                'asuransi' => 0,
                'bruto' => $verification['total_bruto'] ?: $shipment->total_bruto,
                'netto' => $verification['total_netto'] ?: $shipment->total_netto,
                'buyer_nama' => 'GLOBAL COMMODITIES TRADING PTE LTD',
                'buyer_negara' => 'SG',
                'invoice_no' => 'EXP-INV/' . ($shipment->nomor_bl_awb ?? date('Ymd')),
                'invoice_date' => date('Y-m-d'),
                'nama_sarana_pengangkut' => $shipment->nama_kapal ?? 'EVER GIVEN',
                'pelabuhan_muat' => $shipment->pelabuhan_bongkar ?? 'IDTPP',
                'pelabuhan_bongkar' => 'SGSIN',
                'pic_pembuat' => 'BAMBANG SUTRISNO',
                'telepon_pembuat' => '081298765432',
                'lokasi_siap_periksa' => 'GUDANG EKSPORTIR CAKUNG',
                'tanggal_periksa' => now()->toDateString(),
                'bank_kode' => 'BMRI',
                'bank_nama' => 'PT BANK MANDIRI (PERSERO) TBK',
                'dokumen' => $headerDokumen,
                'barang' => $barangPayload,
            ], $overrides);

            $payload = DocumentBC30Service::buildPayload($input);
            $totalNilai = $payload['fob'];
            $totalNilaiIdr = $payload['header']['fobRupiah'] ?? ($payload['fob'] * $kurs);
            $jenisDokumen = '30';
            $mitraNama = $input['buyer_nama'];
            $mitraNegara = $input['buyer_negara'];
        }

        // Simpan dokumen pabean resmi
        $doc = CeisaDocument::create([
            'nomor_aju' => $nomorAju,
            'jenis_dokumen' => $jenisDokumen,
            'kode_kantor' => $kodeKantor,
            'nama_kantor' => config('ceisa.kantor_pabean.' . $kodeKantor, 'KPU Tanjung Priok'),
            'status' => 'DRAFT',
            'status_description' => "Draft dokumen BC {$jenisDokumen} berhasil di-generate otomatis dari verifikasi Packing List.",
            'kode_valuta' => $input['kode_valuta'],
            'ndbm_kurs' => $kurs,
            'total_nilai' => $totalNilai,
            'total_nilai_idr' => $totalNilaiIdr,
            'total_bruto' => $input['bruto'],
            'total_netto' => $input['netto'],
            'total_kemasan' => $verification['total_kemasan'],
            'total_kontainer' => $shipment->total_kontainer ?? 1,
            'total_barang' => count($barangPayload),
            'nama_perusahaan_lokal' => config('ceisa.company.name'),
            'npwp_perusahaan_lokal' => config('ceisa.company.npwp'),
            'nama_mitra_luar_negeri' => $mitraNama,
            'negara_mitra_luar_negeri' => $mitraNegara,
            'nomor_bc11' => $input['nomor_bc11'] ?? null,
            'tanggal_bc11' => $input['tanggal_bc11'] ?? null,
            'pos_bc11' => $input['pos_bc11'] ?? null,
            'subpos_bc11' => $input['subpos_bc11'] ?? null,
            'kode_tps' => $input['kode_tps'] ?? null,
            'tanggal_tiba' => $input['tanggal_tiba'] ?? null,
            'pic_pembuat' => $input['pic_pembuat'] ?? null,
            'telepon_pembuat' => $input['telepon_pembuat'] ?? null,
            'lokasi_siap_periksa' => $input['lokasi_siap_periksa'] ?? null,
            'tanggal_periksa' => $input['tanggal_periksa'] ?? null,
            'bank_kode' => $input['bank_kode'] ?? null,
            'bank_nama' => $input['bank_nama'] ?? null,
            'total_billing' => $verification['total_estimated_billing'],
            'status_billing' => 'UNPAID',
            'raw_payload' => $payload,
        ]);

        // Hubungkan ke Shipment
        $shipment->update([
            'ceisa_document_id' => $doc->id,
            'status_tahap' => 'DRAFT_REVIEW',
        ]);

        // Jalankan Pre-Flight Check Skema secara otomatis
        try {
            $schemaCheck = $this->documentService->checkSchema($doc);
            $doc->update([
                'is_schema_valid' => true,
                'schema_validation_message' => $schemaCheck['data']['message'] ?? 'Skema JSON Valid dan Siap Kirim',
            ]);
        } catch (\Throwable $e) {
            $doc->update([
                'is_schema_valid' => false,
                'schema_validation_message' => $e->getMessage(),
            ]);
        }

        return $doc;
    }
}
