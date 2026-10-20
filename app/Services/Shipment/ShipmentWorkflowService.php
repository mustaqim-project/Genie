<?php

namespace App\Services\Shipment;

use App\Models\CeisaDocument;
use App\Models\Client;
use App\Models\ClientInvoice;
use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Models\ShipmentItem;
use App\Models\ShippingVendor;
use App\Services\Ceisa\CeisaDocumentService;
use App\Services\Ceisa\DocumentBC20Service;
use App\Services\Ceisa\DocumentBC30Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ShipmentWorkflowService
{
    protected CeisaDocumentService $ceisaService;
    protected DocumentExtractionService $extractionService;

    public function __construct(CeisaDocumentService $ceisaService, DocumentExtractionService $extractionService)
    {
        $this->ceisaService = $ceisaService;
        $this->extractionService = $extractionService;
    }

    /**
     * Parsing Berkas Dokumen untuk Pratinjau Langsung (Live Preview AJAX)
     */
    public function parseDocumentsForPreview(array $files, ?string $clientId = null): array
    {
        return $this->extractionService->extractFromDocuments($files, $clientId);
    }

    /**
     * 1. Tim Document Create Project Shipment (Tradisional atau via Form)
     */
    public function createProject(array $data, int|string|null $userId = null): Shipment
    {
        $projectNo = Shipment::generateProjectNo();
        
        $rawEta = $data['eta_date'] ?? $data['eta'] ?? null;
        $eta = $rawEta ? Carbon::parse($rawEta) : now()->addDays(3);
        $freeTimeDays = (int) ($data['free_time_days'] ?? 7);
        $freeTimeExpiredAt = (clone $eta)->addDays($freeTimeDays);

        $valuta = $data['kode_valuta'] ?? $data['currency'] ?? 'USD';
        $ndbm = (float) ($data['ndbm_kurs'] ?? 16200);
        $nilaiValuta = (float) ($data['total_nilai_valuta'] ?? $data['total_cif'] ?? 25000);
        $nilaiIdr = $nilaiValuta * $ndbm;

        $rawType = $data['jenis_layanan'] ?? $data['type'] ?? 'IMPORT_BC20';
        $jenisLayanan = in_array($rawType, ['EXPORT', 'EKSPORT_BC30']) ? 'EKSPORT_BC30' : 'IMPORT_BC20';

        $shipment = Shipment::create([
            'project_no' => $projectNo,
            'client_id' => $data['client_id'],
            'jenis_layanan' => $jenisLayanan,
            'nomor_bl_awb' => $data['nomor_bl_awb'] ?? $data['bl_number'] ?? ('BL-' . time()),
            'tgl_bl_awb' => $data['tgl_bl_awb'] ?? now(),
            'shipping_vendor_id' => $data['shipping_vendor_id'] ?? null,
            'nama_kapal' => $data['nama_kapal'] ?? $data['vessel_name'] ?? 'MV WAN HAI 315',
            'voyage_no' => $data['voyage_no'] ?? $data['voyage_number'] ?? 'V.042N',
            'pelabuhan_muat' => $data['pelabuhan_muat'] ?? $data['port_of_loading'] ?? 'CNSHA (Shanghai)',
            'pelabuhan_bongkar' => $data['pelabuhan_bongkar'] ?? $data['port_of_discharge'] ?? 'IDTPP (Tanjung Priok)',
            'eta_date' => $eta,
            'free_time_days' => $freeTimeDays,
            'free_time_expired_at' => $freeTimeExpiredAt,
            'total_kontainer' => (int) ($data['total_kontainer'] ?? 1),
            'nomor_kontainer' => $data['nomor_kontainer'] ?? 'WHLU-9182341 (1x40HC)',
            'total_kemasan' => (int) ($data['total_kemasan'] ?? 150),
            'jenis_kemasan' => $data['jenis_kemasan'] ?? 'CTN (Cartons)',
            'total_bruto' => (float) ($data['total_bruto'] ?? 8500.00),
            'total_netto' => (float) ($data['total_netto'] ?? 7800.00),
            'kode_valuta' => $valuta,
            'total_nilai_valuta' => $nilaiValuta,
            'ndbm_kurs' => $ndbm,
            'total_nilai_idr' => $nilaiIdr,
            'status_tahap' => 'DOC_COLLECTING',
            'status_do' => 'PENDING_REQUEST',
            'catatan' => $data['catatan'] ?? 'Shipment baru dibuka oleh Tim Dokumen.',
            'created_by_user_id' => $userId,
        ]);

        // Inisialisasi 9 Slot Checklist Dokumen Berversi
        $slots = [
            'INVOICE' => 'Commercial Invoice Pemasok Luar Negeri',
            'PACKING_LIST' => 'Packing List Rincian Barang & Kemasan',
            'BL_AWB' => 'Bill of Lading (B/L) Asli / Sea Waybill',
            'COO_FORME' => 'Certificate of Origin (Form E / Form AK / SKA)',
            'INSURANCE' => 'Polis Sertifikat Asuransi Pengangkutan',
            'PERIJINAN_LARTAS' => 'Persetujuan Impor / LS Surveyor / BPOM (Jika Lartas)',
            'DO_ORIGINAL' => 'Delivery Order (DO) Pelayaran Rilis Resmi',
            'BUKTI_BAYAR_BILLING' => 'Bukti Penerimaan Negara (BPN) Billing MPN G3',
            'POD_SURAT_JALAN' => 'Bukti Pengantaran Gudang / Surat Jalan (POD)',
        ];

        foreach ($slots as $code => $label) {
            ShipmentDocument::create([
                'shipment_id' => $shipment->id,
                'jenis_dokumen' => $code,
                'nama_file' => null,
                'status' => 'MISSING',
                'catatan' => $label,
            ]);
        }

        // Otomatis tetapkan PIC 11 tahapan berdasarkan beban terendah (autoPic)
        $this->autoAssignPics($shipment);

        return $shipment;
    }

    /**
     * 1b. Tim Document Create Project Cepat Berbasis Upload Dokumen & Auto-Deteksi AI
     */
    public function createProjectFromUploadedDocuments(array $files, array $overrides = [], int|string|null $userId = null): Shipment
    {
        // 1. Jalankan ekstraksi dokumen
        $extracted = $this->extractionService->extractFromDocuments($files, $overrides['client_id'] ?? null);

        // 2. Gabungkan data ekstraksi dengan input form (overrides jika user mengoreksi)
        $mergedData = array_merge($extracted, array_filter($overrides, fn($val) => !is_null($val) && $val !== ''));

        // Pastikan client_id tersedia
        if (empty($mergedData['client_id'])) {
            $firstClient = Client::first();
            $mergedData['client_id'] = $firstClient?->id;
        }

        // 3. Buat Project Shipment
        $shipment = $this->createProject($mergedData, $userId);

        // 4. Hubungkan berkas fisik yang diupload ke 9 Slot Checklist Dokumen Berversi
        $fileMapping = [
            'bl_file' => 'BL_AWB',
            'invoice_file' => 'INVOICE',
            'packing_list_file' => 'PACKING_LIST',
            'coo_file' => 'COO_FORME',
            'insurance_file' => 'INSURANCE',
            'lartas_file' => 'PERIJINAN_LARTAS',
        ];

        foreach ($fileMapping as $inputKey => $docCode) {
            if (isset($files[$inputKey]) && $files[$inputKey]) {
                $this->uploadDocument($shipment, $docCode, $files[$inputKey], "Diunggah saat pembukaan proyek (Auto-Deteksi AI).");
            }
        }

        // Jika user mengunggah berkas multi / array generik `documents[]`
        if (isset($files['documents']) && is_array($files['documents'])) {
            foreach ($files['documents'] as $f) {
                if ($f instanceof \Illuminate\Http\UploadedFile) {
                    $name = strtoupper($f->getClientOriginalName());
                    $targetSlot = 'INVOICE';
                    if (str_contains($name, 'BL') || str_contains($name, 'LADING') || str_contains($name, 'AWB') || str_contains($name, 'WAYBILL')) {
                        $targetSlot = 'BL_AWB';
                    } elseif (str_contains($name, 'PACKING') || str_contains($name, 'PL') || str_contains($name, 'LIST')) {
                        $targetSlot = 'PACKING_LIST';
                    } elseif (str_contains($name, 'COO') || str_contains($name, 'FORM') || str_contains($name, 'SKA')) {
                        $targetSlot = 'COO_FORME';
                    } elseif (str_contains($name, 'INSUR') || str_contains($name, 'ASURANSI')) {
                        $targetSlot = 'INSURANCE';
                    }
                    $this->uploadDocument($shipment, $targetSlot, $f, "Diunggah saat batch upload proyek.");
                }
            }
        }

        // 5. Simpan Item Barang Hasil Deteksi AI
        if (!empty($extracted['items'])) {
            foreach ($extracted['items'] as $itemData) {
                $itemData['shipment_id'] = $shipment->id;
                ShipmentItem::create($itemData);
            }
            $shipment->update(['status_tahap' => 'HS_DETECTED']);
        }

        // Jika dokumen inti (BL, Invoice, Packing List) sudah terunggah, sesuaikan status
        $hasBL = $shipment->documents()->where('jenis_dokumen', 'BL_AWB')->where('status', '!=', 'MISSING')->exists();
        $hasInv = $shipment->documents()->where('jenis_dokumen', 'INVOICE')->where('status', '!=', 'MISSING')->exists();
        $hasPL = $shipment->documents()->where('jenis_dokumen', 'PACKING_LIST')->where('status', '!=', 'MISSING')->exists();

        if ($hasBL && $hasInv && $hasPL) {
            $shipment->update(['status_tahap' => 'DOC_READY']);
        }

        return $shipment;
    }

    /**
     * 1c. Sinkronisasi Ulang Data Proyek dari Dokumen Terunggah
     */
    public function reSyncShipmentFromDocuments(Shipment $shipment): array
    {
        $docs = $shipment->documents()->whereNotNull('nama_file')->get();
        $fileNames = $docs->pluck('nama_file')->toArray();

        $extracted = $this->extractionService->extractFromDocuments($fileNames, $shipment->client_id);

        $shipment->update([
            'nomor_bl_awb' => $extracted['bl_number'] ?? $shipment->nomor_bl_awb,
            'shipping_vendor_id' => $extracted['shipping_vendor_id'] ?? $shipment->shipping_vendor_id,
            'nama_kapal' => $extracted['vessel_name'] ?? $shipment->nama_kapal,
            'voyage_no' => $extracted['voyage_number'] ?? $shipment->voyage_no,
            'pelabuhan_muat' => $extracted['port_of_loading'] ?? $shipment->pelabuhan_muat,
            'pelabuhan_bongkar' => $extracted['port_of_discharge'] ?? $shipment->pelabuhan_bongkar,
            'total_kontainer' => $extracted['total_kontainer'] ?? $shipment->total_kontainer,
            'nomor_kontainer' => $extracted['nomor_kontainer'] ?? $shipment->nomor_kontainer,
            'total_kemasan' => $extracted['total_kemasan'] ?? $shipment->total_kemasan,
            'jenis_kemasan' => $extracted['jenis_kemasan'] ?? $shipment->jenis_kemasan,
            'total_bruto' => $extracted['total_bruto'] ?? $shipment->total_bruto,
            'total_netto' => $extracted['total_netto'] ?? $shipment->total_netto,
            'total_nilai_valuta' => $extracted['total_cif'] ?? $shipment->total_nilai_valuta,
            'status_tahap' => 'HS_DETECTED',
        ]);

        if (!empty($extracted['items']) && $shipment->items()->count() === 0) {
            foreach ($extracted['items'] as $itemData) {
                $itemData['shipment_id'] = $shipment->id;
                ShipmentItem::create($itemData);
            }
        }

        return $extracted;
    }

    /**
     * 2. Upload Berkas Dokumen ke Project
     */
    public function uploadDocument(Shipment $shipment, string $jenisDokumen, $file, ?string $catatan = null): ShipmentDocument
    {
        $doc = ShipmentDocument::firstOrNew([
            'shipment_id' => $shipment->id,
            'jenis_dokumen' => $jenisDokumen,
        ]);

        if (is_object($file) && method_exists($file, 'getClientOriginalName')) {
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('shipment_docs/' . $shipment->id, $filename, 'public');
            $doc->nama_file = $file->getClientOriginalName();
            $doc->file_path = $path;
            $doc->file_size_kb = round($file->getSize() / 1024);
        } else {
            // Simulasi nama file jika diupload via form dummy / string
            $doc->nama_file = strtolower($jenisDokumen) . '_' . $shipment->project_no . '.pdf';
            $doc->file_path = 'simulated/' . $doc->nama_file;
            $doc->file_size_kb = 480;
        }

        $doc->status = 'UPLOADED';
        $doc->catatan = $catatan ?? "Berkas {$jenisDokumen} berhasil dilampirkan.";
        $doc->save();

        return $doc;
    }

    /**
     * 3. Verifikasi Kelengkapan Dokumen oleh Supervisor Dokumen
     */
    public function verifyDocument(ShipmentDocument|string $document, int|string $verifierUserId, string $status = 'VERIFIED', ?string $catatan = null): void
    {
        if (is_string($document)) {
            $document = ShipmentDocument::findOrFail($document);
        }

        $document->update([
            'status' => $status,
            'verified_by_user_id' => $verifierUserId,
            'verified_at' => now(),
            'catatan' => $catatan ?? $document->catatan,
        ]);

        $shipment = $document->shipment;

        // Jika 3 dokumen utama (Invoice, PL, BL) sudah VERIFIED, update status ke DOC_READY
        if ($shipment->isDocumentComplete() && $shipment->status_tahap === 'DOC_COLLECTING') {
            $shipment->update([
                'status_tahap' => 'DOC_READY',
            ]);
        }
    }

    /**
     * 3b. Verifikasi Cepat Seluruh Dokumen Terunggah oleh Supervisor (Bulk Verify)
     */
    public function verifyAllDocuments(Shipment $shipment, int|string $verifierUserId): int
    {
        $docs = $shipment->documents()->whereIn('status', ['UPLOADED', 'MISSING'])->get();
        $verifiedCount = 0;

        foreach ($docs as $doc) {
            $updateData = [
                'status' => 'VERIFIED',
                'verified_by_user_id' => $verifierUserId,
                'verified_at' => now(),
            ];

            // Jika belum ada file, buatkan placeholder
            if (!$doc->nama_file) {
                $doc->nama_file = strtolower($doc->jenis_dokumen) . '_' . $shipment->project_no . '_verified.pdf';
                $doc->file_path = 'verified_docs/' . $doc->nama_file;
                $doc->file_size_kb = 350;
            }

            $doc->update($updateData);
            $verifiedCount++;
        }

        if ($shipment->status_tahap === 'DOC_COLLECTING') {
            $shipment->update(['status_tahap' => 'DOC_READY']);
        }

        return $verifiedCount;
    }

    /**
     * 4. Deteksi Otomatis HS Code dari Berkas Packing List (AI/BTKI Extraction Engine & Lartas Sync)
     */
    public function detectHsCodeFromPackingList(Shipment $shipment, ?string $rawText = null, ?\Illuminate\Http\UploadedFile $uploadedFile = null): array
    {
        $extractedItems = [];
        $textToProcess = $rawText;

        // 1. Jika ada berkas baru diupload
        if ($uploadedFile) {
            $textToProcess = $this->extractionService->extractTextFromFile($uploadedFile);
        }

        // 2. Jika tidak ada input teks/file langsung, periksa apakah ada dokumen PACKING_LIST tersimpan di storage
        if (empty($textToProcess)) {
            $plDoc = $shipment->documents()->where('jenis_dokumen', 'PACKING_LIST')->first();
            if ($plDoc && $plDoc->file_path) {
                $fullPath = storage_path('app/public/' . $plDoc->file_path);
                if (file_exists($fullPath)) {
                    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                    if (in_array($ext, ['txt', 'csv', 'tsv', 'json'])) {
                        $textToProcess = (string) file_get_contents($fullPath);
                    }
                }
            }
        }

        // 3. Jalankan parsing cerdas konten packing list jika teks tersedia
        if (!empty($textToProcess)) {
            $parsed = $this->extractionService->parsePackingListContent($textToProcess, $shipment->jenis_layanan);
            $extractedItems = $parsed['items'] ?? [];

            // Sinkronkan total jika terdeteksi dari dokumen
            if (!empty($parsed['total_kemasan']) && $parsed['total_kemasan'] > 1) {
                $shipment->update([
                    'total_kemasan' => $parsed['total_kemasan'],
                    'jenis_kemasan' => $parsed['jenis_kemasan'] ?? $shipment->jenis_kemasan,
                    'total_bruto' => $parsed['total_bruto'] ?? $shipment->total_bruto,
                    'total_netto' => $parsed['total_netto'] ?? $shipment->total_netto,
                ]);
            }
        }

        // 4. Fallback jika tidak ada teks/item yang dapat diekstrak
        if (empty($extractedItems)) {
            $packages = [
                'total_kemasan' => $shipment->total_kemasan ?? 150,
                'jenis_kemasan' => $shipment->jenis_kemasan ?? 'CTN',
                'total_bruto' => $shipment->total_bruto ?? 8500.0,
                'total_netto' => $shipment->total_netto ?? 7800.0,
            ];
            $commercial = [
                'currency' => $shipment->kode_valuta ?? 'USD',
                'value' => $shipment->total_nilai_valuta ?? 25000.0,
                'kurs' => $shipment->ndbm_kurs ?? 16200.0,
            ];
            $extractedItems = $this->extractionService->detectCargoItems($shipment->nomor_bl_awb . ' ' . $shipment->nama_kapal, $shipment->jenis_layanan, $packages, $commercial);
        }

        // Hapus item lama sebelum mengisi hasil deteksi baru
        $shipment->items()->delete();

        foreach ($extractedItems as $itemData) {
            $itemData['shipment_id'] = $shipment->id;
            ShipmentItem::create($itemData);
        }

        $shipment->update([
            'status_tahap' => 'HS_DETECTED',
        ]);

        // 5. Otomatis sinkronkan dan buat slot perizinan dokumen tambahan (Lartas)
        app(\App\Services\Ceisa\CeisaHsCodeService::class)->verifyPackingList($shipment);

        return $extractedItems;
    }

    /**
     * 4b. Tambah Item Barang Kargo Baru Manual & Sinkronkan Lartas
     */
    public function addCargoItem(Shipment $shipment, array $data): ShipmentItem
    {
        $maxUrut = (int) $shipment->items()->max('nomor_urut');
        $item = ShipmentItem::create([
            'shipment_id' => $shipment->id,
            'nomor_urut' => $maxUrut + 1,
            'uraian_barang' => $data['uraian_barang'] ?? $data['item_name'] ?? 'ITEM CARGO',
            'kode_hs' => $data['kode_hs'] ?? $data['hs_code'] ?? '8452.29.00',
            'hs_confidence_score' => 1.0,
            'hs_legal_basis' => $data['hs_legal_basis'] ?? $data['description'] ?? 'Input Manual Pengguna',
            'negara_asal' => $data['negara_asal'] ?? $data['country_of_origin'] ?? 'CN',
            'jumlah_kemasan' => (int) ($data['jumlah_kemasan'] ?? $data['quantity'] ?? 1),
            'jenis_kemasan' => $data['jenis_kemasan'] ?? $data['package_unit'] ?? 'CTN',
            'berat_netto' => (float) ($data['berat_netto'] ?? $data['net_weight'] ?? 100),
            'berat_bruto' => (float) ($data['berat_bruto'] ?? $data['gross_weight'] ?? 120),
            'nilai_fob' => (float) ($data['nilai_fob'] ?? $data['cif_amount'] ?? 1000),
            'tarif_bm_persen' => (float) ($data['tarif_bm_persen'] ?? 5.0),
            'tarif_ppn_persen' => (float) ($data['tarif_ppn_persen'] ?? 11.0),
            'tarif_pph_persen' => (float) ($data['tarif_pph_persen'] ?? 2.5),
        ]);

        // Sinkronkan dokumen lartas untuk item baru
        app(\App\Services\Ceisa\CeisaHsCodeService::class)->verifyPackingList($shipment);

        return $item;
    }

    /**
     * 4c. Update Item Barang Kargo & Sinkronkan Ulang Lartas
     */
    public function updateCargoItem(ShipmentItem $item, array $data): ShipmentItem
    {
        $oldHs = $item->kode_hs;
        $newHs = $data['kode_hs'] ?? $data['hs_code'] ?? $item->kode_hs;

        $item->update([
            'uraian_barang' => $data['uraian_barang'] ?? $data['item_name'] ?? $item->uraian_barang,
            'kode_hs' => $newHs,
            'hs_legal_basis' => $data['hs_legal_basis'] ?? $data['description'] ?? $item->hs_legal_basis,
            'negara_asal' => $data['negara_asal'] ?? $data['country_of_origin'] ?? $item->negara_asal,
            'jumlah_kemasan' => isset($data['jumlah_kemasan']) ? (int) $data['jumlah_kemasan'] : (isset($data['quantity']) ? (int) $data['quantity'] : $item->jumlah_kemasan),
            'jenis_kemasan' => $data['jenis_kemasan'] ?? $data['package_unit'] ?? $item->jenis_kemasan,
            'berat_netto' => isset($data['berat_netto']) ? (float) $data['berat_netto'] : (isset($data['net_weight']) ? (float) $data['net_weight'] : $item->berat_netto),
            'berat_bruto' => isset($data['berat_bruto']) ? (float) $data['berat_bruto'] : (isset($data['gross_weight']) ? (float) $data['gross_weight'] : $item->berat_bruto),
            'nilai_fob' => isset($data['nilai_fob']) ? (float) $data['nilai_fob'] : (isset($data['cif_amount']) ? (float) $data['cif_amount'] : $item->nilai_fob),
            'tarif_bm_persen' => isset($data['tarif_bm_persen']) ? (float) $data['tarif_bm_persen'] : $item->tarif_bm_persen,
            'tarif_ppn_persen' => isset($data['tarif_ppn_persen']) ? (float) $data['tarif_ppn_persen'] : $item->tarif_ppn_persen,
            'tarif_pph_persen' => isset($data['tarif_pph_persen']) ? (float) $data['tarif_pph_persen'] : $item->tarif_pph_persen,
        ]);

        $shipment = $item->shipment;

        // Jika HS code berubah, hapus dokumen perizinan lama yang masih status MISSING
        if ($oldHs !== $newHs) {
            \App\Models\ShipmentItemDocument::where('shipment_item_id', $item->id)
                ->where('status', 'MISSING')
                ->delete();
        }

        // Re-sinkronkan kebutuhan dokumen lartas di database
        if ($shipment) {
            app(\App\Services\Ceisa\CeisaHsCodeService::class)->verifyPackingList($shipment);
        }

        return $item;
    }

    /**
     * 4d. Hapus Item Barang Kargo & Bersihkan Relasi Dokumen Lartas
     */
    public function deleteCargoItem(ShipmentItem $item): void
    {
        $shipment = $item->shipment;

        // Bersihkan relasi dokumen perizinan item ini
        \App\Models\ShipmentItemDocument::where('shipment_item_id', $item->id)->delete();
        $item->delete();

        if ($shipment) {
            app(\App\Services\Ceisa\CeisaHsCodeService::class)->verifyPackingList($shipment);
        }
    }

    /**
     * 5. Generate Dokumen Pabean Resmi (BC 2.0 PIB / BC 3.0 PEB) Otomatis Berbasis Data Packing List & HS Code
     */
    public function generateCeisaDocFromShipment(Shipment $shipment): CeisaDocument
    {
        return app(\App\Services\Ceisa\CeisaHsCodeService::class)->generatePibOrPebFromPackingList($shipment);
    }

    /**
     * 6. Submit ke CEISA 4.0 hingga Kode Billing Terbit (Tim Dokumen Selesai -> Diteruskan ke Tim DO)
     */
    public function submitCeisaAndAdvanceToDo(Shipment $shipment): CeisaDocument
    {
        $ceisaDoc = $shipment->ceisaDocument;
        if (!$ceisaDoc) {
            $ceisaDoc = $this->generateCeisaDocFromShipment($shipment);
        }

        // 1. Submit ke Gateway CEISA
        $this->ceisaService->submit($ceisaDoc);

        // 2. Poll Status & Ambil Kode Billing MPN G3 Resmi
        $this->ceisaService->checkStatus($ceisaDoc);
        $this->ceisaService->fetchResponse($ceisaDoc);
        $ceisaDoc->refresh();

        // 3. Update Status Shipment -> Proses Tim Dokumen Selesai, Diteruskan ke Tim DO
        $shipment->update([
            'status_tahap' => 'BILLING_ISSUED',
        ]);

        if ($ceisaDoc->kode_billing) {
            $billingDoc = $shipment->documents()->where('jenis_dokumen', 'BUKTI_BAYAR_BILLING')->first();
            if ($billingDoc) {
                $billingDoc->update([
                    'status' => 'UPLOADED',
                    'nama_file' => 'BILLING_MPNG3_' . $ceisaDoc->kode_billing . '.pdf',
                    'catatan' => 'Kode Billing MPN G3: ' . $ceisaDoc->kode_billing . ' terbit dari CEISA 4.0.',
                ]);
            }
        }

        return $ceisaDoc;
    }

    /**
     * 7. TIM DO: Kirim Permintaan Invoice Pelayaran Otomatis via Email
     */
    public function sendVendorEmailForDo(Shipment $shipment, ?string $customNotes = null): array
    {
        $vendor = $shipment->vendor;
        $recipient = $vendor ? $vendor->pic_email : 'import.documentation@maersk.com';
        $vendorName = $vendor ? $vendor->name : 'MAERSK LINE INDONESIA';

        // Format Konten Email Permintaan Invoice Pelayaran
        $subject = "URGENT: Permintaan Invoice THC & DO Release - BL No: {$shipment->nomor_bl_awb} - {$shipment->client->name}";
        $body = "Kepada Yth. Tim Finance & Rilis DO {$vendorName},\n\n"
              . "Dengan hormat,\n"
              . "Kami dari PT Agung Trans Solusindo (PPJK Resmi) memohon rincian invoice tagihan pelayaran (Ocean Freight, THC, Cleaning, dan DO Fee) untuk pengapalan berikut:\n\n"
              . "- No. B/L : {$shipment->nomor_bl_awb}\n"
              . "- Nama Kapal / Voyage : {$shipment->nama_kapal} / {$shipment->voyage_no}\n"
              . "- Consignee : {$shipment->client->name}\n"
              . "- No. Kontainer : {$shipment->nomor_kontainer}\n"
              . "- Estimasi Tiba (ETA) : " . ($shipment->eta_date ? $shipment->eta_date->format('d/m/Y H:i') : '-') . "\n\n"
              . "Mohon dapat mengirimkan invoice beserta nomor rekening resmi pelayaran agar kami dapat segera melakukan pelunasan sebelum masa Free Time berakhir.\n\n"
              . ($customNotes ? "Catatan Tambahan: {$customNotes}\n\n" : "")
              . "Terima kasih atas kerja samanya.\n\n"
              . "Salam,\n"
              . "Tim Delivery Order (DO) - PT Agung Trans Solusindo\n"
              . "Hotline Operasional: 021-43900012";

        $shipment->update([
            'status_do' => 'EMAIL_SENT',
            'do_email_sent_at' => now(),
            'do_email_recipient' => $recipient,
            'status_tahap' => 'DO_PROCESSING',
        ]);

        return [
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'sent_at' => now()->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * 8. TIM DO: Update Rilis DO Pelayaran & Batas Waktu Expired DO
     */
    public function releaseDo(Shipment $shipment, string $nomorDo, string $doExpiredAt): void
    {
        $shipment->update([
            'nomor_do' => $nomorDo,
            'do_expired_at' => Carbon::parse($doExpiredAt),
            'status_do' => 'DO_RELEASED',
            'status_tahap' => 'DO_RELEASED', // Diteruskan ke Tim Traffic
        ]);

        // Tandai dokumen DO_ORIGINAL sebagai UPLOADED & VERIFIED
        $doc = $shipment->documents()->where('jenis_dokumen', 'DO_ORIGINAL')->first();
        if ($doc) {
            $doc->update([
                'status' => 'VERIFIED',
                'nama_file' => 'DO_' . $nomorDo . '.pdf',
                'verified_at' => now(),
            ]);
        }
    }

    /**
     * 9. TIM TRAFFIC: Eksekusi Gate Out, Behandle & POD dengan Smart Skip Logic
     */
    public function advanceTraffic(Shipment $shipment, string $actionType, ?array $extra = null): void
    {
        $updateData = [];

        if (isset($extra['customs_channel'])) {
            $updateData['customs_channel'] = strtoupper($extra['customs_channel']);
        }

        if (isset($extra['trucking_vendor_reason'])) {
            $updateData['trucking_vendor_reason'] = $extra['trucking_vendor_reason'];
        }

        if (isset($extra['traffic_notes'])) {
            $updateData['catatan'] = $extra['traffic_notes'];
        }

        if (in_array($actionType, ['SPPB_UPDATE', 'SPPB_RELEASED'])) {
            $updateData['status_tahap'] = 'TRAFFIC_SPPB';
        } elseif ($actionType === 'INSPECTION_CUSTOMS' || $actionType === 'HOLD') {
            $updateData['status_tahap'] = 'TRAFFIC_HOLD';
            // Set 4 checklist langkah pemeriksaan awal
            $updateData['customs_hold_steps'] = [
                ['name' => 'Kunjungan pemeriksa Bea Cukai', 'is_completed' => false],
                ['name' => 'Dokumen tambahan / perizinan dipenuhi', 'is_completed' => false],
                ['name' => 'Bongkar & pemeriksaan fisik kontainer', 'is_completed' => false],
                ['name' => 'Pengembalian segel pelayaran resmi', 'is_completed' => false],
            ];
        } elseif ($actionType === 'GATE_OUT') {
            $updateData['status_tahap'] = 'TRAFFIC_GATE_OUT';
        } elseif (in_array($actionType, ['DELIVERED', 'DELIVERED_POD'])) {
            $updateData['status_tahap'] = 'DELIVERED_POD';
            $updateData['pod_signed_at'] = now();
            $updateData['closed_at'] = now(); // Kebijakan Prototipe: Closing operasional tidak menunggu invoice lunas

            // Tandai dokumen POD sebagai VERIFIED
            $doc = $shipment->documents()->where('jenis_dokumen', 'POD_SURAT_JALAN')->first();
            if ($doc) {
                $doc->update([
                    'status' => 'VERIFIED',
                    'nama_file' => 'POD_' . $shipment->project_no . '.pdf',
                    'file_path' => $extra['pod_file_path'] ?? $doc->file_path,
                    'verified_at' => now(),
                    'catatan' => 'POD fisik diterima dari ' . ($extra['receiver_name'] ?? 'Penerima Klien'),
                ]);
            }
        }

        $shipment->update($updateData);
    }

    /**
     * 10. Generate Invoice Klien P19 (Finance)
     */
    public function generateClientInvoice(Shipment $shipment, int|string|null $userId = null): ClientInvoice
    {
        $ceisaDoc = $shipment->ceisaDocument;
        $talanganPabean = $ceisaDoc ? ($ceisaDoc->total_billing ?? 18500000) : 18500000;
        $biayaPelayaran = 3850000; // THC + DO fee
        $jasaPpjk = 1500000;       // Jasa Pengurusan PPJK
        $trucking = 2500000;       // Pengiriman Jakarta

        $subtotal = $talanganPabean + $biayaPelayaran + $jasaPpjk + $trucking;
        
        // Sesuai PMK No. 71/PMK.03/2022: PPN Jasa Pengurusan Transportasi & PPJK dengan DPP Nilai Lain (efektif 1.1% atas Jasa)
        $dppJasa = $jasaPpjk + $trucking;
        $ppnJasa = round($dppJasa * 0.011, 2);
        $total = $subtotal + $ppnJasa;

        $invoice = ClientInvoice::create([
            'invoice_no' => ClientInvoice::generateInvoiceNo(),
            'shipment_id' => $shipment->id,
            'client_id' => $shipment->client_id,
            'tanggal' => now(),
            'jatuh_tempo' => now()->addDays(14),
            'talangan_pabean_idr' => $talanganPabean,
            'biaya_pelayaran_idr' => $biayaPelayaran,
            'jasa_ppjk_idr' => $jasaPpjk,
            'biaya_trucking_idr' => $trucking,
            'subtotal_idr' => $subtotal,
            'ppn_jasa_idr' => $ppnJasa,
            'total_tagihan_idr' => $total,
            'status' => 'UNPAID',
            'created_by_user_id' => $userId,
        ]);

        $shipment->update([
            'status_tahap' => 'INVOICED_P19',
        ]);

        // Auto-Journaling Akuntansi Forwarding (DR Piutang AR, CR Pendapatan, CR PPN 1.1%, CR Talangan)
        try {
            $accountingService = app(\App\Services\Accounting\AccountingService::class);
            $accountingService->recordClientInvoiceIssued($invoice, $userId);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal auto-journal invoice: " . $e->getMessage());
        }

        return $invoice;
    }

    /**
     * 11. Penugasan Otomatis PIC Beban Terendah (Lowest Workload Balancing - autoPic)
     */
    public function autoAssignPics(Shipment $shipment): array
    {
        $assignments = [];
        $stages = Shipment::STAGES_PIPELINE;

        foreach ($stages as $stg) {
            $roleCode = $stg['role'];
            $role = \App\Models\Role::where('code', $roleCode)->first();
            if (!$role) {
                continue;
            }

            // Ambil kandidat user aktif
            $candidates = $role->users()->where('is_active', true)->get();
            if ($candidates->isEmpty()) {
                continue;
            }

            // Urutkan berdasarkan beban kerja aktif terendah
            $sorted = $candidates->sortBy(function ($usr) {
                return \App\Models\ShipmentPic::where('user_id', $usr->id)
                    ->whereHas('shipment', function ($sq) {
                        $sq->whereNull('closed_at');
                    })
                    ->count();
            })->values();

            $primaryPic = $sorted->first();
            $backupPic = $sorted->get(1);

            \App\Models\ShipmentPic::updateOrCreate(
                [
                    'shipment_id' => $shipment->id,
                    'process_code' => $stg['key'],
                ],
                [
                    'user_id' => $primaryPic->id,
                    'backup_id' => $backupPic?->id,
                ]
            );

            $assignments[$stg['key']] = [
                'primary' => $primaryPic->name,
                'backup' => $backupPic?->name,
            ];
        }

        return $assignments;
    }

    /**
     * 12. Ambil Alih PIC Tahap Tertentu (Take Over PIC)
     */
    public function takeOverPic(Shipment $shipment, string $processCode, int|string $newUserId): \App\Models\ShipmentPic
    {
        $pic = \App\Models\ShipmentPic::firstOrNew([
            'shipment_id' => $shipment->id,
            'process_code' => $processCode,
        ]);

        $oldUserId = $pic->user_id;
        $pic->user_id = $newUserId;
        if ($oldUserId && $oldUserId !== $newUserId) {
            $pic->backup_id = $oldUserId;
        }
        $pic->save();

        return $pic;
    }
}
