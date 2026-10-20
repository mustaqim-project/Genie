<?php

namespace Database\Seeders;

use App\Models\CeisaApiLog;
use App\Models\CeisaDocument;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Ceisa\DocumentBC20Service;
use App\Services\Ceisa\DocumentBC30Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. SEED 12 ROLES SESUAI PRD
        $rolesData = [
            ['code' => 'R-ADM', 'name' => 'Admin Sistem', 'description' => 'Master data, hak akses, konfigurasi SLA dan integrasi'],
            ['code' => 'R-OWN', 'name' => 'Pimpinan (Owner)', 'description' => 'Dashboard eksekutif, KPI, laporan semua tim, approval strategis'],
            ['code' => 'R-AM', 'name' => 'Account Manager', 'description' => 'Komunikasi klien; memantau seluruh shipment kliennya'],
            ['code' => 'R-DOC', 'name' => 'Staff Dokumen', 'description' => 'Kelola dokumen, verifikasi HS, susun dokumen pabean BC 2.0 & BC 3.0'],
            ['code' => 'R-DOC-SPV', 'name' => 'Supervisor Dokumen', 'description' => 'Approve dokumen pabean sebelum kirim ke CEISA 4.0'],
            ['code' => 'R-DO', 'name' => 'Staff Delivery Order', 'description' => 'Pelayaran, invoice pelayaran, penebusan DO'],
            ['code' => 'R-TRF', 'name' => 'Staff Traffic', 'description' => 'Kedatangan, jalur, hold, notul, gate out, vendor kirim'],
            ['code' => 'R-FLD', 'name' => 'Staff Lapangan', 'description' => 'Pemeriksaan fisik (behandle) dan urusan lapangan di TPS pelabuhan'],
            ['code' => 'R-MGR', 'name' => 'Manager Tim', 'description' => 'Approve operasional, eskalasi hold/SLA, KPI timnya'],
            ['code' => 'R-FIN', 'name' => 'Finance', 'description' => 'Pembayaran billing pabean, invoice pelayaran, penagihan invoice klien'],
            ['code' => 'R-HR', 'name' => 'HR', 'description' => 'Karyawan, kontrak PKWT/PKWTT, absensi GPS/wajah, cuti, payroll'],
            ['code' => 'R-CS', 'name' => 'Customer Service', 'description' => 'Inbox percakapan WhatsApp Omnichannel dan pipeline'],
        ];

        $roles = [];
        foreach ($rolesData as $r) {
            $roles[$r['code']] = Role::updateOrCreate(['code' => $r['code']], $r);
        }

        // 2. SEED PERMISSIONS PER 8 KELOMPOK MENU
        $permissionsData = [
            // Dashboard
            ['code' => 'dashboard.view', 'name' => 'Melihat Dashboard', 'module' => 'Dashboard'],
            ['code' => 'dashboard.financial', 'name' => 'Melihat Metrik Finansial Dashboard', 'module' => 'Dashboard'],
            
            // Core Bisnis - Document
            ['code' => 'document.view', 'name' => 'Melihat Dokumen Pabean', 'module' => 'Core Bisnis - Document'],
            ['code' => 'document.create', 'name' => 'Membuat Dokumen PIB/PEB', 'module' => 'Core Bisnis - Document'],
            ['code' => 'document.approve', 'name' => 'Menyetujui Dokumen Pabean', 'module' => 'Core Bisnis - Document'],
            ['code' => 'ceisa.submit', 'name' => 'Mengirim Dokumen ke CEISA 4.0', 'module' => 'Core Bisnis - Document'],

            // Core Bisnis - DO
            ['code' => 'do.view', 'name' => 'Melihat Data Delivery Order', 'module' => 'Core Bisnis - DO'],
            ['code' => 'do.manage', 'name' => 'Mengelola DO & Pelayaran', 'module' => 'Core Bisnis - DO'],

            // Core Bisnis - Traffic
            ['code' => 'traffic.view', 'name' => 'Melihat Data Traffic', 'module' => 'Core Bisnis - Traffic'],
            ['code' => 'traffic.manage', 'name' => 'Mengelola Gate Out & Vendor Trucking', 'module' => 'Core Bisnis - Traffic'],
            ['code' => 'traffic.behandle', 'name' => 'Eksekusi Pemeriksaan Lapangan (Behandle)', 'module' => 'Core Bisnis - Traffic'],

            // HRM
            ['code' => 'hrm.view', 'name' => 'Melihat Data Karyawan & Absensi', 'module' => 'HRM'],
            ['code' => 'hrm.manage', 'name' => 'Mengelola Pegawai, Cuti & Payroll', 'module' => 'HRM'],

            // CRM
            ['code' => 'crm.view', 'name' => 'Melihat CRM & Inbox WhatsApp', 'module' => 'CRM'],
            ['code' => 'crm.manage', 'name' => 'Membalas Pesan & Kelola Pipeline', 'module' => 'CRM'],

            // Finance
            ['code' => 'finance.view', 'name' => 'Melihat Tagihan & Pembayaran', 'module' => 'Finance'],
            ['code' => 'finance.manage', 'name' => 'Mengelola Keuangan & Penagihan', 'module' => 'Finance'],
            ['code' => 'finance.invoice', 'name' => 'Membuat & Mengelola Invoice Klien', 'module' => 'Finance'],
            ['code' => 'finance.pay', 'name' => 'Mencatat Pelunasan Tagihan Klien', 'module' => 'Finance'],
            ['code' => 'finance.bill', 'name' => 'Mengelola Tagihan Vendor Pelayaran & Ekspedisi', 'module' => 'Finance'],
            ['code' => 'finance.ap_approve', 'name' => 'Menyetujui Tagihan Hutang Vendor (AP Approval)', 'module' => 'Finance'],

            // Accounting
            ['code' => 'accounting.view', 'name' => 'Melihat Laporan Laba Rugi & Neraca', 'module' => 'Accounting'],
            ['code' => 'accounting.manage', 'name' => 'Mengelola COA & Jurnal Manual', 'module' => 'Accounting'],

            // AI Agent
            ['code' => 'ai.view', 'name' => 'Melihat Laporan Proaktif AI', 'module' => 'AI Agent'],
            ['code' => 'ai.approve', 'name' => 'Menyetujui Usulan Aksi AI Agent', 'module' => 'AI Agent'],

            // Setting
            ['code' => 'setting.view', 'name' => 'Melihat Pengaturan Sistem', 'module' => 'Setting'],
            ['code' => 'setting.manage', 'name' => 'Mengelola Pengguna & Hak Akses RBAC', 'module' => 'Setting'],
        ];

        $permissions = [];
        foreach ($permissionsData as $p) {
            $permissions[$p['code']] = Permission::updateOrCreate(['code' => $p['code']], $p);
        }

        // Hubungkan Permission ke Role
        // R-ADM & R-OWN get all
        $allPermissionIds = array_map(fn($p) => $p->id, $permissions);
        $roles['R-ADM']->permissions()->sync($allPermissionIds);
        $roles['R-OWN']->permissions()->sync($allPermissionIds);

        // R-DOC & R-DOC-SPV
        $roles['R-DOC']->permissions()->sync([
            $permissions['dashboard.view']->id,
            $permissions['document.view']->id,
            $permissions['document.create']->id,
        ]);
        $roles['R-DOC-SPV']->permissions()->sync([
            $permissions['dashboard.view']->id,
            $permissions['document.view']->id,
            $permissions['document.create']->id,
            $permissions['document.approve']->id,
            $permissions['ceisa.submit']->id,
        ]);

        // R-DO
        $roles['R-DO']->permissions()->sync([
            $permissions['dashboard.view']->id,
            $permissions['do.view']->id,
            $permissions['do.manage']->id,
        ]);

        // R-TRF & R-FLD
        $roles['R-TRF']->permissions()->sync([
            $permissions['dashboard.view']->id,
            $permissions['traffic.view']->id,
            $permissions['traffic.manage']->id,
        ]);
        $roles['R-FLD']->permissions()->sync([
            $permissions['traffic.view']->id,
            $permissions['traffic.behandle']->id,
        ]);

        // R-FIN
        $roles['R-FIN']->permissions()->sync([
            $permissions['dashboard.view']->id,
            $permissions['dashboard.financial']->id,
            $permissions['finance.view']->id,
            $permissions['finance.manage']->id,
            $permissions['finance.invoice']->id,
            $permissions['finance.pay']->id,
            $permissions['finance.bill']->id,
            $permissions['accounting.view']->id,
            $permissions['accounting.manage']->id,
        ]);

        // R-HR
        $roles['R-HR']->permissions()->sync([
            $permissions['dashboard.view']->id,
            $permissions['hrm.view']->id,
            $permissions['hrm.manage']->id,
        ]);

        // R-CS & R-AM
        $roles['R-CS']->permissions()->sync([
            $permissions['dashboard.view']->id,
            $permissions['crm.view']->id,
            $permissions['crm.manage']->id,
        ]);
        $roles['R-AM']->permissions()->sync([
            $permissions['dashboard.view']->id,
            $permissions['document.view']->id,
            $permissions['crm.view']->id,
            $permissions['crm.manage']->id,
        ]);

        // 3. SEED AKUN PENGGUNA RESMI PER PERAN
        $usersData = [
            [
                'name' => 'Administrator Sistem',
                'email' => 'admin@agungtrans.co.id',
                'password' => Hash::make('password'),
                'job_title' => 'System Administrator & IT Lead',
                'department' => 'IT & Gateway Support',
                'phone' => '08129990001',
                'role' => 'R-ADM',
            ],
            [
                'name' => 'Soehartono',
                'email' => 'soehartono@agungtrans.co.id',
                'password' => Hash::make('password'),
                'job_title' => 'Direktur Utama / Pimpinan',
                'department' => 'Executive Management',
                'phone' => '08119876543',
                'role' => 'R-OWN',
            ],
            [
                'name' => 'Rian Hidayat, S.E.',
                'email' => 'spv.doc@agungtrans.co.id',
                'password' => Hash::make('password'),
                'job_title' => 'Supervisor Kepabeanan & Dokumen',
                'department' => 'Customs & Documentation',
                'phone' => '08123456789',
                'role' => 'R-DOC-SPV',
            ],
            [
                'name' => 'Dewi Anggraini',
                'email' => 'doc.staff@agungtrans.co.id',
                'password' => Hash::make('password'),
                'job_title' => 'Staff Dokumen Pabean (PIB/PEB)',
                'department' => 'Customs & Documentation',
                'phone' => '08123456780',
                'role' => 'R-DOC',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'do.staff@agungtrans.co.id',
                'password' => Hash::make('password'),
                'job_title' => 'Staff Delivery Order & Pelayaran',
                'department' => 'Shipping Line & DO',
                'phone' => '08123456781',
                'role' => 'R-DO',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'traffic.staff@agungtrans.co.id',
                'password' => Hash::make('password'),
                'job_title' => 'Staff Traffic & Trucking Dispatcher',
                'department' => 'Traffic & Port Logistics',
                'phone' => '08123456782',
                'role' => 'R-TRF',
            ],
            [
                'name' => 'Hendra Setiawan',
                'email' => 'field.staff@agungtrans.co.id',
                'password' => Hash::make('password'),
                'job_title' => 'Petugas Lapangan & Behandle TPS',
                'department' => 'Port Field Operations',
                'phone' => '08123456783',
                'role' => 'R-FLD',
            ],
            [
                'name' => 'Ir. Gunawan Wibowo',
                'email' => 'manager.ops@agungtrans.co.id',
                'password' => Hash::make('password'),
                'job_title' => 'Operations Manager',
                'department' => 'Operations Management',
                'phone' => '08123456784',
                'role' => 'R-MGR',
            ],
            [
                'name' => 'Siti Nurhaliza, Ak.',
                'email' => 'finance@agungtrans.co.id',
                'password' => Hash::make('password'),
                'job_title' => 'Senior Finance & Tax Officer',
                'department' => 'Finance & Accounting',
                'phone' => '08123456785',
                'role' => 'R-FIN',
            ],
            [
                'name' => 'Maya Kartika, S.Psi.',
                'email' => 'hr@agungtrans.co.id',
                'password' => Hash::make('password'),
                'job_title' => 'HR & General Affairs Specialist',
                'department' => 'Human Resources',
                'phone' => '08123456786',
                'role' => 'R-HR',
            ],
            [
                'name' => 'Putri Wulandari',
                'email' => 'cs@agungtrans.co.id',
                'password' => Hash::make('password'),
                'job_title' => 'Customer Care & Omnichannel Specialist',
                'department' => 'Customer Service',
                'phone' => '08123456787',
                'role' => 'R-CS',
            ],
            [
                'name' => 'Denny Pratama',
                'email' => 'am@agungtrans.co.id',
                'password' => Hash::make('password'),
                'job_title' => 'Senior Account Manager Forwarding',
                'department' => 'Commercial & Account Management',
                'phone' => '08123456788',
                'role' => 'R-AM',
            ],
        ];

        foreach ($usersData as $u) {
            $roleCode = $u['role'];
            unset($u['role']);

            $user = User::updateOrCreate(['email' => $u['email']], $u);
            if (isset($roles[$roleCode])) {
                $user->roles()->sync([$roles[$roleCode]->id]);
            }
        }

        // 4. SEED SAMPLE DOKUMEN PABEAN BC 2.0 & BC 3.0 JIKA BELUM ADA
        if (CeisaDocument::count() === 0) {
            $bc20Data = [
                'kode_kantor' => '040300',
                'ndbm_kurs' => 15850.00,
                'kode_valuta' => 'USD',
                'fob' => 25000.00,
                'freight' => 1200.00,
                'asuransi' => 300.00,
                'bruto' => 4500.00,
                'netto' => 4100.00,
                'supplier_nama' => 'SHENZHEN TECH INNOVATION CO., LTD.',
                'supplier_negara' => 'CN',
                'invoice_no' => 'INV-SZ-2026-8819',
                'invoice_date' => '2026-10-08',
                'bl_no' => 'COSU928104829',
                'nama_sarana_pengangkut' => 'COSCO SHANGHAI VOY. 219E',
                'pelabuhan_muat' => 'CNSHA',
                'pelabuhan_bongkar' => 'IDTPP',
                'pelabuhan_tujuan' => 'IDTPP',
                'barang' => [
                    [
                        'hs_code' => '84713020',
                        'uraian' => 'Laptop Komputer Portabel Layar 14 Inci Core i7',
                        'merk' => 'AGUNG-BOOK',
                        'tipe' => 'ULTRA-14',
                        'jumlah' => 100,
                        'satuan' => 'C62',
                        'netto' => 250,
                        'cif' => 26500.00,
                        'tarif_bm' => 0,
                        'tarif_ppn' => 11,
                        'tarif_pph' => 2.5,
                    ],
                ],
            ];

            $nomorAjuBC20 = '04032001234520261012000001';
            $bc20Data['nomor_aju'] = $nomorAjuBC20;
            $payloadBC20 = DocumentBC20Service::buildPayload($bc20Data);

            CeisaDocument::create([
                'nomor_aju' => $nomorAjuBC20,
                'jenis_dokumen' => '20',
                'nomor_daftar' => '019821',
                'tanggal_daftar' => '2026-10-12',
                'kode_kantor' => '040300',
                'nama_kantor' => 'KPU Bea dan Cukai Tipe A Tanjung Priok',
                'status' => 'SPPB',
                'status_description' => 'Persetujuan Pengeluaran Barang (SPPB) Diterbitkan (Jalur Hijau)',
                'jalur' => 'HIJAU',
                'kode_valuta' => 'USD',
                'ndbm_kurs' => 15850.00,
                'total_nilai' => 26500.00,
                'total_nilai_idr' => 420025000.00,
                'total_bruto' => 4500.00,
                'total_netto' => 4100.00,
                'total_kemasan' => 100,
                'total_kontainer' => 1,
                'total_barang' => 1,
                'nama_perusahaan_lokal' => config('ceisa.company.name'),
                'npwp_perusahaan_lokal' => config('ceisa.company.npwp'),
                'nama_mitra_luar_negeri' => 'SHENZHEN TECH INNOVATION CO., LTD.',
                'negara_mitra_luar_negeri' => 'CN',
                'kode_billing' => '820260918237192',
                'total_billing' => 56703375.00,
                'tgl_jatuh_tempo_billing' => now()->addDays(3),
                'status_billing' => 'PAID',
                'nomor_sppb_npe' => 'SPPB-040300-2026-08129',
                'tanggal_sppb_npe' => now(),
                'raw_payload' => $payloadBC20,
                'last_response' => [
                    'status' => 'SPPB',
                    'nomorDaftar' => '019821',
                    'jalur' => 'HIJAU',
                    'nomorSppb' => 'SPPB-040300-2026-08129',
                ],
            ]);

            $nomorAjuBC30 = '04033001234520261012000001';
            $bc30Data = [
                'kode_kantor' => '040300',
                'ndbm_kurs' => 15850.00,
                'kode_valuta' => 'USD',
                'fob' => 50000.00,
                'freight' => 2500.00,
                'asuransi' => 500.00,
                'bruto' => 12500.00,
                'netto' => 11800.00,
                'buyer_nama' => 'PACIFIC DISTRIBUTORS & COFFEE ROASTERS INC',
                'buyer_negara' => 'US',
                'invoice_no' => 'EXP-INV-2026-0928',
                'invoice_date' => '2026-10-10',
                'nama_sarana_pengangkut' => 'MAERSK MC-KINNEY MOLLER VOY. 2601W',
                'pelabuhan_muat' => 'IDTPP',
                'pelabuhan_bongkar' => 'USLAX',
                'pelabuhan_tujuan' => 'USLAX',
                'barang' => [
                    [
                        'hs_code' => '09012110',
                        'uraian' => 'Kopi Arabika Roasting Kemasan Retail Asli Indonesia',
                        'merk' => 'NUSANTARA COFFEE',
                        'jumlah' => 5000,
                        'satuan' => 'KGM',
                        'netto' => 5000,
                        'fob' => 50000.00,
                        'tarif_bk' => 0,
                        'kode_daerah_asal' => '3201',
                    ],
                ],
            ];
            $bc30Data['nomor_aju'] = $nomorAjuBC30;
            $payloadBC30 = DocumentBC30Service::buildPayload($bc30Data);

            CeisaDocument::create([
                'nomor_aju' => $nomorAjuBC30,
                'jenis_dokumen' => '30',
                'nomor_daftar' => '008923',
                'tanggal_daftar' => '2026-10-12',
                'kode_kantor' => '040300',
                'nama_kantor' => 'KPU Bea dan Cukai Tipe A Tanjung Priok',
                'status' => 'NPE',
                'status_description' => 'Nota Pelayanan Ekspor (NPE) Diterbitkan. Barang dizinkan dimuat ke sarana pengangkut.',
                'jalur' => 'HIJAU',
                'kode_valuta' => 'USD',
                'ndbm_kurs' => 15850.00,
                'total_nilai' => 50000.00,
                'total_nilai_idr' => 792500000.00,
                'total_bruto' => 12500.00,
                'total_netto' => 11800.00,
                'total_kemasan' => 50,
                'total_kontainer' => 1,
                'total_barang' => 1,
                'nama_perusahaan_lokal' => config('ceisa.company.name'),
                'npwp_perusahaan_lokal' => config('ceisa.company.npwp'),
                'nama_mitra_luar_negeri' => 'PACIFIC DISTRIBUTORS & COFFEE ROASTERS INC',
                'negara_mitra_luar_negeri' => 'US',
                'nomor_sppb_npe' => 'NPE-040300-2026-00491',
                'tanggal_sppb_npe' => now(),
                'raw_payload' => $payloadBC30,
                'last_response' => [
                    'status' => 'NPE',
                    'nomorDaftar' => '008923',
                    'jalur' => 'HIJAU',
                    'nomorNpe' => 'NPE-040300-2026-00491',
                ],
            ]);
        }

        // 5. SEED MASTER CLIENTS
        $clients = [
            [
                'code' => 'CLI-001',
                'name' => 'PT SINAR JAYA TEKSTIL INDONESIA',
                'npwp' => '01.234.567.8-011.000',
                'nib' => '9120001234567',
                'email' => 'import@sinarjaya-textile.co.id',
                'phone' => '021-58900122',
                'pic_name' => 'Bapak Irwan Santoso',
                'address' => 'Kawasan Industri MM2100 Blok C-4, Cikarang Barat, Bekasi',
                'surat_kuasa_expired_at' => now()->addMonths(12),
            ],
            [
                'code' => 'CLI-002',
                'name' => 'PT SUMBER BERKAH ELEKTRONIK',
                'npwp' => '02.345.678.9-022.000',
                'nib' => '9120002345678',
                'email' => 'purchasing@sumberberkah.com',
                'phone' => '021-65400991',
                'pic_name' => 'Ibu Linda Wijaya',
                'address' => 'Jl. Mangga Dua Raya No. 45, Jakarta Pusat',
                'surat_kuasa_expired_at' => now()->addMonths(8),
            ],
            [
                'code' => 'CLI-003',
                'name' => 'PT MAJU BERSAMA AGRO NUSANTARA',
                'npwp' => '03.456.789.0-033.000',
                'nib' => '9120003456789',
                'email' => 'export@majubersama-agro.id',
                'phone' => '021-78900334',
                'pic_name' => 'Bapak Hendra Gunawan',
                'address' => 'Kawasan Industri Kendal Kav. 12, Jawa Tengah',
                'surat_kuasa_expired_at' => now()->addMonths(10),
            ],
        ];

        $clientModels = [];
        foreach ($clients as $c) {
            $clientModels[] = \App\Models\Client::updateOrCreate(['code' => $c['code']], $c);
        }

        // 6. SEED MASTER SHIPPING VENDORS (PELAYARAN)
        $vendors = [
            [
                'code' => 'MAERSK',
                'name' => 'PT MAERSK LINE INDONESIA',
                'pic_name' => 'Customer Service Desk & DO Team',
                'pic_email' => 'import.cs@maersk.com',
                'pic_phone' => '021-30005500',
                'portal_url' => 'https://www.maersk.com',
                'address' => 'World Trade Centre 3 Lt. 18, Jl. Jend. Sudirman, Jakarta Selatan',
                'bank_name' => 'CITIBANK JAKARTA',
                'bank_account_no' => '0-100234-012',
                'bank_account_name' => 'PT MAERSK LINE INDONESIA',
            ],
            [
                'code' => 'ONE',
                'name' => 'OCEAN NETWORK EXPRESS (ONE) INDONESIA',
                'pic_name' => 'Documentation & DO Release Dept',
                'pic_email' => 'id.import.doc@one-line.com',
                'pic_phone' => '021-50820000',
                'portal_url' => 'https://www.one-line.com',
                'address' => 'Menara Astra Lt. 29, Jl. Jend. Sudirman, Jakarta Pusat',
                'bank_name' => 'BCA CABANG SUDIRMAN',
                'bank_account_no' => '035-3998877',
                'bank_account_name' => 'PT OCEAN NETWORK EXPRESS INDONESIA',
            ],
            [
                'code' => 'EVERGREEN',
                'name' => 'PT EVERGREEN SHIPPING AGENCY INDONESIA',
                'pic_name' => 'Ibu Vina / Counter DO Release',
                'pic_email' => 'jkt-import-release@evergreen-shipping.co.id',
                'pic_phone' => '021-25508800',
                'portal_url' => 'https://www.evergreen-marine.com',
                'address' => 'Plaza Bapindo Citibank Tower Lt. 15, Jakarta Selatan',
                'bank_name' => 'BANK MANDIRI CABANG PLAZA BAPINDO',
                'bank_account_no' => '102-00-9876543-2',
                'bank_account_name' => 'PT EVERGREEN SHIPPING AGENCY INDONESIA',
            ],
            [
                'code' => 'COSCO',
                'name' => 'COSCO SHIPPING LINES (INDONESIA)',
                'pic_name' => 'Bapak Denny / Import Billing',
                'pic_email' => 'jkt.import@coscon.com',
                'pic_phone' => '021-38902200',
                'portal_url' => 'https://lines.coscoshipping.com',
                'address' => 'Gedung Wisma Antara Lt. 8, Medan Merdeka Selatan, Jakarta',
                'bank_name' => 'BANK BCA CABANG KARTINI',
                'bank_account_no' => '008-8123344',
                'bank_account_name' => 'PT COSCO SHIPPING LINES INDONESIA',
            ],
            [
                'code' => 'SAMUDERA',
                'name' => 'PT SAMUDERA INDONESIA SHIP MANAGEMENT',
                'pic_name' => 'Tim Operasional Pelabuhan Tanjung Priok',
                'pic_email' => 'cs.liner@samudera.id',
                'pic_phone' => '021-4301234',
                'portal_url' => 'https://www.samudera.id',
                'address' => 'Gedung Samudera Indonesia, Tanjung Priok, Jakarta Utara',
                'bank_name' => 'BANK MANDIRI CABANG TANJUNG PRIOK',
                'bank_account_no' => '120-00-0988776-5',
                'bank_account_name' => 'PT SAMUDERA INDONESIA TBK',
            ],
        ];

        $vendorModels = [];
        foreach ($vendors as $v) {
            $vendorModels[] = \App\Models\ShippingVendor::updateOrCreate(['code' => $v['code']], $v);
        }

        // 7. SEED INITIAL WORKFLOW SHIPMENTS (PROYEK IMPORT BC 2.0 & EKSPORT BC 3.0)
        $sampleShipment = \App\Models\Shipment::updateOrCreate(
            ['project_no' => 'SHP-202610-0001'],
            [
                'project_no' => 'SHP-202610-0001',
                'client_id' => $clientModels[0]->id,
                'jenis_layanan' => 'IMPORT_BC20',
                'nomor_bl_awb' => 'WHLU-SHAPRIOK-98124',
                'tgl_bl_awb' => now()->subDays(5),
                'shipping_vendor_id' => $vendorModels[0]->id,
                'nama_kapal' => 'MV WAN HAI 315',
                'voyage_no' => 'V.042N',
                'pelabuhan_muat' => 'CNSHA (Shanghai Port)',
                'pelabuhan_bongkar' => 'IDTPP (KPU Tanjung Priok)',
                'eta_date' => now()->addDays(2),
                'total_kontainer' => 1,
                'nomor_kontainer' => 'WHLU-9182341 (1x40HC)',
                'total_kemasan' => 150,
                'jenis_kemasan' => 'CTN (Cartons)',
                'total_bruto' => 8500.00,
                'total_netto' => 7800.00,
                'kode_valuta' => 'USD',
                'total_nilai_valuta' => 25000.00,
                'ndbm_kurs' => 16200.00,
                'total_nilai_idr' => 405000000.00,
                'free_time_days' => 7,
                'free_time_expired_at' => now()->addDays(9),
                'do_expired_at' => now()->addDays(12),
                'nomor_do' => 'DO/MAEU/202610/00918',
                'status_do' => 'EMAIL_SENT',
                'do_email_sent_at' => now()->subHours(4),
                'do_email_recipient' => 'import.cs@maersk.com',
                'status_tahap' => 'BILLING_ISSUED',
                'catatan' => 'Billing MPN G3 telah terbit dari CEISA 4.0. Menunggu konfirmasi pembayaran invoice pelayaran dari Maersk Line.',
            ]
        );

        // Seed Checklist Dokumen untuk Sample Shipment
        $slots = [
            'INVOICE' => ['Commercial Invoice No: INV-SH-2026-081', 'VERIFIED'],
            'PACKING_LIST' => ['Packing List No: PL-SH-2026-081', 'VERIFIED'],
            'BL_AWB' => ['Bill of Lading WHLU-SHAPRIOK-98124', 'VERIFIED'],
            'COO_FORME' => ['Form E No: E260421008129', 'VERIFIED'],
            'INSURANCE' => ['Marine Cargo Insurance Policy', 'VERIFIED'],
            'PERIJINAN_LARTAS' => ['Bebas Lartas (Non-Lartas Komoditas Garmen)', 'VERIFIED'],
            'DO_ORIGINAL' => ['DO Maersk Line (Menunggu Pelunasan Invoice Pelayaran)', 'UPLOADED'],
            'BUKTI_BAYAR_BILLING' => ['BPN Kode Billing 040300261000123', 'UPLOADED'],
            'POD_SURAT_JALAN' => ['Menunggu Gate Out Kontainer di Pelabuhan', 'MISSING'],
        ];

        foreach ($slots as $type => $info) {
            \App\Models\ShipmentDocument::updateOrCreate(
                ['shipment_id' => $sampleShipment->id, 'jenis_dokumen' => $type],
                [
                    'shipment_id' => $sampleShipment->id,
                    'jenis_dokumen' => $type,
                    'nama_file' => strtolower($type) . '_SHP-202610-0001.pdf',
                    'file_path' => 'simulated/' . strtolower($type) . '.pdf',
                    'file_size_kb' => 350,
                    'status' => $info[1],
                    'catatan' => $info[0],
                ]
            );
        }

        // Seed Shipment Items (Hasil Deteksi HS Code)
        \App\Models\ShipmentItem::updateOrCreate(
            ['shipment_id' => $sampleShipment->id, 'nomor_urut' => 1],
            [
                'shipment_id' => $sampleShipment->id,
                'nomor_urut' => 1,
                'uraian_barang' => 'INDUSTRIAL SEWING MACHINE HEAVY DUTY COMPLETE SET (MODEL: DDL-8700)',
                'kode_hs' => '8452.29.00',
                'hs_confidence_score' => 0.98,
                'hs_legal_basis' => 'BTKI 2022 Pos 8452 - Mesin jahit industri (BM 5%, PPN 11%, PPh 2.5%)',
                'negara_asal' => 'CN',
                'jumlah_kemasan' => 50,
                'jenis_kemasan' => 'CTN',
                'berat_netto' => 2500.00,
                'berat_bruto' => 2800.00,
                'nilai_fob' => 12500.00,
                'tarif_bm_persen' => 5.0,
                'tarif_ppn_persen' => 11.0,
                'tarif_pph_persen' => 2.5,
            ]
        );

        \App\Models\ShipmentItem::updateOrCreate(
            ['shipment_id' => $sampleShipment->id, 'nomor_urut' => 2],
            [
                'shipment_id' => $sampleShipment->id,
                'nomor_urut' => 2,
                'uraian_barang' => 'SPARE PARTS AND STEEL NEEDLES FOR INDUSTRIAL SEWING MACHINES',
                'kode_hs' => '8452.90.00',
                'hs_confidence_score' => 0.95,
                'hs_legal_basis' => 'BTKI 2022 Pos 8452.90 - Bagian mesin jahit (BM 0%, PPN 11%, PPh 2.5%)',
                'negara_asal' => 'CN',
                'jumlah_kemasan' => 100,
                'jenis_kemasan' => 'BOX',
                'berat_netto' => 5300.00,
                'berat_bruto' => 5700.00,
                'nilai_fob' => 12500.00,
                'tarif_bm_persen' => 0.0,
                'tarif_ppn_persen' => 11.0,
                'tarif_pph_persen' => 2.5,
            ]
        );

        // 8. SEED SAMPLE QUOTATION (SURAT PENAWARAN HARGA LOGISTIK)
        \App\Models\Quotation::updateOrCreate(
            ['quotation_no' => 'QUO-202610-0001'],
            [
                'quotation_no' => 'QUO-202610-0001',
                'client_id' => $clientModels[0]->id,
                'tanggal' => now()->subDays(10),
                'valid_until' => now()->addDays(20),
                'pelabuhan_asal' => 'CNSHA (Shanghai Port, China)',
                'pelabuhan_tujuan' => 'IDTPP (Tanjung Priok, Jakarta)',
                'term_of_shipment' => 'FOB / CY-CY 1x40HC',
                'rincian_biaya' => [
                    ['komponen' => 'Ocean Freight (Shanghai - Jakarta 1x40HC)', 'biaya' => 12500000],
                    ['komponen' => 'Terminal Handling Charges (THC) Pelabuhan', 'biaya' => 2150000],
                    ['komponen' => 'Penebusan Delivery Order (DO Fee) Pelayaran', 'biaya' => 850000],
                    ['komponen' => 'Jasa Pengurusan Kepabeanan PPJK (PIB BC 2.0)', 'biaya' => 1500000],
                    ['komponen' => 'Trucking Trailer Pengiriman (Priok - Cikarang)', 'biaya' => 2500000],
                ],
                'subtotal_idr' => 19500000.00,
                'ppn_idr' => 165000.00, // 11% dari Jasa PPJK
                'total_idr' => 19665000.00,
                'status' => 'ACCEPTED',
                'syarat_ketentuan' => 'Tarif sudah termasuk jasa PPJK dan trucking. Belum termasuk Bea Masuk dan Pajak Impor resmi yang ditagihkan sesuai Billing DJBC.',
            ]
        );

        // 9. SEED SAMPLE CLIENT INVOICE P19
        \App\Models\ClientInvoice::updateOrCreate(
            ['invoice_no' => 'INV-202610-0001'],
            [
                'invoice_no' => 'INV-202610-0001',
                'shipment_id' => $sampleShipment->id,
                'client_id' => $clientModels[0]->id,
                'tanggal' => now(),
                'jatuh_tempo' => now()->addDays(14),
                'talangan_pabean_idr' => 18500000.00,
                'biaya_pelayaran_idr' => 3850000.00,
                'jasa_ppjk_idr' => 1500000.00,
                'biaya_trucking_idr' => 2500000.00,
                'subtotal_idr' => 26350000.00,
                'ppn_jasa_idr' => 165000.00,
                'total_tagihan_idr' => 26515000.00,
                'status' => 'UNPAID',
                'catatan_pembayaran' => 'Pembayaran ditransfer ke rekening Mandiri PT Agung Trans Solusindo: 120-00-1122334-5.',
            ]
        );

        // 9. HRM & PAYROLL SEEDER (F8)
        $this->call(HrmSeeder::class);

        // 10. FINANCE & ACCOUNTING SEEDERS (F7)
        $this->call(ChartOfAccountSeeder::class);
        $this->call(CashBankAccountSeeder::class);
        $this->call(VendorBillSeeder::class);

        // 11. MASTER HS CODE & BTKI 2022 BEA CUKAI (F2 & CEISA 4.0)
        $this->call(Btki2022DatabaseSeeder::class);
    }
}

