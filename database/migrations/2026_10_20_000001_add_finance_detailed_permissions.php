<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $newPerms = [
            [
                'code' => 'finance.invoice',
                'name' => 'Membuat & Mengelola Invoice Klien',
                'module' => 'Finance',
                'description' => 'Membuat draft invoice penagihan P19, mengubah komponen biaya, dan menerbitkan invoice resmi.',
            ],
            [
                'code' => 'finance.pay',
                'name' => 'Mencatat Pelunasan Tagihan Klien',
                'module' => 'Finance',
                'description' => 'Mencatat mutasi pelunasan invoice klien, bukti bayar bank, dan mengubah status menjadi PAID.',
            ],
            [
                'code' => 'finance.bill',
                'name' => 'Mengelola Tagihan Vendor Pelayaran & Ekspedisi',
                'module' => 'Finance',
                'description' => 'Mencatat tagihan vendor masuk (pelayaran, trucking, TPS, depo) dan menjadwalkan pembayaran.',
            ],
            [
                'code' => 'finance.ap_approve',
                'name' => 'Menyetujui Tagihan Hutang Vendor (AP Approval)',
                'module' => 'Finance',
                'description' => 'Hak otorisasi khusus menyetujui tagihan vendor di atas batas nominal limit AP sebelum dibayar.',
            ],
        ];

        foreach ($newPerms as $pData) {
            $perm = Permission::firstOrCreate(['code' => $pData['code']], $pData);

            // Berikan ke R-ADM & R-OWN
            $adminRole = Role::where('code', 'R-ADM')->first();
            $ownerRole = Role::where('code', 'R-OWN')->first();
            $finRole = Role::where('code', 'R-FIN')->first();

            if ($adminRole && !$adminRole->permissions->contains($perm->id)) {
                $adminRole->permissions()->attach($perm->id);
            }
            if ($ownerRole && !$ownerRole->permissions->contains($perm->id)) {
                $ownerRole->permissions()->attach($perm->id);
            }

            // Berikan izin invoice, pay, bill ke R-FIN (ap_approve khusus R-OWN & R-ADM)
            if ($finRole && in_array($pData['code'], ['finance.invoice', 'finance.pay', 'finance.bill'])) {
                if (!$finRole->permissions->contains($perm->id)) {
                    $finRole->permissions()->attach($perm->id);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $codes = ['finance.invoice', 'finance.pay', 'finance.bill', 'finance.ap_approve'];
        Permission::whereIn('code', $codes)->forceDelete();
    }
};
