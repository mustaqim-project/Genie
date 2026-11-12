<?php

namespace Database\Seeders;

use App\Models\StagePrerequisite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StagePrerequisiteSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            [
                'stage_number' => 1,
                'stage_code' => 'DOC_COLLECTING',
                'stage_name' => 'Pos 1: Pengumpulan & Verifikasi Berkas',
                'required_fields' => ['client_id', 'nomor_bl_awb', 'eta_date'],
                'required_documents' => [],
                'is_approval_required' => false,
                'approver_role' => null,
            ],
            [
                'stage_number' => 2,
                'stage_code' => 'HS_DETECTED',
                'stage_name' => 'Pos 2: Deteksi HS Code & Lartas',
                'required_fields' => ['client_id', 'nomor_bl_awb'],
                'required_documents' => ['INVOICE', 'PACKING_LIST', 'BL_AWB'],
                'is_approval_required' => false,
                'approver_role' => null,
            ],
            [
                'stage_number' => 3,
                'stage_code' => 'DRAFT_REVIEW',
                'stage_name' => 'Pos 3: Review Dokumen Pabean (PIB/PEB)',
                'required_fields' => ['client_id', 'nomor_bl_awb', 'total_cif'],
                'required_documents' => ['INVOICE', 'PACKING_LIST', 'BL_AWB'],
                'is_approval_required' => true,
                'approver_role' => 'R-SPV',
            ],
            [
                'stage_number' => 4,
                'stage_code' => 'BILLING_ISSUED',
                'stage_name' => 'Pos 4: Billing Kepabeanan & PNBP',
                'required_fields' => ['nomor_bl_awb'],
                'required_documents' => [],
                'is_approval_required' => true,
                'approver_role' => 'R-SPV',
            ],
            [
                'stage_number' => 5,
                'stage_code' => 'DO_PROCESSING',
                'stage_name' => 'Pos 5: Pelayaran & Delivery Order (DO)',
                'required_fields' => ['shipping_vendor_id', 'nama_kapal', 'voyage_no', 'nomor_kontainer'],
                'required_documents' => ['BL_AWB'],
                'is_approval_required' => false,
                'approver_role' => null,
            ],
            [
                'stage_number' => 6,
                'stage_code' => 'ARRIVED',
                'stage_name' => 'Pos 6: Kedatangan Kapal & Penentuan Jalur',
                'required_fields' => ['nomor_do'],
                'required_documents' => ['DO_ORIGINAL'],
                'is_approval_required' => false,
                'approver_role' => null,
            ],
            [
                'stage_number' => 7,
                'stage_code' => 'TRAFFIC_HOLD',
                'stage_name' => 'Pos 7: Hold, Notul & Pemeriksaan Lapangan',
                'required_fields' => ['customs_channel'],
                'required_documents' => [],
                'is_approval_required' => false,
                'approver_role' => null,
            ],
            [
                'stage_number' => 8,
                'stage_code' => 'SPPB_RELEASED',
                'stage_name' => 'Pos 8: SPPB / NPE & Pengeluaran Gate Out',
                'required_fields' => ['nomor_do'],
                'required_documents' => ['DO_ORIGINAL'],
                'is_approval_required' => false,
                'approver_role' => null,
            ],
            [
                'stage_number' => 9,
                'stage_code' => 'DELIVERY',
                'stage_name' => 'Pos 9: Pengiriman Truk ke Gudang Tujuan',
                'required_fields' => ['nomor_kontainer'],
                'required_documents' => [],
                'is_approval_required' => false,
                'approver_role' => null,
            ],
            [
                'stage_number' => 10,
                'stage_code' => 'COMPLETED',
                'stage_name' => 'Pos 10: Tanda Terima POD & Closing Proyek',
                'required_fields' => [],
                'required_documents' => ['POD_SURAT_JALAN'],
                'is_approval_required' => false,
                'approver_role' => null,
            ],
            [
                'stage_number' => 11,
                'stage_code' => 'INVOICE_ISSUED',
                'stage_name' => 'Pos 11: Tagihan Invoice Klien & Pelunasan',
                'required_fields' => [],
                'required_documents' => ['POD_SURAT_JALAN'],
                'is_approval_required' => true,
                'approver_role' => 'R-OWN',
            ],
        ];

        foreach ($stages as $stage) {
            StagePrerequisite::updateOrCreate(
                ['stage_code' => $stage['stage_code']],
                array_merge($stage, ['id' => (string) Str::uuid()])
            );
        }
    }
}
