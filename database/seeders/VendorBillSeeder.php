<?php

namespace Database\Seeders;

use App\Models\Approval;
use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use App\Models\Shipment;
use App\Models\VendorBill;
use App\Models\VendorPayment;
use Illuminate\Database\Seeder;

class VendorBillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder Tagihan Vendor Realistis (Pelayaran, Trucking, TPS).
     */
    public function run(): void
    {
        $shipment = Shipment::first();
        $bankBca = CashBankAccount::where('account_no', '084-3928172')->first();

        // 1. Tagihan Pelayaran Wan Hai Lines (Rp 14.500.000 - Melebihi Limit Rp 10 Jt -> PENDING Approval Pimpinan)
        $bill1 = VendorBill::updateOrCreate(
            ['ref_no' => 'INV-WHL-2026-9182'],
            [
                'vendor_type' => 'SHIPPING_LINE',
                'shipment_id' => $shipment?->id,
                'amount' => 14500000.00,
                'due_date' => now()->addDays(5),
                'status' => 'PENDING',
                'approved_by_id' => null,
            ]
        );

        Approval::updateOrCreate(
            [
                'object_type' => VendorBill::class,
                'object_id' => $bill1->id,
            ],
            [
                'step' => 1,
                'approver_role' => 'R-OWN',
                'status' => 'PENDING',
                'reason' => 'Tagihan pelayaran Wan Hai melebihi limit Rp 10.000.000 (limitAP prototipe)',
            ]
        );

        // 2. Tagihan Trucking Inland Samudera Express (Rp 4.200.000 - Dibawah Limit -> APPROVED Siap Bayar)
        $adminUser = \App\Models\User::first();
        VendorBill::updateOrCreate(
            ['ref_no' => 'INV-TRK-88192'],
            [
                'vendor_type' => 'TRUCKING',
                'shipment_id' => $shipment?->id,
                'amount' => 4200000.00,
                'due_date' => now()->addDays(10),
                'status' => 'APPROVED',
                'approved_by_id' => $adminUser?->id,
            ]
        );

        // 3. Tagihan Terminal Petikemas TPS Priok (Rp 2.850.000 - Sudah Terbayar / PAID)
        $bill3 = VendorBill::updateOrCreate(
            ['ref_no' => 'INV-TPS-02918'],
            [
                'vendor_type' => 'TPS',
                'shipment_id' => $shipment?->id,
                'amount' => 2850000.00,
                'due_date' => now()->subDays(2),
                'status' => 'PAID',
                'approved_by_id' => $adminUser?->id,
            ]
        );

        if ($bankBca && $bill3->payments()->count() === 0) {
            VendorPayment::create([
                'bill_id' => $bill3->id,
                'account_id' => $bankBca->id,
                'amount' => 2850000.00,
                'paid_at' => now()->subDay(),
            ]);

            CashBankTransaction::create([
                'account_id' => $bankBca->id,
                'trx_date' => now()->subDay(),
                'amount' => -2850000.00,
                'description' => 'Pembayaran Terminal TPS Priok Ref: ' . $bill3->ref_no,
                'reconciled' => true,
                'ref_type' => 'VENDOR_BILL',
                'ref_id' => $bill3->id,
            ]);
        }

        // 4. Tagihan Pelayaran Evergreen (Rp 18.500.000 - PENDING Approval Pimpinan)
        $bill4 = VendorBill::updateOrCreate(
            ['ref_no' => 'INV-EMC-2026-3918'],
            [
                'vendor_type' => 'SHIPPING_LINE',
                'shipment_id' => $shipment?->id,
                'amount' => 18500000.00,
                'due_date' => now()->addDays(2),
                'status' => 'PENDING',
                'approved_by_id' => null,
            ]
        );

        Approval::updateOrCreate(
            [
                'object_type' => VendorBill::class,
                'object_id' => $bill4->id,
            ],
            [
                'step' => 1,
                'approver_role' => 'R-OWN',
                'status' => 'PENDING',
                'reason' => 'Tagihan pelayaran Evergreen melebihi limit Rp 10.000.000 (limitAP prototipe)',
            ]
        );
    }
}
