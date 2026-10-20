<?php

namespace Database\Seeders;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use Illuminate\Database\Seeder;

class CashBankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Inisialisasi 3 Rekening Kas & Bank Dinamis beserta Transaksi Saldo Awal.
     */
    public function run(): void
    {
        $accountsData = [
            [
                'name' => 'Bank Central Asia (BCA) Operasional',
                'bank' => 'Bank Central Asia (BCA)',
                'account_no' => '084-3928172',
                'initial_balance' => 450000000.00,
                'memo' => 'Saldo awal modal kerja rekening operasional & penagihan klien BCA',
            ],
            [
                'name' => 'Bank Mandiri Khusus Pabean (MPN G3)',
                'bank' => 'Bank Mandiri',
                'account_no' => '120-00-1928374-1',
                'initial_balance' => 380000000.00,
                'memo' => 'Saldo awal kuota talangan billing pabean MPN G3 Bea Cukai',
            ],
            [
                'name' => 'Kas Kecil Kantor Tanjung Priok',
                'bank' => 'Kas Fisik Tunai',
                'account_no' => 'KAS-PRIOK-01',
                'initial_balance' => 20000000.00,
                'memo' => 'Saldo awal kas kecil operasional lapangan & administrasi Priok',
            ],
        ];

        foreach ($accountsData as $data) {
            $account = CashBankAccount::updateOrCreate(
                ['account_no' => $data['account_no']],
                [
                    'name' => $data['name'],
                    'bank' => $data['bank'],
                ]
            );

            // Inisialisasi saldo awal jika belum ada transaksi mutasi
            if ($account->transactions()->count() === 0) {
                CashBankTransaction::create([
                    'account_id' => $account->id,
                    'trx_date' => now()->startOfMonth(),
                    'amount' => $data['initial_balance'],
                    'description' => $data['memo'],
                    'reconciled' => true,
                    'ref_type' => 'OPENING_BALANCE',
                ]);
            }
        }
    }
}
