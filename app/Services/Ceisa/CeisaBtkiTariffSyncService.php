<?php

namespace App\Services\Ceisa;

use App\Models\HsCode;
use App\Models\HsRegulation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CeisaBtkiTariffSyncService
{
    protected CeisaClientService $client;

    public function __construct(CeisaClientService $client)
    {
        $this->client = $client;
    }

    /**
     * Master Data Resmi BTKI 2022 (Buku Tarif Kepabeanan Indonesia - PMK No. 26/PMK.010/2022)
     * Mengkover pos-pos tarif impor/ekspor utama Indonesia beserta Bea Masuk, PPN 11%, PPh 2.5%,
     * Bea Keluar (BK), Satuan, Status Lartas, dan Dokumen Persyaratan Pabean.
     */
    public static function getBtkiMasterTariffRegistry(): array
    {
        return [
            // =========================================================================
            // BAB 84: MESIN & PERALATAN MEKANIS (HEAVY EQUIPMENT, TOOLS, SPARE PARTS)
            // =========================================================================
            [
                'code' => '8430.69.00',
                'description' => 'Hydraulic Breaker / Mesin Pemecah Batu Hidraulik untuk dipasang pada Ekskavator (Non-self-propelled)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'SET',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas). Barang modal baru bebas impor tanpa izin khusus.',
                'btki' => 'BTKI 2022 Pos 8430.69.00 - Mesin untuk mengerjakan tanah, mineral atau bijih',
                'regulations' => [],
            ],
            [
                'code' => '8431.49.90',
                'description' => 'Bagian dan Suku Cadang Hydraulic Breaker Ekskavator (Chisel, Pin Rod, Side Bolt, Bushing, Piston, Piping Kit)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas). Suku cadang mesin pemecah batu.',
                'btki' => 'BTKI 2022 Pos 8431.49.90 - Bagian yang cocok digunakan semata-mata atau terutama pada mesin dari pos 84.30',
                'regulations' => [],
            ],
            [
                'code' => '8467.11.00',
                'description' => 'Pneumatic Pick / Alat Pemecah Beton Pneumatik Genggam (Jack Hammer Tipe Rotasi & Tanpa Rotasi: G10, G15, G20, TCA-7, TPB60)',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8467.11.00 - Perkakas pneumatik untuk digunakan dengan tangan',
                'regulations' => [],
            ],
            [
                'code' => '8467.92.00',
                'description' => 'Suku Cadang Pneumatic Pick & Breaker Bobok (Cylinder, Air Piston, Valve, Front Head, Handle, Retainer Spring)',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8467.92.00 - Bagian dari perkakas pneumatik pos 84.67',
                'regulations' => [],
            ],
            [
                'code' => '8427.90.00',
                'description' => 'Truk Pengangkat Mekanis Non-Bertenaga (Hand Pallet Truck Manual, Hydraulic Hand Stacker, Lift Truck)',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8427.90.00 - Truk kerja lainnya yang dilengkapi alat pengangkat',
                'regulations' => [],
            ],
            [
                'code' => '8431.20.00',
                'description' => 'Bagian dan Komponen Hand Pallet Truck / Hand Stacker (Fork, Rangka Baja, Handle Kemudi, Link Rod, Torsion Bar)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8431.20.00 - Bagian dari mesin pos 84.27',
                'regulations' => [],
            ],
            [
                'code' => '8412.21.00',
                'description' => 'Mesin dan Motor Hidraulik Aksi Linier (Silinder Hidraulik / Hydraulic Pump Cylinder Unit Hand Pallet & Stacker)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8412.21.00 - Motor hidraulik aksi linier (silinder)',
                'regulations' => [],
            ],
            [
                'code' => '8481.80.99',
                'description' => 'Katup, Kran, dan Perlengkapan Pipa Saluran Lainnya (Hydraulic Pressure Release Valve, Ball Valve, Control Valve)',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8481.80.99 - Kran, klep, dan peralatan serupa lainnya',
                'regulations' => [],
            ],
            [
                'code' => '8482.10.00',
                'description' => 'Bantalan Peluru Baja (Ball Bearings) untuk Roda, As Mesin, dan Peralatan Mekanis',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8482.10.00 - Bantalan peluru (ball bearings)',
                'regulations' => [],
            ],
            [
                'code' => '8482.20.00',
                'description' => 'Bantalan Rol Tirus (Tapered Roller Bearings Termasuk Rakitan Kerucut dan Rol)',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8482.20.00 - Bantalan rol tirus',
                'regulations' => [],
            ],
            [
                'code' => '8479.89.40',
                'description' => 'Mesin dan Peralatan Mekanis dengan Fungsi Individual yang Tidak Dirinci di Bab 84 (Peralatan Industri Khusus)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8479.89.40 - Mesin dan peralatan mekanis lainnya',
                'regulations' => [],
            ],
            [
                'code' => '8452.29.00',
                'description' => 'Mesin Jahit Industri Non-Otomatik Lengkap dengan Meja, Dinamo, dan Penyangga',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'SET',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8452.29.00 - Mesin jahit industri lainnya',
                'regulations' => [],
            ],
            [
                'code' => '8452.90.00',
                'description' => 'Bagian dan Suku Cadang Mesin Jahit Industri (Jarum Jahit, Bobbin, Shuttle Hook, Looper, Presser Foot)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'BOX',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8452.90.00 - Bagian dari mesin jahit pos 84.52',
                'regulations' => [],
            ],
            [
                'code' => '8471.30.20',
                'description' => 'Mesin Pengolah Data Otomatis Portabel / Komputer Jinjing (Laptop, Notebook, Ultrabook Berat < 10 kg)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Post-Border (Sertifikasi Standar SDPPI Kominfo & Persetujuan Impor Kemendag)',
                'btki' => 'BTKI 2022 Pos 8471.30.20 - Laptop / Komputer jinjing',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor (PI Elektronik Kemendag)'],
                        'required_doc' => 'Persetujuan Impor (PI Elektronik)',
                        'source_regulation' => 'Permendag No. 36/2023 jo. Permendag No. 8/2024 tentang Kebijakan dan Pengaturan Impor',
                        'verified' => true,
                    ],
                    [
                        'doc_condition' => ['code' => '853', 'title' => 'Sertifikat Standar Perangkat Telekomunikasi (SDPPI)'],
                        'required_doc' => 'Sertifikat SDPPI Kominfo',
                        'source_regulation' => 'Permenkominfo No. 7/2018 tentang Sertifikasi Alat dan/atau Perangkat Telekomunikasi',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '8471.49.00',
                'description' => 'Server Komputer dan Unit Pemroses Data Digital Terpusat Lainnya',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Post-Border (Laporan Surveyor Kemendag & Sertifikasi SDPPI Kominfo)',
                'btki' => 'BTKI 2022 Pos 8471.49.00 - Unit pengolah data digital lainnya',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '861', 'title' => 'Laporan Surveyor Kemendag (LS Elektronik)'],
                        'required_doc' => 'Laporan Surveyor Kemendag',
                        'source_regulation' => 'Permendag No. 8/2024',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '8413.70.42',
                'description' => 'Pompa Air Sentrifugal Berpenggerak Motor Listrik untuk Irigasi dan Industri',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8413.70.42 - Pompa sentrifugal lainnya',
                'regulations' => [],
            ],
            [
                'code' => '8414.80.14',
                'description' => 'Kompresor Udara Bertekanan Tinggi Tipe Piston Industri (Air Compressor)',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8414.80.14 - Kompresor udara lainnya',
                'regulations' => [],
            ],
            [
                'code' => '8415.10.10',
                'description' => 'Mesin Pengatur Suhu Udara (Air Conditioner / AC) Split Wall Rumah Tangga & Komersial',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'SET',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (SNI Wajib Kemenperin & Laporan Surveyor Kemendag)',
                'btki' => 'BTKI 2022 Pos 8415.10.10 - AC split',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '810', 'title' => 'SNI Wajib / Sertifikat Produk Penggunaan Tanda SNI (SPPT SNI)'],
                        'required_doc' => 'SPPT SNI Kemenperin',
                        'source_regulation' => 'Permenperin No. 19/2021',
                        'verified' => true,
                    ],
                ],
            ],

            // =========================================================================
            // BAB 85: MESIN, PERALATAN ELEKTRIK, POWER, & BATERAI
            // =========================================================================
            [
                'code' => '8504.40.90',
                'description' => 'Static Converters / Power Supply Units / Adaptor Listrik AC-DC Pengisi Daya Perangkat Industri',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Post-Border (SNI Wajib & Laporan Surveyor / LS Kemendag)',
                'btki' => 'BTKI 2022 Pos 8504.40.90 - Konverter statis lainnya',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '861', 'title' => 'Laporan Surveyor (LS Kemendag)'],
                        'required_doc' => 'Laporan Surveyor Kemendag',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '8504.40.30',
                'description' => 'Inverter Solar Panel / Konverter Statis Pengubah Arus DC ke AC Tenaga Surya',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8504.40.30 - Inverter untuk tenaga surya',
                'regulations' => [],
            ],
            [
                'code' => '8502.11.00',
                'description' => 'Genset Listrik Berpenggerak Mesin Diesel dengan Output Tidak Melebihi 75 kVA',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8502.11.00 - Perangkat pembangkit listrik dengan mesin diesel < 75 kVA',
                'regulations' => [],
            ],
            [
                'code' => '8507.60.10',
                'description' => 'Akumulator Listrik / Baterai Lithium-Ion untuk Perangkat Elektronik Portabel',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (SNI Wajib Baterai Litium & Izin Ditjen KPAII Kemenperin)',
                'btki' => 'BTKI 2022 Pos 8507.60.10 - Akumulator lithium-ion',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '810', 'title' => 'SNI Wajib Baterai Sekunder Lithium'],
                        'required_doc' => 'SPPT SNI Kemenperin',
                        'source_regulation' => 'Permenperin No. 41/2021',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '8517.13.00',
                'description' => 'Telepon Pintar (Smartphones / Handphone 4G & 5G Seluler)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Ketat (Persetujuan Impor PI Kemendag, Tanda Pendaftaran IMEI CEIR, & SDPPI)',
                'btki' => 'BTKI 2022 Pos 8517.13.00 - Smartphone telepon seluler',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor (PI HKT Kemendag)'],
                        'required_doc' => 'Persetujuan Impor HKT',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                    [
                        'doc_condition' => ['code' => '853', 'title' => 'Sertifikasi SDPPI Kominfo & Registrasi IMEI'],
                        'required_doc' => 'Sertifikat SDPPI & IMEI',
                        'source_regulation' => 'Permenkominfo No. 1/2020',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '8541.43.00',
                'description' => 'Sel Fotovoltaik Dirakit Menjadi Modul atau Dibuat Menjadi Panel (Solar PV Panel)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Post-Border (SNI Wajib Modul Fotovoltaik Silikon Kemen ESDM)',
                'btki' => 'BTKI 2022 Pos 8541.43.00 - Sel fotovoltaik dirakit menjadi modul',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '810', 'title' => 'Sertifikat Kesesuaian SNI Modul Fotovoltaik'],
                        'required_doc' => 'Sertifikat SNI ESDM',
                        'source_regulation' => 'Permen ESDM No. 2/2021',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '8544.49.41',
                'description' => 'Kabel Listrik Berpenghantar Tembaga Berinsulasi untuk Tegangan Tidak Melebihi 1.000 V',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'MTR',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (SNI Wajib Kabel Listrik Kemenperin & Laporan Surveyor)',
                'btki' => 'BTKI 2022 Pos 8544.49.41 - Kawat dan kabel listrik tembaga',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '810', 'title' => 'SNI Wajib Kabel Listrik'],
                        'required_doc' => 'SPPT SNI Kemenperin',
                        'source_regulation' => 'Permenperin No. 22/2020',
                        'verified' => true,
                    ],
                ],
            ],

            // =========================================================================
            // BAB 72 & 73: BESI, BAJA, PIPA, FITTING, BAUT, & FASTENERS
            // =========================================================================
            [
                'code' => '7318.15.90',
                'description' => 'Baut dan Sekrup Lainnya Berkepala dari Besi atau Baja, Berulir (Side Bolt, Through Bolt, Hex Bolt Breaker)',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 7318.15.90 - Baut dan sekrup berkepala lainnya dari besi atau baja',
                'regulations' => [],
            ],
            [
                'code' => '7318.16.00',
                'description' => 'Mur dari Besi atau Baja (Hex Nuts, Lock Nuts, Flange Nuts)',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 7318.16.00 - Mur dari besi atau baja',
                'regulations' => [],
            ],
            [
                'code' => '7318.22.00',
                'description' => 'Ring Pelat Cincin / Washer dari Besi atau Baja Tidak Berulir',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 7318.22.00 - Cincin (washers) lainnya',
                'regulations' => [],
            ],
            [
                'code' => '7326.90.99',
                'description' => 'Hose Clamp / Klem Pengikat Selang Logam Besi atau Baja untuk Selang Hidraulik & Pneumatik',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 7326.90.99 - Barang lainnya dari besi atau baja',
                'regulations' => [],
            ],
            [
                'code' => '7307.99.10',
                'description' => 'Fitting Sambungan Tabung atau Pipa dari Besi atau Baja (Hydraulic Nipple, Elbow, Tee Fitting Tekanan Tinggi)',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 7307.99.10 - Sambungan pipa lainnya dari besi atau baja',
                'regulations' => [],
            ],
            [
                'code' => '7208.38.00',
                'description' => 'Produk Canai Lantaian dari Besi atau Baja Bukan Paduan dalam Bentuk Gulungan (Hot Rolled Coil / HRC Tebal >= 3mm)',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'TNE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Ketat (Persetujuan Impor Besi Baja Kemendag & Laporan Surveyor)',
                'btki' => 'BTKI 2022 Pos 7208.38.00 - Baja canai panas tebal 3 mm s.d. 4.75 mm',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor (PI Besi Baja Kemendag)'],
                        'required_doc' => 'Persetujuan Impor Besi Baja',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                    [
                        'doc_condition' => ['code' => '861', 'title' => 'Laporan Surveyor (LS Besi Baja Kemendag)'],
                        'required_doc' => 'Laporan Surveyor Kemendag',
                        'source_regulation' => 'Permendag No. 8/2024',
                        'verified' => true,
                    ],
                ],
            ],

            // =========================================================================
            // BAB 82 & 83: PERKAKAS PAHAT, CASTER WHEEL, & HARDWARE LOGAM
            // =========================================================================
            [
                'code' => '8207.90.00',
                'description' => 'Pahat Perkakas Tangan Pneumatik Bobok (Interchangeable Chisel Tools, Moil Point, Flat Chisel Breaker)',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8207.90.00 - Perkakas yang dapat dipertukarkan lainnya',
                'regulations' => [],
            ],
            [
                'code' => '8302.20.00',
                'description' => 'Caster Wheels / Roda Castor Logam & Braket untuk Troli, Forklift, Hand Pallet, Perabot Kerja',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8302.20.00 - Castors',
                'regulations' => [],
            ],

            // =========================================================================
            // BAB 39 & 40: KARET, GASKET SEAL, RESIN, & PRODUK PLASTIK
            // =========================================================================
            [
                'code' => '4016.93.20',
                'description' => 'Gasket, Ring O-Ring, dan Cincin Perapat (Rubber Seal Vulkanisir Cincin Perapat Minyak & Fluida Hidraulik)',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'SET',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 4016.93.20 - Gasket, ring dan perapat lainnya dari karet vulkanisir',
                'regulations' => [],
            ],
            [
                'code' => '3926.90.99',
                'description' => 'Barang Plastik Lainnya Termasuk Komponen Poliuretana (Polyurethane Guides, Wear Pads, PU Bushing)',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 3926.90.99 - Barang plastik lainnya',
                'regulations' => [],
            ],
            [
                'code' => '3901.10.12',
                'description' => 'Polietilena Mempunyai Berat Jenis Kurang Dari 0.94 dalam Bentuk Pelet (LDPE Resin Granule)',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'KGM',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Persetujuan Impor Bahan Baku Plastik Kemendag & LS Kemendag)',
                'btki' => 'BTKI 2022 Pos 3901.10.12 - Polietilena berat jenis < 0.94 bentuk pelet',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor Plastik Kemendag'],
                        'required_doc' => 'Persetujuan Impor Bahan Plastik',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                ],
            ],

            // =========================================================================
            // BAB 68 & 70: ASPAL BITUMEN, WATERPROOFING, & FIBERGLASS
            // =========================================================================
            [
                'code' => '6807.10.00',
                'description' => 'SBS / Granule Waterproofing Membrane Gulungan Aspal Bitumen Kedap Air untuk Bangunan',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'ROL',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 6807.10.00 - Barang dari aspal atau bahan serupa dalam bentuk gulungan',
                'regulations' => [],
            ],
            [
                'code' => '7019.61.00',
                'description' => 'Fiberglass Mesh Kain Tenun Tertutup dari Serat Kaca Penguat Struktur Bangunan & Waterproofing',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'ROL',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 7019.61.00 - Kain tenun tertutup dari serat kaca',
                'regulations' => [],
            ],

            // =========================================================================
            // BAB 87: KENDARAAN, MOBIL LISTRIK, RODA KENDARAAN, & ALAT ANGKUT
            // =========================================================================
            [
                'code' => '8716.90.90',
                'description' => 'Roda Polyurethane (PU Wheel), Roda Nilon, & Roda Karet untuk Kereta Angkut Manual dan Pallet Mover',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8716.90.90 - Bagian dari trailer dan kendaraan tidak digerakkan secara mekanis',
                'regulations' => [],
            ],
            [
                'code' => '8703.80.99',
                'description' => 'Mobil Penumpang Tenaga Listrik Murni (Battery Electric Vehicle / BEV) CBU',
                'duty_bm' => 0.0, // Insentif Pajak Mobil Listrik PMK
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Persetujuan Impor Kemendag, TPT Kemenperin, Uji Tipe SUT Kemenhub)',
                'btki' => 'BTKI 2022 Pos 8703.80.99 - Kendaraan bermotor listrik murni',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor Kendaraan Bermotor Kemendag'],
                        'required_doc' => 'Persetujuan Impor Kendaraan Bermotor',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                    [
                        'doc_condition' => ['code' => '850', 'title' => 'Sertifikat Uji Tipe (SUT) Kementerian Perhubungan'],
                        'required_doc' => 'SUT Kemenhub',
                        'source_regulation' => 'Permenhub No. 44/2020',
                        'verified' => true,
                    ],
                ],
            ],

            // =========================================================================
            // KOMODITAS PERTANIAN, PERKEBUNAN & EKSPOR (CPO, KOPI, KAYU SVLK)
            // =========================================================================
            [
                'code' => '1511.10.00',
                'description' => 'Crude Palm Oil (CPO) / Minyak Kelapa Sawit Mentah',
                'duty_bm' => 0.0,
                'duty_ppn' => 0.0,
                'duty_pph' => 0.0,
                'duty_bk' => 52.0, // Tarif Bea Keluar Ekspor PMK 38/2024 (USD/MT)
                'satuan' => 'TNE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Ekspor (Eksportir Terdaftar / ET Sawit Kemendag & Pungutan Sawit BPDPKS)',
                'btki' => 'BTKI 2022 Pos 1511.10.00 - Minyak kelapa sawit mentah',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '872', 'title' => 'Eksportir Terdaftar (ET) Sawit Kemendag'],
                        'required_doc' => 'ET Sawit Kemendag',
                        'source_regulation' => 'Permendag No. 39/2022 tentang Ekspor CPO dan Produk Turunannya',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '0901.21.10',
                'description' => 'Kopi Arabika Disangrai (Roasted Arabica Coffee Beans) Kualitas Ekspor/Impor',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'KGM',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Karantina Tumbuhan (Sertifikat Fitosanitari / PC Barantin)',
                'btki' => 'BTKI 2022 Pos 0901.21.10 - Kopi disangrai tidak dihilangkan kafeinnya',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '831', 'title' => 'Sertifikat Fitosanitari (Phytosanitary Certificate) Barantin'],
                        'required_doc' => 'Sertifikat Karantina Barantin (PC)',
                        'source_regulation' => 'UU No. 21/2019 tentang Karantina Hewan, Ikan, dan Tumbuhan',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '4407.11.00',
                'description' => 'Kayu Olahan Coniferous Digergaji Memanjang (Sawn Timber) Bersertifikasi Kelestarian Hutan',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 15.0, // Bea Keluar Ekspor Kayu
                'satuan' => 'MTQ',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Ekspor (Dokumen V-Legal / SVLK KLHK & Laporan Surveyor Kemendag)',
                'btki' => 'BTKI 2022 Pos 4407.11.00 - Kayu coniferous digergaji',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '880', 'title' => 'Dokumen V-Legal / SVLK KLHK'],
                        'required_doc' => 'Dokumen V-Legal SVLK',
                        'source_regulation' => 'Permen LHK No. 8/2021 tentang Pengelolaan Hutan Lestari',
                        'verified' => true,
                    ],
                    [
                        'doc_condition' => ['code' => '861', 'title' => 'Laporan Surveyor (LS) Ekspor Kayu'],
                        'required_doc' => 'Laporan Surveyor Ekspor Kayu',
                        'source_regulation' => 'Permendag No. 23/2023',
                        'verified' => true,
                    ],
                ],
            ],

            // =========================================================================
            // BAB 30 & 90: FARMASI & ALAT KESEHATAN
            // =========================================================================
            [
                'code' => '3004.90.99',
                'description' => 'Obat-Obatan Medis Jadi Dikemas untuk Penjualan Eceran (Farmasi)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'BOX',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Izin Edar BPOM RI & Surat Keterangan Impor SKI BPOM)',
                'btki' => 'BTKI 2022 Pos 3004.90.99 - Obat jadi lainnya',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '820', 'title' => 'Surat Keterangan Impor (SKI) BPOM RI'],
                        'required_doc' => 'SKI BPOM RI',
                        'source_regulation' => 'PerBPOM No. 27/2020 tentang Pemasukan Obat dan Makanan',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '9018.90.90',
                'description' => 'Instrumen dan Peralatan Medis, Bedah, dan Kedokteran Gigi (Alat Kesehatan / Alkes)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Izin Edar Alat Kesehatan Kementerian Kesehatan RI)',
                'btki' => 'BTKI 2022 Pos 9018.90.90 - Instrumen medis lainnya',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '821', 'title' => 'Izin Edar Alat Kesehatan Kemenkes RI'],
                        'required_doc' => 'Izin Edar Alkes Kemenkes',
                        'source_regulation' => 'Permenkes No. 62/2017 tentang Izin Edar Alat Kesehatan',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '9026.20.10',
                'description' => 'Alat Ukur Tekanan / Manometer (Pressure Gauge) untuk Sistem Hidraulik dan Industri',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 9026.20.10 - Manometer untuk mengukur tekanan',
                'regulations' => [],
            ],

            // =========================================================================
            // BAB 61, 62 & 64: TEKSTIL, GARMEN, & ALAS KAKI
            // =========================================================================
            [
                'code' => '6109.10.10',
                'description' => 'Kaos T-Shirt dan Singlet dari Katun untuk Pria atau Anak Laki-Laki (Pakaian Rajutan)',
                'duty_bm' => 20.0, // Bea Masuk Tekstil Pengamanan
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Persetujuan Impor Tekstil Kemendag, Laporan Surveyor, & Bea Masuk Tindakan Pengamanan BMTP)',
                'btki' => 'BTKI 2022 Pos 6109.10.10 - T-shirt katun',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor Pakaian Jadi Kemendag'],
                        'required_doc' => 'PI Tekstil & Produk Tekstil',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                    [
                        'doc_condition' => ['code' => '861', 'title' => 'Laporan Surveyor (LS Tekstil)'],
                        'required_doc' => 'Laporan Surveyor LS Tekstil',
                        'source_regulation' => 'Permendag No. 8/2024',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '6404.11.90',
                'description' => 'Alas Kaki dengan Sol Luar dari Karet atau Plastik dan Bagian Atas dari Bahan Tekstil (Sepatu Olahraga / Sneakers)',
                'duty_bm' => 25.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PAR',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Persetujuan Impor Alas Kaki Kemendag & Laporan Surveyor)',
                'btki' => 'BTKI 2022 Pos 6404.11.90 - Sepatu olahraga sol karet',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor Alas Kaki Kemendag'],
                        'required_doc' => 'Persetujuan Impor Alas Kaki',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                ],
            ],

            // =========================================================================
            // BAB 10, 02, 03, 07, 08, 17: PANGAN, PERTANIAN, HORTIKULTURA, & DAGING
            // =========================================================================
            [
                'code' => '1006.30.90',
                'description' => 'Beras Semi-Giling atau Giling Seluruhnya, Dipoles atau Dikilapkan (Beras Premium / Medium)',
                'duty_bm' => 0.0, // Dikenakan Tarif Spesifik Rp 450/kg
                'duty_ppn' => 0.0, // PPN Dibebaskan (Bapok)
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'TNE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Ketat (Persetujuan Impor Beras Kemendag & Sertifikat Fitosanitari Barantin)',
                'btki' => 'BTKI 2022 Pos 1006.30.90 - Beras giling lainnya',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor Beras Kemendag'],
                        'required_doc' => 'PI Beras Kemendag',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                    [
                        'doc_condition' => ['code' => '831', 'title' => 'Sertifikat Fitosanitari Barantin'],
                        'required_doc' => 'Sertifikat Karantina Tumbuhan (PC)',
                        'source_regulation' => 'UU No. 21/2019 Barantin',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '1001.99.12',
                'description' => 'Biji Gandum Lainnya untuk Bahan Baku Industri Tepung Terigu (Meslin)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'TNE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Karantina Tumbuhan (Sertifikat Fitosanitari / PC Barantin)',
                'btki' => 'BTKI 2022 Pos 1001.99.12 - Biji gandum bahan baku tepung',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '831', 'title' => 'Sertifikat Fitosanitari Barantin'],
                        'required_doc' => 'Sertifikat Karantina Barantin (PC)',
                        'source_regulation' => 'UU No. 21/2019 Barantin',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '1201.90.00',
                'description' => 'Biji Kedelai Pecah Maupun Tidak, Bukan untuk Benih (Bahan Baku Tempe & Tahu)',
                'duty_bm' => 0.0,
                'duty_ppn' => 0.0, // Bahan Pokok Dibebaskan
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'TNE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Karantina Tumbuhan (Sertifikat Fitosanitari / PC Barantin)',
                'btki' => 'BTKI 2022 Pos 1201.90.00 - Biji kedelai lainnya',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '831', 'title' => 'Sertifikat Fitosanitari Barantin'],
                        'required_doc' => 'Sertifikat Karantina Barantin (PC)',
                        'source_regulation' => 'UU No. 21/2019 Barantin',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '0201.30.00',
                'description' => 'Daging Hewan Jenis Lembu Segar atau Dingin Tanpa Tulang (Daging Sapi Boneless Impor)',
                'duty_bm' => 5.0,
                'duty_ppn' => 0.0, // PPN Bebas
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'KGM',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Persetujuan Impor Daging Kemendag, Rekomendasi Kementan, & Sertifikat Veteriner Karantina Barantin)',
                'btki' => 'BTKI 2022 Pos 0201.30.00 - Daging sapi tanpa tulang segar/dingin',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor Produk Hewan Kemendag'],
                        'required_doc' => 'PI Hewan Kemendag',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                    [
                        'doc_condition' => ['code' => '830', 'title' => 'Sertifikat Pelepasan Karantina Hewan Barantin (KH-14)'],
                        'required_doc' => 'Sertifikat Karantina Hewan',
                        'source_regulation' => 'UU No. 21/2019 Barantin',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '0703.20.90',
                'description' => 'Bawang Putih Segar atau Dingin Lainnya (Garlic)',
                'duty_bm' => 5.0,
                'duty_ppn' => 0.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'KGM',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Rekomendasi Impor Produk Hortikultura RIPH Kementan & Persetujuan Impor Kemendag)',
                'btki' => 'BTKI 2022 Pos 0703.20.90 - Bawang putih segar',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor Bawang Putih Kemendag'],
                        'required_doc' => 'PI Bawang Putih',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '1701.14.00',
                'description' => 'Gula Tebu Mentah Lainnya Tanpa Tambahan Bahan Perasa atau Pewarna (Raw Sugar untuk Rafinasi)',
                'duty_bm' => 0.0, // Tarif Spesifik
                'duty_ppn' => 0.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'TNE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Persetujuan Impor Gula Kemendag & Neraca Komoditas)',
                'btki' => 'BTKI 2022 Pos 1701.14.00 - Gula tebu mentah',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor Gula Kemendag'],
                        'required_doc' => 'PI Gula Kemendag',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                ],
            ],

            // =========================================================================
            // BAB 27: ENERGI, MINYAK, BATU BARA, & BAHAN BAKAR
            // =========================================================================
            [
                'code' => '2701.12.10',
                'description' => 'Batu Bara Bituminous Kokas (Coking Coal) Kualitas Ekspor',
                'duty_bm' => 0.0,
                'duty_ppn' => 0.0,
                'duty_pph' => 1.5,
                'duty_bk' => 0.0,
                'satuan' => 'TNE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Ekspor (Eksportir Terdaftar ET Batu Bara Kemendag & Laporan Surveyor ESDM)',
                'btki' => 'BTKI 2022 Pos 2701.12.10 - Batu bara bituminous kokas',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '872', 'title' => 'Eksportir Terdaftar (ET) Pertambangan Batu Bara Kemendag'],
                        'required_doc' => 'ET Batu Bara Kemendag',
                        'source_regulation' => 'Permendag No. 23/2023',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '2710.12.21',
                'description' => 'Minyak Bensin untuk Bahan Bakar Motor dengan Nilai Oktan RON 92 ke Atas (Bahan Bakar Pertamax)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'LTR',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Persetujuan Impor Minyak Bumi & Gas Bumi Kementerian ESDM)',
                'btki' => 'BTKI 2022 Pos 2710.12.21 - Bensin motor RON >= 92',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Izin Usaha Niaga Umum / Impor Migas Kementerian ESDM'],
                        'required_doc' => 'Izin Impor ESDM',
                        'source_regulation' => 'Permen ESDM No. 29/2017',
                        'verified' => true,
                    ],
                ],
            ],

            // =========================================================================
            // BAB 28, 29, 31, 33, 34, 35: BAHAN KIMIA, PUPUK, KOSMETIK, & SABUN
            // =========================================================================
            [
                'code' => '2924.29.90',
                'description' => 'Senyawa Amida Asiklik Parasetamol / Asetaminofen Murni (Bahan Baku Farmasi)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'KGM',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Surat Keterangan Impor SKI BPOM & Pengawasan Farmasi)',
                'btki' => 'BTKI 2022 Pos 2924.29.90 - Senyawa amida lainnya',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '820', 'title' => 'Surat Keterangan Impor (SKI Bahan Baku) BPOM RI'],
                        'required_doc' => 'SKI BPOM RI',
                        'source_regulation' => 'PerBPOM No. 27/2020',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '3102.10.00',
                'description' => 'Pupuk Urea Mengandung Nitrogen Lebih dari 45% Menurut Beratnya (Bahan Baku Pertanian)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'TNE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Persetujuan Impor/Ekspor Pupuk Kementan & SNI Wajib)',
                'btki' => 'BTKI 2022 Pos 3102.10.00 - Pupuk urea',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '810', 'title' => 'SNI Wajib Pupuk Urea Kemenperin'],
                        'required_doc' => 'SPPT SNI Kemenperin',
                        'source_regulation' => 'Permenperin No. 16/2016',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '3105.20.00',
                'description' => 'Pupuk Mineral atau Kimia Mengandung Tiga Unsur Nitrogen, Fosfor, dan Kalium (NPK Majemuk)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'TNE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (SNI Wajib Pupuk NPK Padat Kemenperin & Pendaftaran Kementan)',
                'btki' => 'BTKI 2022 Pos 3105.20.00 - Pupuk NPK',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '810', 'title' => 'SNI Wajib Pupuk NPK Padat'],
                        'required_doc' => 'SPPT SNI Pupuk NPK',
                        'source_regulation' => 'Permenperin No. 19/2019',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '3304.99.90',
                'description' => 'Produk Kecantikan atau Rias dan Produk Perawatan Kulit / Skincare (Lotion, Cream Wajah, Serum)',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Notifikasi Kosmetik BPOM RI & SKI Kosmetik BPOM)',
                'btki' => 'BTKI 2022 Pos 3304.99.90 - Preparat kecantikan lainnya',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '820', 'title' => 'Surat Pemberitahuan Notifikasi Kosmetik BPOM RI'],
                        'required_doc' => 'Notifikasi Kosmetik BPOM',
                        'source_regulation' => 'PerBPOM No. 12/2020',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '3401.11.40',
                'description' => 'Sabun Mandi Padat Mengandung Bahan Obat atau Disinfektan (Medicated Soap)',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'KGM',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 3401.11.40 - Sabun mandi berobat',
                'regulations' => [],
            ],

            // =========================================================================
            // BAB 39 & 40: TAMBAHAN RESIN, PIPA PVC, & BAN KENDARAAN
            // =========================================================================
            [
                'code' => '3902.10.40',
                'description' => 'Polipropilena (PP) Bukan Paduan dalam Bentuk Butiran / Granule Pelet untuk Kemasan Plastik',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'KGM',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Persetujuan Impor Bahan Baku Plastik Kemendag)',
                'btki' => 'BTKI 2022 Pos 3902.10.40 - Polipropilena bentuk butiran',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor Plastik Kemendag'],
                        'required_doc' => 'PI Bahan Plastik',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '3917.21.00',
                'description' => 'Tabung, Pipa, dan Selang Kaku dari Polimer Etilena (Pipa HDPE Distribusi Air Bersih)',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'KGM',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 3917.21.00 - Pipa kaku dari polietilena',
                'regulations' => [],
            ],
            [
                'code' => '4011.20.10',
                'description' => 'Ban Luar Pneumatik Baru dari Karet untuk Bus atau Truk (Ban Radial Truk Angkutan Berat)',
                'duty_bm' => 15.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (SNI Wajib Ban Mobil Bus & Truk Kemenperin & Persetujuan Impor Kemendag)',
                'btki' => 'BTKI 2022 Pos 4011.20.10 - Ban bus dan truk',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '810', 'title' => 'SNI Wajib Ban Truk dan Bus'],
                        'required_doc' => 'SPPT SNI Ban Kemenperin',
                        'source_regulation' => 'Permenperin No. 76/2015',
                        'verified' => true,
                    ],
                ],
            ],

            // =========================================================================
            // BAB 44, 48, 49: KAYU LAPIS, KERTAS KARTON, & BUKU
            // =========================================================================
            [
                'code' => '4412.31.00',
                'description' => 'Kayu Lapis (Plywood) Terdiri Semata-mata dari Lembaran Kayu Tropis Kualitas Ekspor Indonesia',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'MTQ',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Ekspor (Dokumen V-Legal / SVLK KLHK & Eksportir Terdaftar Kayu Kemendag)',
                'btki' => 'BTKI 2022 Pos 4412.31.00 - Plywood kayu tropis',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '880', 'title' => 'Dokumen V-Legal / SVLK KLHK'],
                        'required_doc' => 'Dokumen V-Legal SVLK',
                        'source_regulation' => 'Permen LHK No. 8/2021',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '4802.56.90',
                'description' => 'Kertas Fotokopi HVS Tanpa Salutan Ukuran A4 Berat 80 g/m² Dikemas Rim untuk Perkantoran',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'KGM',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 4802.56.90 - Kertas tulis cetak ukuran lembaran',
                'regulations' => [],
            ],
            [
                'code' => '4819.10.00',
                'description' => 'Kotak dan Kardus Karton Bergelombang untuk Pengemasan Barang Logistik (Corrugated Box)',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 4819.10.00 - Kotak karton bergelombang',
                'regulations' => [],
            ],

            // =========================================================================
            // BAB 72, 73, 76: LOGAM DASAR, REBAR, PIPA SEAMLESS, & ALUMINIUM
            // =========================================================================
            [
                'code' => '7214.20.00',
                'description' => 'Batang Besi Beton Berulir / Deformed Rebar dari Besi atau Baja Bukan Paduan untuk Konstruksi Sipil',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'TNE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Ketat (SNI Wajib Baja Tulangan Beton BjTS Kemenperin & Persetujuan Impor Besi Baja)',
                'btki' => 'BTKI 2022 Pos 7214.20.00 - Besi beton berulir',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '810', 'title' => 'SNI Wajib Baja Tulangan Beton (SPPT SNI)'],
                        'required_doc' => 'SPPT SNI Baja Beton Kemenperin',
                        'source_regulation' => 'Permenperin No. 14/2018',
                        'verified' => true,
                    ],
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor Besi Baja Kemendag'],
                        'required_doc' => 'PI Besi Baja Kemendag',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '7304.19.00',
                'description' => 'Pipa Saluran Minyak dan Gas Tanpa Sambungan (Seamless Pipe Baja API 5L Standar Migas)',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'TNE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Persetujuan Impor Besi Baja Kemendag & Rekomendasi Migas ESDM)',
                'btki' => 'BTKI 2022 Pos 7304.19.00 - Pipa pipa saluran mulus',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor Besi Baja & Pipa Kemendag'],
                        'required_doc' => 'PI Pipa Baja Kemendag',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '7601.10.00',
                'description' => 'Aluminium Tidak Ditempa Bukan Paduan (Ingot Aluminium Batangan Murni P1020)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'TNE',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 7601.10.00 - Aluminium bukan paduan tidak ditempa',
                'regulations' => [],
            ],
            [
                'code' => '7604.29.90',
                'description' => 'Profil dan Batangan Ekstrusi dari Paduan Aluminium untuk Kusen Bangunan & Konstruksi',
                'duty_bm' => 5.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'KGM',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 7604.29.90 - Profil aluminium lainnya',
                'regulations' => [],
            ],

            // =========================================================================
            // BAB 84 & 85: MESIN CNC, PRINTER, DISPLAY TV, & LAMPU LED
            // =========================================================================
            [
                'code' => '8458.11.00',
                'description' => 'Mesin Bubut Horisontal Pembuang Logam Terkendali Numerik Komputer (Mesin CNC Lathe)',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas Barang Modal Baru)',
                'btki' => 'BTKI 2022 Pos 8458.11.00 - Mesin bubut terkendali numerik',
                'regulations' => [],
            ],
            [
                'code' => '8443.32.90',
                'description' => 'Mesin Cetak Komputer Multifungsi Printer Laser & Inkjet Terhubung Jaringan',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas)',
                'btki' => 'BTKI 2022 Pos 8443.32.90 - Printer komputer',
                'regulations' => [],
            ],
            [
                'code' => '8517.62.21',
                'description' => 'Router Nirkabel WiFi & Peralatan Pemancar Penerima Jaringan Komputer Telekomunikasi',
                'duty_bm' => 0.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Sertifikasi Standar Perangkat Telekomunikasi SDPPI Kominfo)',
                'btki' => 'BTKI 2022 Pos 8517.62.21 - Router nirkabel',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '853', 'title' => 'Sertifikat Standar Perangkat Telekomunikasi SDPPI'],
                        'required_doc' => 'Sertifikat SDPPI Kominfo',
                        'source_regulation' => 'Permenkominfo No. 7/2018',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '8528.72.92',
                'description' => 'Aparatus Penerima Televisi Layar Datar LED Berwarna dengan Resolusi Tinggi (Smart TV 4K)',
                'duty_bm' => 15.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (SNI Wajib TV CRT/LCD/LED Kemenperin, LS Kemendag, & SDPPI WiFi)',
                'btki' => 'BTKI 2022 Pos 8528.72.92 - Televisi layar datar',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '810', 'title' => 'SNI Wajib Televisi'],
                        'required_doc' => 'SPPT SNI Televisi Kemenperin',
                        'source_regulation' => 'Permenperin No. 15/2018',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '8539.52.00',
                'description' => 'Lampu Dioda Pemancar Cahaya (Lampu LED Bulb dan LED Tube Penghemat Energi)',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'PCE',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (SNI Wajib Luminer & Lampu Swabalast LED Kemenperin)',
                'btki' => 'BTKI 2022 Pos 8539.52.00 - Lampu LED',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '810', 'title' => 'SNI Wajib Lampu LED Swabalast'],
                        'required_doc' => 'SPPT SNI Lampu LED Kemenperin',
                        'source_regulation' => 'Permenperin No. 73/2015',
                        'verified' => true,
                    ],
                ],
            ],

            // =========================================================================
            // BAB 87: SEPEDA MOTOR & TRUK
            // =========================================================================
            [
                'code' => '8711.20.90',
                'description' => 'Sepeda Motor dengan Mesin Torak Bolak-Balik Silinder Melebihi 125 cc tetapi <= 150 cc (Skutik 150cc)',
                'duty_bm' => 30.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => true,
                'lartas_desc' => 'Lartas Border (Persetujuan Impor Sepeda Motor Kemendag, TPT Kemenperin, & SUT Kemenhub)',
                'btki' => 'BTKI 2022 Pos 8711.20.90 - Sepeda motor 125cc-150cc',
                'regulations' => [
                    [
                        'doc_condition' => ['code' => '860', 'title' => 'Persetujuan Impor Kendaraan Bermotor Roda Dua'],
                        'required_doc' => 'PI Sepeda Motor Kemendag',
                        'source_regulation' => 'Permendag No. 36/2023',
                        'verified' => true,
                    ],
                ],
            ],
            [
                'code' => '8704.22.21',
                'description' => 'Dump Truck Kendaraan Bermotor Pengangkut Barang GVW Melebihi 5 Ton tetapi <= 20 Ton Mesin Diesel',
                'duty_bm' => 10.0,
                'duty_ppn' => 11.0,
                'duty_pph' => 2.5,
                'duty_bk' => 0.0,
                'satuan' => 'UNT',
                'has_lartas' => false,
                'lartas_desc' => 'Bebas Lartas (Non-Lartas untuk Keadaan Baru)',
                'btki' => 'BTKI 2022 Pos 8704.22.21 - Dump truck GVW 5t - 20t',
                'regulations' => [],
            ],
        ];
    }

    /**
     * Jalankan sinkronisasi database HS Codes dari registry resmi BTKI 2022
     */
    public function syncFromBtkiRegistry(bool $force = false): array
    {
        $registry = self::getBtkiMasterTariffRegistry();
        $inserted = 0;
        $updated = 0;
        $regulationsCount = 0;

        DB::beginTransaction();
        try {
            foreach ($registry as $item) {
                $cleanCode = str_replace(['.', ' ', '-'], '', $item['code']);

                // Cari berdasarkan format titik maupun unpunctuated
                $hsRecord = HsCode::where('code', $item['code'])
                    ->orWhere('code', $cleanCode)
                    ->first();

                $attributes = [
                    'description' => $item['description'],
                    'duty_bm' => $item['duty_bm'],
                    'duty_ppn' => $item['duty_ppn'] ?? 11.0,
                    'duty_pph' => $item['duty_pph'] ?? 2.5,
                    'duty_bk' => $item['duty_bk'] ?? 0.0,
                    'satuan' => $item['satuan'] ?? 'PCE',
                    'has_lartas' => (bool) $item['has_lartas'],
                    'lartas_desc' => $item['lartas_desc'] ?? null,
                    'btki' => $item['btki'] ?? ("BTKI 2022 Pos {$cleanCode}"),
                    'source' => 'DJBC_BTKI_2022',
                    'valid_from' => '2022-04-01', // Efektif berlakunya BTKI 2022 PMK 26/2022
                    'valid_to' => null,
                ];

                if (!$hsRecord) {
                    $attributes['code'] = $item['code'];
                    $hsRecord = HsCode::create($attributes);
                    $inserted++;
                } else {
                    if ($force || empty($hsRecord->duty_bm) || $hsRecord->duty_bm == 0) {
                        $hsRecord->update($attributes);
                        $updated++;
                    }
                }

                // Sinkronisasi Tabel Regulasi Lartas (hs_regulations)
                if (!empty($item['regulations'])) {
                    foreach ($item['regulations'] as $reg) {
                        HsRegulation::updateOrCreate(
                            [
                                'hs_from' => $cleanCode,
                                'hs_to' => $cleanCode,
                                'required_doc' => $reg['required_doc'],
                            ],
                            [
                                'doc_condition' => $reg['doc_condition'],
                                'source_regulation' => $reg['source_regulation'],
                                'verified' => $reg['verified'] ?? true,
                                'valid_from' => '2022-04-01',
                            ]
                        );
                        $regulationsCount++;
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'total_registry' => count($registry),
                'inserted' => $inserted,
                'updated' => $updated,
                'regulations_synced' => $regulationsCount,
                'total_in_db' => HsCode::count(),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal sinkronisasi BTKI 2022: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sinkronisasi Pos Tarif Spesifik langsung dari Live Gateway CEISA 4.0 Open API
     */
    public function syncFromCeisaApi(string $posTarif): ?HsCode
    {
        $cleanCode = str_replace(['.', ' ', '-'], '', trim($posTarif));

        try {
            $apiRes = $this->client->send('GET', "/tarif-hs?posTarif={$cleanCode}");

            if ($apiRes['success'] && !empty($apiRes['data'])) {
                $d = $apiRes['data'];

                $hsRecord = HsCode::updateOrCreate(
                    ['code' => $d['posTarif'] ?? $posTarif],
                    [
                        'description' => $d['uraian'] ?? "Barang Impor/Ekspor Pos Tarif {$cleanCode}",
                        'duty_bm' => (float) ($d['bm'] ?? 5.0),
                        'duty_ppn' => (float) ($d['ppn'] ?? 11.0),
                        'duty_pph' => (float) ($d['pph'] ?? 2.5),
                        'duty_bk' => (float) ($d['bk'] ?? 0.0),
                        'satuan' => $d['satuan'] ?? 'PCE',
                        'has_lartas' => !empty($d['lartas']),
                        'lartas_desc' => $d['lartas'] ?? 'Bebas Lartas (Non-Lartas)',
                        'btki' => "CEISA 4.0 Open API Live Pos {$cleanCode}",
                        'source' => 'CEISA_OPENAPI',
                        'valid_from' => now()->toDateString(),
                    ]
                );

                return $hsRecord;
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal fetch tarif HS dari CEISA Open API ({$posTarif}): {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Dapatkan Statistik Status Database HS Code Bea Cukai
     */
    public function getStats(): array
    {
        return [
            'total_hs_codes' => HsCode::count(),
            'total_lartas_codes' => HsCode::where('has_lartas', true)->count(),
            'total_regulations' => HsRegulation::count(),
            'latest_synced' => HsCode::latest('updated_at')->value('updated_at'),
            'sources' => HsCode::select('source', DB::raw('count(*) as total'))
                ->groupBy('source')
                ->pluck('total', 'source')
                ->toArray(),
        ];
    }
}
