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
     * Menambahkan permission granular untuk edit saat draft, edit pasca-CEISA, dan rollback tahap sebelumnya.
     */
    public function up(): void
    {
        $newPerms = [
            [
                'code' => 'document.edit',
                'name' => 'Mengedit Dokumen Pabean (Draft)',
                'module' => 'Core Bisnis - Document',
                'description' => 'Mengubah data draft pengajuan kepabeanan BC 2.0 / BC 3.0 saat masih dalam status DRAFT sebelum submit ke CEISA 4.0.',
            ],
            [
                'code' => 'document.edit_after_ceisa',
                'name' => 'Mengedit Dokumen Pabean Pasca-CEISA',
                'module' => 'Core Bisnis - Document',
                'description' => 'Hak otorisasi khusus (Supervisor / Manajer) untuk mengubah data, merevisi, dan memperbarui dokumen yang telah dikirim ke CEISA 4.0.',
            ],
            [
                'code' => 'workflow.rollback_stage',
                'name' => 'Membuka & Mengembalikan Pos Alur Sebelumnya',
                'module' => 'Core Bisnis - Document',
                'description' => 'Membuka kembali pos alur 11 tahap pabean sebelumnya untuk memfasilitasi perbaikan dan revisi berkas terdahulu.',
            ],
        ];

        $adminRole = Role::where('code', 'R-ADM')->first();
        $ownerRole = Role::where('code', 'R-OWN')->first();
        $docSpvRole = Role::where('code', 'R-DOC-SPV')->first();
        $docStaffRole = Role::where('code', 'R-DOC')->first();

        foreach ($newPerms as $pData) {
            $perm = Permission::updateOrCreate(['code' => $pData['code']], $pData);

            // Berikan ke Super Admin & Owner
            if ($adminRole && !$adminRole->permissions->contains($perm->id)) {
                $adminRole->permissions()->attach($perm->id);
            }
            if ($ownerRole && !$ownerRole->permissions->contains($perm->id)) {
                $ownerRole->permissions()->attach($perm->id);
            }

            // Supervisor Dokumen mendapatkan semua izin edit & rollback
            if ($docSpvRole && !$docSpvRole->permissions->contains($perm->id)) {
                $docSpvRole->permissions()->attach($perm->id);
            }

            // Staff Dokumen hanya mendapatkan izin edit DRAFT
            if ($docStaffRole && $pData['code'] === 'document.edit') {
                if (!$docStaffRole->permissions->contains($perm->id)) {
                    $docStaffRole->permissions()->attach($perm->id);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $codes = ['document.edit', 'document.edit_after_ceisa', 'workflow.rollback_stage'];
        Permission::whereIn('code', $codes)->delete();
    }
};
