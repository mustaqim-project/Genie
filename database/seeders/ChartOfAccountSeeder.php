<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Standarisasi 35 Bagan Akun COA Logistik, Kepabeanan (PPJK) & Freight Forwarding Indonesia.
     */
    public function run(): void
    {
        $accounts = [
            // 1. ASET (1-xxxx)
            ['code' => '1-1000', 'name' => 'Aset Lancar', 'type' => 'ASSET', 'parent_code' => null],
            ['code' => '1-1110', 'name' => 'Kas Kecil & Kas Operasional', 'type' => 'ASSET', 'parent_code' => '1-1000'],
            ['code' => '1-1120', 'name' => 'Bank Mandiri (Billing Pabean MPN G3)', 'type' => 'ASSET', 'parent_code' => '1-1000'],
            ['code' => '1-1130', 'name' => 'Bank BCA (Operasional & Penagihan)', 'type' => 'ASSET', 'parent_code' => '1-1000'],
            ['code' => '1-1200', 'name' => 'Piutang Usaha Klien (AR)', 'type' => 'ASSET', 'parent_code' => '1-1000'],
            ['code' => '1-1300', 'name' => 'Piutang Talangan Pabean (Reimbursable)', 'type' => 'ASSET', 'parent_code' => '1-1000'],
            ['code' => '1-1400', 'name' => 'Uang Muka Vendor Pelayaran & Trucking', 'type' => 'ASSET', 'parent_code' => '1-1000'],
            ['code' => '1-1500', 'name' => 'PPN Masukan (Faktur Pajak Masukan 11%)', 'type' => 'ASSET', 'parent_code' => '1-1000'],
            
            ['code' => '1-2000', 'name' => 'Aset Tetap', 'type' => 'ASSET', 'parent_code' => null],
            ['code' => '1-2100', 'name' => 'Peralatan & Inventaris Kantor', 'type' => 'ASSET', 'parent_code' => '1-2000'],
            ['code' => '1-2110', 'name' => 'Kendaraan Operasional & Logistik', 'type' => 'ASSET', 'parent_code' => '1-2000'],
            ['code' => '1-2200', 'name' => 'Akumulasi Penyusutan Aset Tetap', 'type' => 'ASSET', 'parent_code' => '1-2000'],

            // 2. KEWAJIBAN & HUTANG (2-xxxx)
            ['code' => '2-1000', 'name' => 'Kewajiban Lancar', 'type' => 'LIABILITY', 'parent_code' => null],
            ['code' => '2-1100', 'name' => 'Hutang Usaha Vendor Pelayaran (THC/DO/Demurrage)', 'type' => 'LIABILITY', 'parent_code' => '2-1000'],
            ['code' => '2-1110', 'name' => 'Hutang Usaha Vendor Trucking Inland', 'type' => 'LIABILITY', 'parent_code' => '2-1000'],
            ['code' => '2-1120', 'name' => 'Hutang Terminal Petikemas TPS', 'type' => 'LIABILITY', 'parent_code' => '2-1000'],
            ['code' => '2-1200', 'name' => 'Titipan Uang Muka Pabean Klien (Deposit)', 'type' => 'LIABILITY', 'parent_code' => '2-1000'],
            ['code' => '2-1300', 'name' => 'Hutang Biaya Akrual & Operasional', 'type' => 'LIABILITY', 'parent_code' => '2-1000'],
            ['code' => '2-1600', 'name' => 'Hutang PPN Keluaran (PPN Jasa 1.1% PMK 71/2022)', 'type' => 'LIABILITY', 'parent_code' => '2-1000'],
            ['code' => '2-1700', 'name' => 'Hutang PPh Pasal 23 Forwarding (2%)', 'type' => 'LIABILITY', 'parent_code' => '2-1000'],
            ['code' => '2-1800', 'name' => 'Hutang PPh Pasal 21 Karyawan', 'type' => 'LIABILITY', 'parent_code' => '2-1000'],

            // 3. EKUITAS & MODAL (3-xxxx)
            ['code' => '3-1000', 'name' => 'Ekuitas Pemilik', 'type' => 'EQUITY', 'parent_code' => null],
            ['code' => '3-1100', 'name' => 'Modal Disetor Saham', 'type' => 'EQUITY', 'parent_code' => '3-1000'],
            ['code' => '3-2100', 'name' => 'Laba Ditahan (Retained Earnings)', 'type' => 'EQUITY', 'parent_code' => '3-1000'],
            ['code' => '3-3100', 'name' => 'Laba Tahun Berjalan', 'type' => 'EQUITY', 'parent_code' => '3-1000'],

            // 4. PENDAPATAN RESMI (4-xxxx)
            ['code' => '4-1000', 'name' => 'Pendapatan Usaha Logistik & PPJK', 'type' => 'REVENUE', 'parent_code' => null],
            ['code' => '4-1100', 'name' => 'Pendapatan Jasa Pengurusan Kepabeanan (PPJK Fee)', 'type' => 'REVENUE', 'parent_code' => '4-1000'],
            ['code' => '4-1200', 'name' => 'Pendapatan Jasa Trucking & Angkutan Pelabuhan', 'type' => 'REVENUE', 'parent_code' => '4-1000'],
            ['code' => '4-1300', 'name' => 'Pendapatan Handling & Storage Recovery', 'type' => 'REVENUE', 'parent_code' => '4-1000'],
            ['code' => '4-1400', 'name' => 'Pendapatan Freight & Margin Pelayaran', 'type' => 'REVENUE', 'parent_code' => '4-1000'],
            ['code' => '4-1500', 'name' => 'Pendapatan Jasa Konsultasi & Perizinan Lartas', 'type' => 'REVENUE', 'parent_code' => '4-1000'],
            ['code' => '4-2100', 'name' => 'Pendapatan Bunga Bank & Jasa Giro', 'type' => 'REVENUE', 'parent_code' => null],

            // 5. BEBAN POKOK HPP (5-1xxx)
            ['code' => '5-1000', 'name' => 'Beban Pokok Operasional Forwarding (HPP)', 'type' => 'EXPENSE', 'parent_code' => null],
            ['code' => '5-1100', 'name' => 'Beban Penebusan DO & Biaya Pelayaran Resmi', 'type' => 'EXPENSE', 'parent_code' => '5-1000'],
            ['code' => '5-1200', 'name' => 'Beban Subkon Vendor Trucking Inland', 'type' => 'EXPENSE', 'parent_code' => '5-1000'],
            ['code' => '5-1300', 'name' => 'Beban Terminal Petikemas TPS & Lift On/Off', 'type' => 'EXPENSE', 'parent_code' => '5-1000'],
            ['code' => '5-1400', 'name' => 'Beban Lapangan TPS & Jasa Behandle', 'type' => 'EXPENSE', 'parent_code' => '5-1000'],

            // 6. BEBAN OPERASIONAL & UMUM (5-2xxx s.d. 5-4xxx)
            ['code' => '5-2000', 'name' => 'Beban Usaha & Administrasi Umum', 'type' => 'EXPENSE', 'parent_code' => null],
            ['code' => '5-2100', 'name' => 'Beban Gaji & Upah Karyawan', 'type' => 'EXPENSE', 'parent_code' => '5-2000'],
            ['code' => '5-2110', 'name' => 'Beban BPJS Ketenagakerjaan & Kesehatan', 'type' => 'EXPENSE', 'parent_code' => '5-2000'],
            ['code' => '5-2200', 'name' => 'Beban Sewa Kantor & Fasilitas', 'type' => 'EXPENSE', 'parent_code' => '5-2000'],
            ['code' => '5-2300', 'name' => 'Beban Utilitas (Listrik, Air, Internet)', 'type' => 'EXPENSE', 'parent_code' => '5-2000'],
            ['code' => '5-2400', 'name' => 'Beban Perlengkapan & Pemeliharaan Kantor', 'type' => 'EXPENSE', 'parent_code' => '5-2000'],
            ['code' => '5-3100', 'name' => 'Beban Pemasaran, Jamuan & Perjalanan Dinas', 'type' => 'EXPENSE', 'parent_code' => null],
            ['code' => '5-4100', 'name' => 'Beban Penyusutan Aset Tetap', 'type' => 'EXPENSE', 'parent_code' => null],
        ];

        // 1. Masukkan akun utama (parent) terlebih dahulu
        $createdMap = [];
        foreach ($accounts as $acc) {
            if ($acc['parent_code'] === null) {
                $coa = ChartOfAccount::updateOrCreate(
                    ['code' => $acc['code']],
                    [
                        'name' => $acc['name'],
                        'type' => $acc['type'],
                        'parent_id' => null,
                    ]
                );
                $createdMap[$acc['code']] = $coa->id;
            }
        }

        // 2. Masukkan sub-akun (child) dengan parent_id tertaut
        foreach ($accounts as $acc) {
            if ($acc['parent_code'] !== null) {
                $parentId = $createdMap[$acc['parent_code']] ?? null;
                $coa = ChartOfAccount::updateOrCreate(
                    ['code' => $acc['code']],
                    [
                        'name' => $acc['name'],
                        'type' => $acc['type'],
                        'parent_id' => $parentId,
                    ]
                );
                $createdMap[$acc['code']] = $coa->id;
            }
        }
    }
}
