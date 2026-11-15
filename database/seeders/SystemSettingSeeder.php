<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // 1. CEISA Inward Manifest Mock & Fallback Configuration
            [
                'key' => 'ceisa_mock_manifest_inward',
                'group' => 'ceisa',
                'description' => 'Parameter manifest kedatangan kapal (BC 1.1 Inward) untuk simulasi gateway CEISA 4.0 DJBC',
                'value' => [
                    'nomor_bc11_default' => '001294',
                    'nama_kapal' => 'EVER GIVEN',
                    'voyage_no' => 'V-2026-N',
                    'pelabuhan_muat' => 'CNSHA',
                    'pelabuhan_bongkar' => 'IDTPP',
                    'pos_bc11' => '0001',
                    'subpos_bc11' => '0000',
                    'subsubpos_bc11' => '0000',
                    'jumlah_kemasan' => 1200,
                    'jenis_kemasan' => 'PK',
                    'berat_bruto' => 24500.0,
                ],
            ],

            // 2. CEISA TPS Online Gate Out Mock & Fallback Configuration
            [
                'key' => 'ceisa_mock_tps_gate_out',
                'group' => 'ceisa',
                'description' => 'Parameter respon simulasi penarikan status Gate Out dari sistem TPS Online Pelabuhan',
                'value' => [
                    'status_gate' => 'GATE_OUT',
                    'nama_tps' => 'KOJA CONTAINER TERMINAL',
                    'lokasi_tps' => 'IDTPP01',
                    'nomor_truk' => 'B 9812 UIK',
                    'keterangan' => 'Peti kemas telah keluar dari pintu gerbang utama TPS',
                ],
            ],

            // 3. CEISA Respon & Status Template
            [
                'key' => 'ceisa_mock_response_template',
                'group' => 'ceisa',
                'description' => 'Template dan parameter kode respon simulasi penjaluran pabean CEISA 4.0',
                'value' => [
                    'kode_status_default' => '200',
                    'keterangan_status' => 'Dokumen terdaftar dan telah masuk dalam sistem penjaluran DJBC',
                    'id_header_prefix' => 'fbb63046',
                    'auto_generate_sppb' => true,
                ],
            ],

            // 4. Parameter Neraca Saldo Awal Akuntansi
            [
                'key' => 'accounting_initial_balance_config',
                'group' => 'accounting',
                'description' => 'Alokasi saldo awal akun kas, bank operasional, dan ekuitas modal disetor',
                'value' => [
                    'bca_balance' => 450000000,
                    'mandiri_balance' => 380000000,
                    'kas_kecil_balance' => 20000000,
                    'bca_coa' => '1110-01',
                    'mandiri_coa' => '1110-02',
                    'kas_kecil_coa' => '1100-01',
                    'equity_coa' => '3100-01',
                    'description' => 'Modal Disetor Awal Pendirian PT Forwarding Logistics',
                ],
            ],

            // 5. Parameter Peringatan Dini Demurrage Kontainer
            [
                'key' => 'demurrage_threshold_config',
                'group' => 'shipment',
                'description' => 'Ambang batas hari peringatan dini demurrage peti kemas dan biaya pinalti',
                'value' => [
                    'alert_days' => [3, 2, 1],
                    'default_free_time_days' => 7,
                    'daily_penalty_rate_20ft' => 450000,
                    'daily_penalty_rate_40ft' => 750000,
                    'notify_email_pic' => true,
                    'notify_wa_client' => true,
                ],
            ],

            // 6. Parameter Tarif PPh 21 Pasal 17 (Progressive Bracket DJP)
            [
                'key' => 'tax_pph21_parameters',
                'group' => 'tax',
                'description' => 'Lapisan tarif progresif PPh 21 UU HPP dan ketentuan biaya jabatan DJP',
                'value' => [
                    'brackets' => [
                        ['max' => 60000000, 'rate' => 0.05],
                        ['max' => 250000000, 'rate' => 0.15],
                        ['max' => 500000000, 'rate' => 0.25],
                        ['max' => 5000000000, 'rate' => 0.30],
                        ['max' => null, 'rate' => 0.35],
                    ],
                    'biaya_jabatan_rate' => 0.05,
                    'max_biaya_jabatan_tahunan' => 6000000,
                    'max_biaya_jabatan_bulanan' => 500000,
                ],
            ],

            // 7. Parameter Nilai PTKP (Penghasilan Tidak Kena Pajak)
            [
                'key' => 'tax_ptkp_rules',
                'group' => 'tax',
                'description' => 'Tabel batas penghasilan tidak kena pajak tahunan berdasarkan status perkawinan',
                'value' => [
                    'TK/0' => 54000000,
                    'TK/1' => 58500000,
                    'TK/2' => 63000000,
                    'TK/3' => 67500000,
                    'K/0' => 58500000,
                    'K/1' => 63000000,
                    'K/2' => 67500000,
                    'K/3' => 72000000,
                    'tanggungan_tambahan' => 4500000,
                ],
            ],

            // 8. Parameter Estimasi Biaya Pengapalan & Pabean Default
            [
                'key' => 'shipment_tariff_estimates',
                'group' => 'shipment',
                'description' => 'Nilai estimasi standar pungutan negara (PNBP) dan jasa verifikasi pabean',
                'value' => [
                    'pnbp_import_standard' => 250000,
                    'pnbp_export_standard' => 100000,
                    'survey_behandle_standard' => 750000,
                    'do_release_admin_fee' => 350000,
                    'minimum_escrow_buffer' => 5000000,
                ],
            ],

            // 9. Parameter Integrasi WhatsApp Business API (Meta Cloud)
            [
                'key' => 'whatsapp_meta_config',
                'group' => 'crm',
                'description' => 'Kredensial dan endpoint Meta WhatsApp Cloud API v19.0',
                'value' => [
                    'api_version' => 'v19.0',
                    'base_url' => 'https://graph.facebook.com',
                    'phone_number_id' => '100987654321098',
                    'waba_id' => 'WABA-ID-99218274',
                    'webhook_verify_token' => 'GENIE_WHATSAPP_SECURE_TOKEN_2026',
                    'default_sender_name' => 'GENIE Support CS',
                ],
            ],

            // 10. Profil Perusahaan & Legalitas PPJK
            [
                'key' => 'company_profile',
                'group' => 'company',
                'description' => 'Identitas resmi perusahaan, legalitas PPJK, NPWP, dan kontak operasional',
                'value' => [
                    'name' => 'PT AGUNG TRANS SOLUSINDO',
                    'brand_name' => 'GENIE ERP',
                    'address' => 'Gedung Cyber 2 Lantai 15, Jl. H.R. Rasuna Said Blok X-5 No. 13, Jakarta Selatan 12950',
                    'phone' => '+62 21 5299 8000',
                    'email' => 'ops@genie-logistics.co.id',
                    'npwp' => '01.234.567.8-012.000',
                    'nib' => '9120001234567',
                    'ppjk_license_no' => 'KEP-456/BC.01/2024',
                    'kode_kantor_default' => '040300',
                ],
            ],

            // 11. Template Surat Permohonan Rilis Delivery Order (DO) Pelayaran
            [
                'key' => 'template_do_request',
                'group' => 'template',
                'description' => 'Template kop, perihal, dan kalimat surat resmi permohonan rilis DO pelayaran',
                'value' => [
                    'company_name' => 'PT AGUNG TRANS SOLUSINDO',
                    'company_subtitle' => "International Freight Forwarder & Customs Brokerage (PPJK)\nJl. Enggano Raya No. 45, Tanjung Priok, Jakarta Utara 14310 | Telp: (021) 4390-8821\nEmail: do-desk@agungtrans.co.id | NPWP: 03.882.910.4-041.000",
                    'letter_prefix' => 'ATS/DO-REQ',
                    'lampiran' => 'Copy Bill of Lading & Bukti Pembayaran Resmi',
                    'perihal' => 'Permohonan Penerbitan dan Rilis Delivery Order (DO) Online',
                    'salam_pembuka' => 'Dengan hormat,',
                    'paragraf_pembuka' => 'Sehubungan dengan kedatangan sarana pengangkut kargo impor di Pelabuhan Tanjung Priok, bersama ini kami bertindak atas kuasa pemilik barang (Consignee) mengajukan permohonan penerbitan dan rilis berkas Delivery Order (DO) atas pengiriman dengan rincian data sebagai berikut:',
                    'lampiran_items' => [
                        'Salinan (Copy) Bill of Lading (B/L) yang telah di-endorse sah oleh importir.',
                        'Bukti transfer pelunasan biaya lokal pelayaran (Ocean Freight, THC, DO Admin Fee).',
                        'Surat Kuasa Pengurusan DO dari Consignee kepada perusahaan kami.',
                    ],
                    'instruksi_rilis' => 'Mohon berkas Delivery Order (DO) tersebut dapat segera dirilis melalui sistem DO Online / portal pelayaran atau dikirimkan ke alamat email operasional kami: do-desk@agungtrans.co.id.',
                    'paragraf_penutup' => 'Demikian surat permohonan ini kami sampaikan. Atas kerja sama dan bantuan yang diberikan, kami ucapkan terima kasih.',
                    'signer_name' => 'RENDI SAPUTRA',
                    'signer_title' => 'Head of Shipping Line & DO Clearance',
                ],
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::set(
                $setting['key'],
                $setting['value'],
                $setting['group'],
                $setting['description']
            );
        }
    }
}
