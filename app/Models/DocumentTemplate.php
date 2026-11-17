<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'document_templates';

    protected $fillable = [
        'code',
        'name',
        'category',
        'division',
        'subject',
        'body',
        'terms_and_conditions',
        'header_notes',
        'footer_notes',
        'signee_name',
        'emergency_phone',
        'variables',
        'is_default',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_default' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Kategori Template yang Didukung
     */
    public const CATEGORIES = [
        'EMAIL' => 'Email Korespondensi Divisi',
        'SURAT_JALAN' => 'Surat Jalan Pengeluaran Kargo',
        'TERMS_KLAUSUL' => 'Klausul & Syarat Ketentuan Hukum',
        'WHATSAPP' => 'Pesan Cepat WhatsApp / SMS',
    ];

    /**
     * Divisi yang Didukung
     */
    public const DIVISIONS = [
        'marketing' => 'Marketing & Sales',
        'finance' => 'Finance & Penagihan',
        'document' => 'Dokumen Kepabeanan',
        'traffic' => 'Traffic & Armada Lapangan',
        'general' => 'Operasional Umum',
    ];

    /**
     * Dapatkan Template Aktif Berdasarkan Kode atau Kategori + Divisi
     */
    public static function getTemplate(string $codeOrCategory, ?string $division = null): ?self
    {
        // 1. Coba cari berdasarkan code eksak
        $tpl = self::where('code', $codeOrCategory)->where('is_active', true)->first();
        if ($tpl) {
            return $tpl;
        }

        // 2. Coba cari berdasarkan kategori + divisi default
        $query = self::where('category', $codeOrCategory)->where('is_active', true);
        if ($division) {
            $query->where('division', $division);
        }

        return $query->orderByDesc('is_default')->first();
    }

    /**
     * Render Template dengan menggantikan tag placeholder {variabel}
     */
    public function render(array $data = []): array
    {
        $renderedSubject = $this->subject;
        $renderedBody = $this->body;
        $renderedTerms = $this->terms_and_conditions;
        $renderedHeader = $this->header_notes;
        $renderedFooter = $this->footer_notes;

        foreach ($data as $key => $value) {
            $tag = '{' . $key . '}';
            $val = is_scalar($value) ? (string) $value : '';

            if ($renderedSubject) $renderedSubject = str_replace($tag, $val, $renderedSubject);
            if ($renderedBody) $renderedBody = str_replace($tag, $val, $renderedBody);
            if ($renderedTerms) $renderedTerms = str_replace($tag, $val, $renderedTerms);
            if ($renderedHeader) $renderedHeader = str_replace($tag, $val, $renderedHeader);
            if ($renderedFooter) $renderedFooter = str_replace($tag, $val, $renderedFooter);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'division' => $this->division,
            'subject' => $renderedSubject,
            'body' => $renderedBody,
            'terms_and_conditions' => $renderedTerms,
            'header_notes' => $renderedHeader,
            'footer_notes' => $renderedFooter,
            'signee_name' => $this->signee_name,
            'emergency_phone' => $this->emergency_phone,
        ];
    }

    /**
     * Seed Template Default Sistem (MoM Compliance)
     */
    public static function seedDefaults(): void
    {
        $defaults = [
            [
                'code' => 'EMAIL_MARKETING_DEFAULT',
                'name' => 'Email Penawaran Harga (Marketing & Sales)',
                'category' => 'EMAIL',
                'division' => 'marketing',
                'subject' => 'Penawaran Harga Resmi Ekspor Impor - PT GENIE Logistik Indonesia - {quotation_no}',
                'body' => "Yth. Bapak/Ibu {contact_person} / Pimpinan {client_name},\n\nTerima kasih atas kepercayaan Anda kepada PT GENIE Logistik Indonesia sebagai mitra logistik dan kepabeanan Anda.\n\nBerikut kami sampaikan Surat Penawaran Harga resmi dengan rincian sebagai berikut:\n• No. Penawaran: {quotation_no}\n• Rute Pengiriman: {origin_port} ➔ {destination_port}\n• Estimasi Total: {total_amount}\n• Masa Berlaku: s/d {valid_until}\n\n{custom_notes}\n\nDokumen resmi penawaran harga telah kami lampirkan dalam format PDF pada email ini. Mohon konfirmasi kesepakatan harga agar proses operasional dapat segera kami jadwalkan.",
                'footer_notes' => 'PT GENIE LOGISTIK INDONESIA - Divisi Marketing & Layanan Klien',
                'variables' => ['client_name', 'contact_person', 'quotation_no', 'origin_port', 'destination_port', 'total_amount', 'valid_until', 'custom_notes'],
                'is_default' => true,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'code' => 'EMAIL_FINANCE_DEFAULT',
                'name' => 'Email Tagihan Invoice P19 (Finance & Billing)',
                'category' => 'EMAIL',
                'division' => 'finance',
                'subject' => 'Pemberitahuan Tagihan Resmi (Invoice P19) - PT GENIE Logistik Indonesia - {invoice_no}',
                'body' => "Yth. Tim Finance & Akuntansi {client_name},\n\nSemoga Bapak/Ibu senantiasa dalam keadaan sehat dan sukses.\n\nBersama email ini, kami sampaikan lembar tagihan resmi (Invoice P19) atas jasa Freight Forwarding & Pengurusan Dokumen Kepabeanan:\n• No. Invoice: {invoice_no}\n• No. Proyek / B/L: {project_no} / {bl_number}\n• Total Tagihan: {total_amount}\n• Batas Waktu Pembayaran: {due_date}\n\n{custom_notes}\n\n{bank_info}\n\nDokumen faktur tagihan resmi telah kami lampirkan dalam bentuk file PDF.",
                'footer_notes' => 'PT GENIE LOGISTIK INDONESIA - Divisi Finance, Billing & Akuntansi',
                'variables' => ['client_name', 'invoice_no', 'project_no', 'bl_number', 'total_amount', 'due_date', 'custom_notes', 'bank_info'],
                'is_default' => true,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'code' => 'EMAIL_DOCUMENT_DEFAULT',
                'name' => 'Email Review Draft PIB/PEB (Dokumen Kepabeanan)',
                'category' => 'EMAIL',
                'division' => 'document',
                'subject' => 'Konfirmasi & Approval Draft Dokumen Pabean (PIB/PEB) - Aju: {nomor_aju}',
                'body' => "Yth. Tim Ekspor Impor {client_name},\n\nDraft Pemberitahuan Pabean ({document_type}) untuk pengajuan kargo Anda telah selesai kami susun:\n• No. Pengajuan (Aju): {nomor_aju}\n• Jenis Dokumen: {document_type}\n• Sarana Pengangkut: {vessel_name} (Voy: {voyage_no})\n• Pelabuhan: {pol} ➔ {pod}\n\n{custom_notes}\n\nMohon periksa kesesuaian uraian barang, pos tarif HS Code, dan nilai pabean (CIF/FOB). Balas email ini dengan persetujuan (APPROVAL) agar dokumen segera kami transfer via EDI/CEISA 4.0.",
                'footer_notes' => 'PT GENIE LOGISTIK INDONESIA - Divisi Dokumen & Kepabeanan PPJK',
                'variables' => ['client_name', 'document_type', 'nomor_aju', 'vessel_name', 'voyage_no', 'pol', 'pod', 'custom_notes'],
                'is_default' => true,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'code' => 'EMAIL_TRAFFIC_DEFAULT',
                'name' => 'Email Update Posisi Kargo & ETA (Traffic & Lapangan)',
                'category' => 'EMAIL',
                'division' => 'traffic',
                'subject' => 'Update Status Kargo, Estimasi Kedatangan (ETA) & SPPB - {project_no} ({vessel_name})',
                'body' => "Yth. Tim Logistik & Operasional {client_name},\n\nBerikut kami informasikan perkembangan posisi kargo dan status armada pengangkut terkini:\n• No. Proyek: {project_no}\n• Sarana Pengangkut: {vessel_name} (Voy: {voyage_no})\n• Estimasi Sandar (ETA): {eta_date}\n• Status Terkini: {shipment_status}\n• Terminal Bongkar: {tps_location}\n\n{custom_notes}\n\nSurat Jalan dan surat penugasan armada telah kami lampirkan. Tim armada lapangan kami telah standby untuk proses pengeluaran kargo.",
                'footer_notes' => 'PT GENIE LOGISTIK INDONESIA - Divisi Traffic & Armada Lapangan',
                'variables' => ['client_name', 'project_no', 'vessel_name', 'voyage_no', 'eta_date', 'shipment_status', 'tps_location', 'custom_notes'],
                'is_default' => true,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'code' => 'SJ_FCL_DEFAULT',
                'name' => 'Surat Jalan Format 1: Kontainer FCL Standard',
                'category' => 'SURAT_JALAN',
                'division' => 'traffic',
                'subject' => 'Surat Jalan Pengeluaran Kontainer FCL Pelabuhan',
                'terms_and_conditions' => "1. Segel kontainer (seal pelayaran dan Bea Cukai) wajib dalam keadaan utuh saat serah terima di gudang klien.\n2. Penerima wajib membubuhkan stempel resmi perusahaan, tanda tangan, nama jelas & jam terima barang pada lembar putih.\n3. Klaim kerusakan/kekurangan kargo harus dicatat langsung di lembar surat jalan sebelum truk meninggalkan lokasi penerima.",
                'signee_name' => 'Bambang Wijaya (Manager Ops Lapangan)',
                'emergency_phone' => '+62 811-3322-1100 / 031-3558899',
                'variables' => ['client_name', 'project_no', 'container_no', 'seal_no', 'truck_plate', 'driver_name'],
                'is_default' => true,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'code' => 'SJ_LCL_DEFAULT',
                'name' => 'Surat Jalan Format 2: Kargo Parsial LCL & Air Freight',
                'category' => 'SURAT_JALAN',
                'division' => 'traffic',
                'subject' => 'Surat Jalan Pengiriman Kargo Parsial LCL / Udara',
                'terms_and_conditions' => "1. Jumlah koli, kemasan, dan tanda marking kargo wajib dicocokkan dengan delivery note sebelum serah terima kargo.\n2. Kargo parsial ditimbang dan dihitung ulang di gudang CFS penimbunan sementara.\n3. Lembar stempel penerima menjadi bukti sah penyerahan kargo freight forwarder.",
                'signee_name' => 'Bambang Wijaya (Manager Ops Lapangan)',
                'emergency_phone' => '+62 811-3322-1100 / 031-3558899',
                'variables' => ['client_name', 'project_no', 'colli_count', 'gross_weight', 'truck_plate', 'driver_name'],
                'is_default' => true,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'code' => 'SJ_INTER_FACTORY_DEFAULT',
                'name' => 'Surat Jalan Format 3: Antar Pabrik Kawasan Berikat (KB)',
                'category' => 'SURAT_JALAN',
                'division' => 'traffic',
                'subject' => 'Surat Jalan Pengeluaran Antar Pabrik Kawasan Berikat',
                'terms_and_conditions' => "1. Pengeluaran barang antar Kawasan Berikat wajib dilengkapi persetujuan pabean BC 2.7 / BC 4.0 yang telah terbit barcode.\n2. Truk pengangkut wajib memiliki izin subkontrak / pergerakan barang berikat dari Kantor Pelayanan Bea dan Cukai.\n3. Lembar pelindung pengangkutan wajib dibawa sopir selama perjalanan.",
                'signee_name' => 'Bambang Wijaya (Manager Ops Lapangan)',
                'emergency_phone' => '+62 811-3322-1100 / 031-3558899',
                'variables' => ['client_name', 'project_no', 'bc_doc_type', 'bc_doc_no', 'truck_plate', 'driver_name'],
                'is_default' => true,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'code' => 'SJ_EMPTY_DEPO_DEFAULT',
                'name' => 'Surat Jalan Format 4: Pengembalian Empty Depo (EIR Return)',
                'category' => 'SURAT_JALAN',
                'division' => 'traffic',
                'subject' => 'Surat Jalan Pengembalian Kontainer Kosong ke Depo',
                'terms_and_conditions' => "1. Kontainer kosong wajib dikembalikan ke depo pelayaran dalam kondisi bersih, kering, dan bebas bau/residu.\n2. Petugas depo pelayaran wajib menerbitkan bukti EIR (Equipment Interchange Receipt) penerimaan empty.\n3. Kerusakan fisik pada dinding/lantai kontainer menjadi tanggung jawab sesuai ketentuan repair pelayaran.",
                'signee_name' => 'Bambang Wijaya (Manager Ops Lapangan)',
                'emergency_phone' => '+62 811-3322-1100 / 031-3558899',
                'variables' => ['client_name', 'project_no', 'container_no', 'depo_name', 'truck_plate', 'driver_name'],
                'is_default' => true,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'code' => 'TERMS_QUOTATION_DEFAULT',
                'name' => 'Klausul Syarat & Ketentuan Penawaran Harga',
                'category' => 'TERMS_KLAUSUL',
                'division' => 'marketing',
                'terms_and_conditions' => "1. Tarif penawaran belum termasuk PPN jasa kepabeanan & biaya tidak terduga di pelabuhan (storage, demurrage, behandle bila ada).\n2. Masa berlaku penawaran harga adalah 30 hari kalender sejak tanggal penerbitan.\n3. Perubahan kurs valuta asing (NDBM Bea Cukai) dan fluktuasi fuel surcharge pelayaran akan disesuaikan secara proporsional.",
                'variables' => ['client_name', 'valid_days'],
                'is_default' => true,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'code' => 'TERMS_INVOICE_DEFAULT',
                'name' => 'Klausul Instruksi Pembayaran Tagihan Invoice P19',
                'category' => 'TERMS_KLAUSUL',
                'division' => 'finance',
                'terms_and_conditions' => "1. Pembayaran tagihan wajib disetorkan penuh ke rekening resmi perusahaan tanpa potongan biaya transfer bank.\n2. Keterlambatan pelunasan melampaui tanggal jatuh tempo dapat dikenakan denda administrasi 2% per bulan.\n3. Bukti transfer mohon diunggah atau dikirimkan ke email finance@genie-logistics.id.",
                'variables' => ['client_name', 'due_date', 'bank_account'],
                'is_default' => true,
                'is_system' => true,
                'is_active' => true,
            ],
        ];

        foreach ($defaults as $def) {
            self::updateOrCreate(
                ['code' => $def['code']],
                $def
            );
        }
    }
}
