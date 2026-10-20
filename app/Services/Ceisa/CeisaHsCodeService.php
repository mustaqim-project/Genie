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
        '84122100' => [
            'code' => '8412.21.00',
            'desc' => 'Hydraulic Breaker Mesin Pemecah Batu Hidraulik & Motor Hidraulik Linear (DYB800, THB140)',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'SET',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8412.21 - Motor tenaga hidraulik aksi linear / silinder (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84306900' => [
            'code' => '8430.69.00',
            'desc' => 'Mesin pemadat atau perata tanah atau mineral lainnya',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'SET',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8430.69 - Mesin untuk mengerjakan tanah atau mineral (BM 0%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84314990' => [
            'code' => '8431.49.90',
            'desc' => 'Suku Cadang Hydraulic Breaker (Chisel, Pin Rod, Pin Lock, Side Bolt, Through Bolt, Bushing, Fitting, Sleeve Valve, Hose Clamp, Piping Kit)',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8431.49 - Bagian dari mesin pos 84.30 / Breaker (BM 0% ACFTA / 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '68071000' => [
            'code' => '6807.10.00',
            'desc' => 'SBS / Granule Waterproofing Membrane Gulungan Aspal Bitumen Kedap Air',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'ROL',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 6807.10 - Barang dari aspal dalam bentuk gulungan (BM 0% ACFTA / 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84671900' => [
            'code' => '8467.19.00',
            'desc' => 'Pneumatic Pick / Perkakas Pneumatik Genggam Bobok Beton (G10, G15, G20, TCA-7, TPB60)',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8467.19 - Perkakas pneumatik lainnya (BM 0% ACFTA / 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84679990' => [
            'code' => '8467.99.90',
            'desc' => 'Suku Cadang Perkakas Pneumatik (Chisel Type Plus/Minus, Spring Head, Bushing TPB60)',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8467.99 - Bagian perkakas genggam pos 84.67 (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84279000' => [
            'code' => '8427.90.00',
            'desc' => 'Hand Pallet Truck & Hand Stacker (Manual & Elektrik) Truk Angkat Kerja',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8427.90 - Truk kerja lainnya (BM 0% ACFTA / 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84312090' => [
            'code' => '8431.20.90',
            'desc' => 'Suku Cadang Hand Pallet & Hand Stacker (Oil Cylinder, PU/Nylon Caster, Wheel Bracket, Rubber Seal 2T/3T)',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8431.20 - Bagian dari mesin pos 84.27 (BM 0% ACFTA / 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84622600' => [
            'code' => '8462.26.00',
            'desc' => 'Bending Machine GW-50 Mesin Pembengkok / Penekuk Profil Besi Beton',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8462.26 - Mesin pembengkok profil logam (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84615000' => [
            'code' => '8461.50.00',
            'desc' => 'Steel Bar Cutting Machine GQ50 Mesin Pemotong Besi Tulangan / Beton',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8461.50 - Mesin pemotong atau penggergaji logam (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84602910' => [
            'code' => '8460.29.10',
            'desc' => 'Angle Grinder SH-05 Mesin Gerinda Tangan Listrik',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8460.29 - Mesin penggerinda logam dioperasikan dengan tangan (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84743110' => [
            'code' => '8474.31.10',
            'desc' => 'Electric Concrete Mixer 180, 240, 300 Mesin Molen Pengaduk Semen / Beton Listrik',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8474.31 - Mesin pengaduk beton atau semen (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '70199090' => [
            'code' => '7019.90.90',
            'desc' => 'Fiberglass Mesh Waterproofing 120gr 1*50m Jaring Serat Kaca Kedap Air',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'ROL',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 7019.90 - Serat kaca dan barang daripadanya (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84679910' => [
            'code' => '8467.99.10',
            'desc' => 'Hammer Drill Bit 10*160, 14*160 Mata Bor Palu Beton untuk Perkakas',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8467.99 - Bagian dari perkakas pneumatik / listrik (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84269900' => [
            'code' => '8426.99.00',
            'desc' => 'Hydraulic Engine Crane Folding Type 2T, 3T Derek Derek Derek Mesin Hidraulik',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8426.99 - Derek dan alat pengangkat lainnya (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84269100' => [
            'code' => '8426.91.00',
            'desc' => 'Mini Truck Crane 1T 12V, 24V Derek Pengangkat Dipasang pada Kendaraan',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'SET',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8426.91 - Derek dirancang untuk dipasang pada kendaraan (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84251900' => [
            'code' => '8425.19.00',
            'desc' => 'Lever Block & Chain Block (0.75T, 1T, 1.5T, 2T, 3T, 10T) Katrol Takel Manual',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8425.19 - Derek katrol puli dan kerekan manual (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '56074900' => [
            'code' => '5607.49.00',
            'desc' => 'Paranet 75% & 65% Black 100M*3M Jaring Peneduh Polietilena',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'KGM',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 5607.49 - Tali, benang dan jala dari polietilena atau polipropilena (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84306100' => [
            'code' => '8430.61.00',
            'desc' => 'Tamping Rammer Gasoline Type 125 Stamper Kuda Mesin Pemadat Tanah Bensin',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8430.61 - Mesin pemadat atau perata tanah (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '56090000' => [
            'code' => '5609.00.00',
            'desc' => 'Webbing Sling Belt Yellow 3T, Grey 4T, Red 5T/10T Sabuk Tali Angkat Poliester',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'KGM',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 5609.00 - Barang dari benang, strip atau tali (BM 0% ACFTA, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84279000' => [
            'code' => '8427.90.00',
            'desc' => 'Hand Pallet Truck & Hand Stacker Truk Pengangkat Mekanis Manual',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8427.90 - Truk kerja manual non-elektrik (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84271000' => [
            'code' => '8427.10.00',
            'desc' => 'Electric Hand Stacker Truk Stacker Bertenaga Motor Listrik',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8427.10 - Truk kerja bertenaga motor listrik (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84312000' => [
            'code' => '8431.20.00',
            'desc' => 'Suku Cadang Hand Pallet & Stacker (Oil Cylinder, PU/Nylon Caster, Wheel Bracket)',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8431.20 - Bagian dari mesin pos 84.27 (BM 0%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84622400' => [
            'code' => '8462.24.00',
            'desc' => 'Bending Machine Mesin Pelengkung / Pembengkok Besi Tulangan (Model GW-50)',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8462.24 - Mesin pelengkung profil logam (BM 0%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84623300' => [
            'code' => '8462.33.00',
            'desc' => 'Steel Bar Cutting Machine Mesin Pemotong Besi Beton / Tulangan (Model GQ50)',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8462.33 - Mesin pemotong logam (BM 0%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84743100' => [
            'code' => '8474.31.00',
            'desc' => 'Electric Concrete Mixer Mesin Pengaduk Semen / Beton Elektrik',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8474.31 - Mesin pengaduk beton atau semen (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84672100' => [
            'code' => '8467.21.00',
            'desc' => 'Impact Drill Perkakas Bor Tangan Listrik',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8467.21 - Mesin bor dengan motor listrik (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84672910' => [
            'code' => '8467.29.10',
            'desc' => 'Angle Grinder Mesin Gerinda Sudut Tangan Elektrik',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8467.29 - Gerinda tangan elektromekanis (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '82075000' => [
            'code' => '8207.50.00',
            'desc' => 'Hammer Drill Bit Mata Bor Perkakas Mesin',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8207.50 - Perkakas untuk pengeboran (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84251900' => [
            'code' => '8425.19.00',
            'desc' => 'Lever Block & Chain Block Takel Rantai Alat Kerek Manual',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8425.19 - Alat kerek dan kerekan puli (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84261900' => [
            'code' => '8426.19.00',
            'desc' => 'Hydraulic Engine Crane & Mini Truck Crane Derek Gantung Hidraulik',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8426.19 - Derek gantung dan portal crane (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84306100' => [
            'code' => '8430.61.00',
            'desc' => 'Tamping Rammer Mesin Stamper Kuda Pemadat Tanah',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'UNT',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8430.61 - Mesin perata atau pemadat tanah (BM 0%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '63079090' => [
            'code' => '6307.90.90',
            'desc' => 'Webbing Sling Belt Tali Sabuk Pengangkat Kargo Poliester',
            'bm' => 10.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 6307.90 - Barang jadi tekstil lainnya tali angkat (BM 10%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '56081920' => [
            'code' => '5608.19.20',
            'desc' => 'Paranet Jaring Peneduh Tanaman & Proyek dari Plastik Sintetik',
            'bm' => 10.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'ROL',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 5608.19 - Jaring peneduh sintetik (BM 10%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '70196100' => [
            'code' => '7019.61.00',
            'desc' => 'Fiberglass Mesh Waterproofing Jaring Serat Kaca Penguat Lapisan Aspal',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'ROL',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 7019.61 - Kain tenun tertutup dari serat kaca (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '73269099' => [
            'code' => '7326.90.99',
            'desc' => 'Hose Clamp Klem Pengikat Selang Logam Besi / Baja',
            'bm' => 10.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 7326.90 - Barang lainnya dari besi atau baja (BM 10%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '40169320' => [
            'code' => '4016.93.20',
            'desc' => 'Rubber Seal / Gasket Karet Vulkanisir Cincin Perapat',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'SET',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 4016.93 - Gasket dan perapat karet (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '73181590' => [
            'code' => '7318.15.90',
            'desc' => 'Baut dan Sekrup Lainnya Berkepala dari Besi atau Baja (Side Bolt / Through Bolt)',
            'bm' => 10.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 7318.15 - Baut dan sekrup berkepala dari besi atau baja (BM 10%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '73079910' => [
            'code' => '7307.99.10',
            'desc' => 'Fitting Sambungan Tabung atau Pipa dari Besi atau Baja (Hydraulic Fitting)',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 7307.99 - Sambungan pipa lainnya dari besi atau baja (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '84122100' => [
            'code' => '8412.21.00',
            'desc' => 'Mesin dan Motor Hidraulik Aksi Linier / Silinder (Oil Cylinder Hand Pallet/Stacker)',
            'bm' => 0.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8412.21 - Motor hidraulik aksi linier silinder (BM 0%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
        ],
        '82079000' => [
            'code' => '8207.90.00',
            'desc' => 'Pahat Perkakas Tangan Pneumatik Bobok (Chisel Interchangeable Tool)',
            'bm' => 5.0,
            'ppn' => 11.0,
            'pph' => 2.5,
            'bk' => 0.0,
            'satuan' => 'PCE',
            'has_lartas' => false,
            'lartas' => 'Bebas Lartas (Non-Lartas)',
            'legal' => 'BTKI 2022 Pos 8207.90 - Perkakas yang dapat dipertukarkan lainnya (BM 5%, PPN 11%, PPh 2.5%)',
            'required_documents' => [],
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

        // 1. Cek Database Master Lokal (Tabel hs_codes) TERLEBIH DAHULU
        $dbHs = HsCode::where('code', $hsCode)
            ->orWhere('code', $cleanCode)
            ->orWhereRaw("REPLACE(REPLACE(REPLACE(code, '.', ''), ' ', ''), '-', '') = ?", [$cleanCode])
            ->first();

        if ($dbHs) {
            $regulations = HsRegulation::where(function ($q) use ($cleanCode) {
                $q->where('hs_from', '<=', $cleanCode)
                  ->where('hs_to', '>=', $cleanCode);
            })->orWhere('hs_from', $cleanCode)->get();

            $reqDocs = [];
            foreach ($regulations as $reg) {
                $cond = $reg->doc_condition ?? [];
                $reqDocs[] = [
                    'kode_dokumen' => $cond['code'] ?? '860',
                    'nama_dokumen' => $cond['title'] ?? ($reg->required_doc ?? 'Dokumen Perizinan Lartas'),
                    'instansi' => $reg->source_regulation ?? 'Kementerian Teknis Terkait',
                    'is_mandatory' => true,
                ];
            }

            return [
                'success' => true,
                'source' => $dbHs->source ?? 'DJBC_BTKI_2022',
                'code' => $dbHs->code,
                'clean_code' => $cleanCode,
                'desc' => $dbHs->description,
                'bm' => (float) $dbHs->duty_bm,
                'ppn' => (float) ($dbHs->duty_ppn ?? 11.0),
                'pph' => (float) ($dbHs->duty_pph ?? 2.5),
                'bk' => (float) ($dbHs->duty_bk ?? 0.0),
                'satuan' => $dbHs->satuan ?? 'PCE',
                'has_lartas' => (bool) ($dbHs->has_lartas || count($reqDocs) > 0),
                'lartas' => $dbHs->lartas_desc ?? (count($reqDocs) > 0 ? "Lartas ({$regulations->first()->required_doc})" : 'Bebas Lartas (Non-Lartas)'),
                'legal' => $dbHs->btki ?? ("BTKI 2022 Pos {$dbHs->code}"),
                'required_documents' => $reqDocs,
            ];
        }

        // 2. Cek Built-in Knowledge Base
        if (isset($this->builtInKnowledgeBase[$cleanCode])) {
            $data = $this->builtInKnowledgeBase[$cleanCode];
            $data['required_documents'] = $data['required_documents'] ?? [];

            // Simpan ke DB lokal agar persisten untuk query berikutnya
            try {
                HsCode::updateOrCreate(
                    ['code' => $data['code']],
                    [
                        'description' => $data['desc'],
                        'duty_bm' => $data['bm'],
                        'duty_ppn' => $data['ppn'] ?? 11.0,
                        'duty_pph' => $data['pph'] ?? 2.5,
                        'duty_bk' => $data['bk'] ?? 0.0,
                        'satuan' => $data['satuan'] ?? 'PCE',
                        'has_lartas' => (bool) ($data['has_lartas'] ?? false),
                        'lartas_desc' => $data['lartas'] ?? null,
                        'btki' => $data['legal'] ?? "BTKI 2022 Pos {$cleanCode}",
                        'source' => 'DJBC_BTKI_2022',
                        'valid_from' => '2022-04-01',
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning("Gagal auto-persist HS Code ke DB: {$e->getMessage()}");
            }

            return array_merge(['success' => true, 'source' => 'BTKI_KNOWLEDGE_BASE'], $data);
        }

        // 3. Panggil API CEISA 4.0 Open API Live Gateway (/tarif-hs)
        try {
            $apiRes = $this->client->send('GET', "/tarif-hs?posTarif={$cleanCode}");
            if ($apiRes['success'] && !empty($apiRes['data'])) {
                $d = $apiRes['data'];
                $tariffResult = [
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
                    'has_lartas' => !empty($d['lartas']),
                    'lartas' => $d['lartas'] ?? 'Bebas Lartas (Non-Lartas)',
                    'legal' => "CEISA 4.0 Open API Pos {$cleanCode}",
                    'required_documents' => [],
                ];

                // Simpan ke database
                try {
                    HsCode::updateOrCreate(
                        ['code' => $tariffResult['code']],
                        [
                            'description' => $tariffResult['desc'],
                            'duty_bm' => $tariffResult['bm'],
                            'duty_ppn' => $tariffResult['ppn'],
                            'duty_pph' => $tariffResult['pph'],
                            'duty_bk' => $tariffResult['bk'],
                            'satuan' => $tariffResult['satuan'],
                            'has_lartas' => $tariffResult['has_lartas'],
                            'lartas_desc' => $tariffResult['lartas'],
                            'btki' => $tariffResult['legal'],
                            'source' => 'CEISA_OPENAPI',
                            'valid_from' => now()->toDateString(),
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::warning("Gagal persist HS dari API ke DB: {$e->getMessage()}");
                }

                return $tariffResult;
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

        // 2. Evaluasi Suku Cadang & Komponen Mesin Industri (Hierarki Prioritas Utama)
        // 2a. Mesin Hydraulic Breaker & Seluruh Suku Cadangnya (Chisel, Pin, Bolt, Bushing, Valve, Clamp, Fitting, Piping Kit)
        if (str_contains($upper, 'BREAKER')) {
            // Suku cadang khusus breaker (chisel, pin rod, pin lock, side bolt, through bolt, bushing, clamp, valve, fitting, piping kit) -> 8431.49.90
            if (str_contains($upper, 'SPAREPART') || str_contains($upper, 'SPARE PART') || preg_match('/\b(CHISEL|PIN\s*ROD|PIN\s*LOCK|PIN\s*BUSHING|SIDE\s*BOLT|THROUGH\s*BOLT|INNER\s*BUSHING|OUTER\s*BUSHING|T\s*BUSH|RING\s*PIN|BUSHING|BUSH|SLEEVE\s*VALVE|HOSE\s*CLAMP|PIPING\s*KIT|STOP\s*PIN|PISTON|SEAL\s*KIT|FITTING)\b/', $upper)) {
                $tariff = $this->getTariffAndLartas('84314990');
                $tariff['confidence_score'] = 0.99;
                $tariff['classification_source'] = 'AI_BREAKER_PARTS_MATCH';
                return $tariff;
            }
            // Unit Mesin Hydraulic Breaker Utuh (DYB800, THB140) -> 8412.21.00
            $tariff = $this->getTariffAndLartas('84122100');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_BREAKER_UNIT_MATCH';
            return $tariff;
        }

        // 2b. Hand Pallet Truck & Hand Stacker serta Suku Cadangnya (Oil cylinder, Caster, Wheel bracket, Rubber seal)
        if (str_contains($upper, 'PALLET') || str_contains($upper, 'STACKER')) {
            // Suku cadang khusus hand pallet / stacker (hanya jika ada kata SPAREPART atau komponen lepasan)
            if (str_contains($upper, 'SPAREPART') || str_contains($upper, 'SPARE PART')) {
                $tariff = $this->getTariffAndLartas('84312090');
                $tariff['confidence_score'] = 0.99;
                $tariff['classification_source'] = 'AI_PALLET_PARTS_MATCH';
                return $tariff;
            }
            if (preg_match('/\b(OIL\s*CYLINDER|WHEEL\s*BRACKET|RUBBER\s*SEAL)\b/', $upper)) {
                $tariff = $this->getTariffAndLartas('84312090');
                $tariff['confidence_score'] = 0.99;
                $tariff['classification_source'] = 'AI_PALLET_PARTS_MATCH';
                return $tariff;
            }
            // Unit Hand Pallet / Stacker -> 8427.90.00
            $tariff = $this->getTariffAndLartas('84279000');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_PALLET_UNIT_MATCH';
            return $tariff;
        }

        // 2c. Perkakas Pneumatik (Pneumatic Pick) & Suku Cadangnya
        if (str_contains($upper, 'PNEUMATIC PICK') || str_contains($upper, 'AIR PICK') || preg_match('/\b(G10|G15|G20|TCA-7|TPB60)\b/', $upper)) {
            // Suku cadang perkakas pneumatik (Chisel, Spring Head, Bushing) -> 8467.99.90
            if (str_contains($upper, 'SPAREPART') || str_contains($upper, 'SPARE PART') || preg_match('/\b(CHISEL|SPRING\s*HEAD|BUSHING|SPRING)\b/', $upper)) {
                $tariff = $this->getTariffAndLartas('84679990');
                $tariff['confidence_score'] = 0.99;
                $tariff['classification_source'] = 'AI_PNEUMATIC_PARTS_MATCH';
                return $tariff;
            }
            // Unit Perkakas Pneumatik Genggam (G10, G15, G20, TCA-7, TPB60) -> 8467.19.00
            $tariff = $this->getTariffAndLartas('84671900');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_PNEUMATIC_UNIT_MATCH';
            return $tariff;
        }

        // 2d. Membran Kedap Air Aspal / Bitumen (SBS & Granule Waterproofing Membrane) -> 6807.10.00
        if (preg_match('/\b(WATERPROOFING\s*MEMBRANE|SBS|GRANULE\s*WATERPROOFING|MEMBRANE\s*GREEN|MEMBRANE\s*PE)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('68071000');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_WATERPROOFING_MATCH';
            return $tariff;
        }

        // 2e. Mesin Tekuk Besi Beton (Bending Machine GW-50) -> 8462.26.00
        if (preg_match('/\b(BENDING\s*MACHINE|GW-50|GW50|TEKUK\s*BESI)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('84622600');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2f. Mesin Pemotong Besi Beton / Tulangan (Steel Bar Cutting Machine GQ50) -> 8461.50.00
        if (preg_match('/\b(CUTTING\s*MACHINE|GQ-50|GQ50|BAR\s*CUTTING|POTONG\s*BESI)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('84615000');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2g. Mesin Gerinda Tangan Listrik (Angle Grinder SH-05) -> 8460.29.10
        if (preg_match('/\b(ANGLE\s*GRINDER|SH-05|SH05|GERINDA)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('84602910');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2h. Molen Pengaduk Semen / Beton Elektrik (Electric Concrete Mixer 180, 240, 300) -> 8474.31.10
        if (preg_match('/\b(CONCRETE\s*MIXER|MOLEN)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('84743110');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2i. Jaring Kaca Kedap Air (Fiberglass Mesh Waterproofing) -> 7019.90.90
        if (preg_match('/\b(FIBERGLASS|FIBREGLASS|FIBER\s*GLASS)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('70199090');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2j. Mata Bor Palu Beton untuk Perkakas (Hammer Drill Bit 10*160, 14*160) -> 8467.99.10
        if (preg_match('/\b(HAMMER\s*DRILL\s*BIT|DRILL\s*BIT|MATA\s*BOR)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('84679910');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2k. Derek Gantung Mesin Hidraulik (Hydraulic Engine Crane 2T, 3T) -> 8426.99.00
        if (preg_match('/\b(ENGINE\s*CRANE)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('84269900');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2l. Derek Truk Mini (Mini Truck Crane 1T 12V/24V) -> 8426.91.00
        if (preg_match('/\b(TRUCK\s*CRANE)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('84269100');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2m. Mesin Bor Listrik Tangan (Impact Drill 10mm, 13mm) -> 8467.21.00
        if (preg_match('/\b(IMPACT\s*DRILL|BOR\s*LISTRIK)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('84672100');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2n. Takel Derek Manual (Lever Block & Chain Block 0.75T s.d. 10T) -> 8425.19.00
        if (preg_match('/\b(LEVER\s*BLOCK|CHAIN\s*BLOCK|HOIST|TAKEL)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('84251900');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2o. Jaring Peneduh Sintetis (Paranet 75%, 65% Black) -> 5607.49.00
        if (preg_match('/\b(PARANET|SHADE\s*NET|JARING\s*PENEDUH)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('56074900');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2p. Stamper Pemadat Tanah Bensin (Tamping Rammer Gasoline Type 125) -> 8430.61.00
        if (preg_match('/\b(TAMPING\s*RAMMER|RAMMER|STAMPER)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('84306100');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2q. Sabuk Tali Pengangkat Poliester (Webbing Sling Belt 3T, 4T, 5T, 10T) -> 5609.00.00
        if (preg_match('/\b(WEBBING\s*SLING|SLING\s*BELT|CARGO\s*SLING)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('56090000');
            $tariff['confidence_score'] = 0.99;
            $tariff['classification_source'] = 'AI_EQUIPMENT_MATCH';
            return $tariff;
        }

        // 2r. Fallback: Fasteners Umum (Hanya jika bukan bagian dari mesin khusus di atas) -> 7318.15.90
        if (preg_match('/\b(BOLT|SCREW|NUT|BAUT|SEKRUP|MUR)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('73181590');
            $tariff['confidence_score'] = 0.95;
            $tariff['classification_source'] = 'AI_GENERIC_HARDWARE_MATCH';
            return $tariff;
        }

        // 2s. Fallback: Klem Selang Umum -> 7326.90.99
        if (preg_match('/\b(HOSE\s*CLAMP|PIPE\s*CLAMP|CLAMP)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('73269099');
            $tariff['confidence_score'] = 0.95;
            $tariff['classification_source'] = 'AI_GENERIC_HARDWARE_MATCH';
            return $tariff;
        }

        // 2t. Fallback: Sambungan Pipa / Fitting Umum -> 7307.99.10
        if (preg_match('/\b(FITTING|PIPE\s*CONNECTOR|HOSE\s*JOINT)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('73079910');
            $tariff['confidence_score'] = 0.95;
            $tariff['classification_source'] = 'AI_GENERIC_HARDWARE_MATCH';
            return $tariff;
        }

        // 2u. Fallback: Seal Karet Umum -> 4016.93.20
        if (preg_match('/\b(RUBBER\s*SEAL|SEAL\s*KIT|OIL\s*SEAL|O-RING|GASKET)\b/', $upper)) {
            $tariff = $this->getTariffAndLartas('40169320');
            $tariff['confidence_score'] = 0.95;
            $tariff['classification_source'] = 'AI_GENERIC_HARDWARE_MATCH';
            return $tariff;
        }

        // 3. Pencocokan Cerdas Kata Kunci Komoditas Industri Lainnya
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
            'SPARE PART' => '84314990',
        ];

        foreach ($keywordMap as $kw => $code) {
            if (str_contains($upper, $kw)) {
                $tariff = $this->getTariffAndLartas($code);
                $tariff['confidence_score'] = 0.96;
                $tariff['classification_source'] = 'AI_KEYWORD_MATCH';
                return $tariff;
            }
        }

        // 3b. Pencocokan cerdas via Database Master Pos Tarif BTKI 2022 Bea Cukai
        $words = array_filter(explode(' ', preg_replace('/[^A-Z0-9\s]/', ' ', $upper)), fn($w) => strlen($w) >= 3);
        foreach ($words as $w) {
            $matchedDb = HsCode::where('description', 'like', "%{$w}%")->first();
            if ($matchedDb) {
                $tariff = $this->getTariffAndLartas($matchedDb->code);
                $tariff['confidence_score'] = 0.94;
                $tariff['classification_source'] = 'DATABASE_BTKI_LOOKUP';
                return $tariff;
            }
        }

        // 4. Fallback jika komoditas umum
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
