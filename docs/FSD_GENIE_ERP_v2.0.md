# DOKUMEN SPESIFIKASI FUNGSIONAL (FUNCTIONAL SPECIFICATION DOCUMENT - FSD)
## SISTEM ERP LOGISTIK & KEPABEANAN GENIE (PATS_CEISA) v2.0
**PT AGUNG TRANS SOLUSINDO & COOCA.ID**  
*Klasifikasi: Dokumen Arsitektur Teknis, Spesifikasi Data, & Algoritma Sistem*  
*Versi Dokumen: 2.0-Engineering*  
*Tanggal Rilis: 15 November 2026*  
*Dokumen Rujukan: docs/FEATURE_AUDIT_REPORT.md, docs/PRD_GENIE_ERP_v2.0.md, docs/BRD_GENIE_ERP_v2.0.md*

---

## DAFTAR ISI
1. [Arsitektur Sistem & Spesifikasi Tumpukan Teknologi (Technology Stack)](#1-arsitektur-sistem--spesifikasi-tumpukan-teknologi-technology-stack)
2. [Standar Data & Konvensi Rekayasa Perangkat Lunak](#2-standar-data--konvensi-rekayasa-perangkat-lunak)
3. [Spesifikasi Teknis Modul Inti & Algoritma (Technical Specifications)](#3-spesifikasi-teknis-modul-inti--algoritma-technical-specifications)
   - [3.1 Pipeline Asinkron F15: Batch Import Queue Worker](#31-pipeline-asinkron-f15-batch-import-queue-worker)
   - [3.2 Engine Pengiriman Email Rilis DO Pelayaran (P09 Mailer)](#32-engine-pengiriman-email-rilis-do-pelayaran-p09-mailer)
   - [3.3 Arsitektur Penyimpanan Media Privat & Kepatuhan Biometrik UU PDP](#33-arsitektur-penyimpanan-media-privat--kepatuhan-biometrik-uu-pdp)
   - [3.4 Penegakan Penguncian Periode Akuntansi via Model Observer](#34-penegakan-penguncian-periode-akuntansi-via-model-observer)
   - [3.5 Gerbang Verifikasi Dana Talangan Pabean (Pre-Funding Escrow Gate)](#35-gerbang-verifikasi-dana-talangan-pabean-pre-funding-escrow-gate)
   - [3.6 Spesifikasi CRUD Master Klien & Master Vendor](#36-spesifikasi-crud-master-klien--master-vendor)
   - [3.7 Integrasi Meta Cloud API WhatsApp WABA & Webhook Handler](#37-integrasi-meta-cloud-api-whatsapp-waba--webhook-handler)
   - [3.8 Mesin Rekonsiliasi Pajak Tahunan PPh 21 Masa Desember (1721-A1)](#38-mesin-rekonsiliasi-pajak-tahunan-pph-21-masa-desember-1721-a1)
4. [Diagram Hubungan Entitas Basis Data (Entity Relationship Diagram - ERD)](#4-diagram-hubungan-entitas-basis-data-entity-relationship-diagram---erd)
5. [Spesifikasi Rute & Matriks Otorisasi Permission (Route & RBAC Specification)](#5-spesifikasi-rute--matriks-otorisasi-permission-route--rbac-specification)
6. [Penanganan Eksepsi & Ketahanan Kesalahan (Error Handling & Resilience)](#6-penanganan-eksepsi--ketahanan-kesalahan-error-handling--resilience)

---

## 1. ARSITEKTUR SISTEM & SPESIFIKASI TUMPUKAN TEKNOLOGI

Sistem **GENIE (PATS_CEISA)** dirancang dengan arsitektur **Monolith Modern Mandiri (Standalone Enterprise Monolith)** yang memisahkan eksekusi antarmuka pengguna dengan pemrosesan komputasi berat di latar belakang (*background workers*):

```
┌────────────────────────────────────────────────────────────────────────┐
│                        ARSITEKTUR TEKNIS SISTEM                        │
├────────────────────┬────────────────────┬──────────────────────────────┤
│ 1. PRESENTATION     │ 2. APPLICATION CORE│ 3. INFRASTRUCTURE & STORAGE  │
│ • Tailwind CSS 3.4 │ • Laravel 11.x     │ • PHP 8.3.28 Engine          │
│ • Apple HIG Bento  │ • HasMiddleware    │ • MySQL 8.4.3 Enterprise     │
│ • Alpine.js v3     │ • Service Layer    │ • Redis 7.x Queue & Cache    │
│ • Lucide SVG Icons │ • Model Observers  │ • Storage Private Disk (AES) │
│ • DomPDF Engine    │ • MCP Protocol SSE │ • CEISA 4.0 Open API Gateway │
└────────────────────┴────────────────────┴──────────────────────────────┘
```

---

## 2. STANDAR DATA & KONVENSI REKAYASA PERANGKAT LUNAK

1. **Standar Identitas UUID v4 (100% Primary & Foreign Keys):**
   * Seluruh tabel basis data menggunakan migrasi `$table->uuid('id')->primary()`.
   * Seluruh relasi relasional menggunakan `$table->foreignUuid('..._id')` dengan *indexed foreign keys*.
   * Seluruh Eloquent Model menyertakan trait bawaan `Illuminate\Database\Eloquent\Concerns\HasUuids`.
2. **Standar Penghapusan Lembut (100% Soft Deletes):**
   * Seluruh tabel entitas bisnis menyertakan `$table->softDeletes()`.
   * Seluruh Eloquent Model menyertakan trait `Illuminate\Database\Eloquent\SoftDeletes`. Dilarang menggunakan penghapusan fisik permanen (`HARD DELETE`) pada level operasional.
3. **Pencatatan Audit Trail Forensik Global:**
   * Model bisnis mengimplementasikan trait `App\Traits\Auditable` yang mengaitkan event `created`, `updated`, `deleted`, dan `restored` untuk merekam JSON diff ke tabel `audit_logs`.
4. **Penegakan Hak Akses Berlapis (Controller-Level RBAC):**
   * Seluruh controller mengimplementasikan interface `Illuminate\Routing\Controllers\HasMiddleware` dengan pendefinisian permission granular per method (`only: [...]`, `except: [...]`).

---

## 3. SPESIFIKASI TEKNIS MODUL INTI & ALGORITMA

### 3.1 Pipeline Asinkron F15: Batch Import Queue Worker

Untuk mengeliminasi risiko memori habis dan HTTP 504 Timeout saat mengekstrak puluhan dokumen pabean sekaligus, alur impor massal diubah menjadi antrean asinkron multi-tahap:

```
[User Uploads ZIP] ──► Controller (HTTP 200 < 2s) ──► Queue: ExtractBatchZipJob
                                                             │
                                                             ▼
[CrossValidationJob] ◄── [DocumentOcrParserJob] ◄── [DocumentClassificationJob]
         │
         ▼
[Broadcast Status: FINISHED] ──► Polling AJAX / WebSocket Updates UI
```

#### Spesifikasi Kelas Job (`app/Jobs/Import/`):
1. **`ExtractBatchZipJob`**:
   * *Tugas:* Menerima path berkas ZIP dari `import_batches`, memeriksa rasio kompresi (menolak rasio kompresi $> 100:1$ atau uncompressed size $> 2\text{ GB}$ untuk proteksi *Zip Bomb*).
   * *Aksi:* Mengekstrak setiap berkas PDF ke `storage/app/private/imports/{batch_id}/`, menghitung hash SHA-256, dan membuat record `ImportFile`. Setelah selesai, memicu `DocumentClassificationJob`.
2. **`DocumentClassificationJob`**:
   * *Tugas:* Membaca halaman pertama setiap PDF menggunakan library `smalot/pdfparser`.
   * *Algoritma:* Mengklasifikasikan jenis dokumen (Invoice, Packing List, B/L, PIB, PEB, DO) berdasarkan pencocokan pola teks kata kunci dan menghitung skor keyakinan (*confidence score* 0–100).
3. **`DocumentOcrParserJob`**:
   * *Tugas:* Mengurai isi dokumen, mengekstrak nomor B/L, nomor kontainer, nilai devisa, serta daftar rincian barang.
   * *Output:* Menyimpan koordinat kotak batas (*bounding box* `{x, y, w, h, page}`) ke tabel `import_fields` dan daftar item barang ke `import_items`.
4. **`CrossDocumentValidationJob`**:
   * *Tugas:* Mengeksekusi service `CrossDocumentValidationService` untuk memverifikasi 10 aturan validasi silang (V01 s.d. V10):
     - `V01`: Konsistensi Nomor B/L antar-dokumen (Invoice, Packing List, B/L) — *Blocking*.
     - `V02`: Toleransi bobot kotor/koli Packing List vs B/L — *Warning*.
     - `V03`: Sinkronisasi akumulasi total item terhadap header invoice — *Warning*.
     - `V04`: Checksum digit kontainer ISO 6346 — *Warning*.
     - `V05`: Validasi keabsahan 8-digit Pos Tarif HS Code BTKI 2022 — *Blocking*.

---

### 3.2 Engine Pengiriman Email Rilis DO Pelayaran (P09 Mailer)

Menggantikan kode simulasi lama pada `ShipmentWorkflowService::sendVendorEmailForDo` dengan transmisi email riil berbasis antrean:

#### Implementasi Mailable (`app/Mail/VendorDoRequestMail.php`):
```php
namespace App\Mail;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class VendorDoRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Shipment $shipment, public string $customNotes = '') {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Permohonan Rilis Delivery Order (DO) - BL: {$this->shipment->bl_number} - {$this->shipment->client->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor_do_request',
            with: [
                'shipment' => $this->shipment,
                'vendor' => $this->shipment->shippingVendor,
                'notes' => $this->customNotes,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        // Ambil berkas B/L fisik dari relasi ShipmentDocument
        $blDoc = $this->shipment->documents()->where('document_type', 'BL')->where('is_current', true)->first();
        if ($blDoc && Storage::disk('private')->exists($blDoc->file_path)) {
            $attachments[] = Attachment::fromStorageDisk('private', $blDoc->file_path)
                ->as("BL_{$this->shipment->bl_number}.pdf")
                ->withMime('application/pdf');
        }
        return $attachments;
    }
}
```

#### Pemanggilan pada Service Workflow:
```php
Mail::to($shipment->shippingVendor->email)
    ->cc(config('mail.from.address'))
    ->queue(new VendorDoRequestMail($shipment, $customNotes));

$shipment->update([
    'status' => 'DO_REQUESTED',
    'do_requested_at' => now(),
]);
```

---

### 3.3 Arsitektur Penyimpanan Media Privat & Kepatuhan Biometrik UU PDP

Untuk menghentikan paparan data pribadi di webroot publik (`public/uploads/attendances/`):

#### 1. Konfigurasi Disk Penyimpanan Privat (`config/filesystems.php`):
```php
'disks' => [
    'private' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'visibility' => 'private',
        'throw' => true,
    ],
],
```

#### 2. Enkripsi Atribut pada Model `Employee`:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Employee extends Model
{
    use HasUuids;

    protected $casts = [
        'face_data' => 'encrypted', // Enkripsi AES-256 bawaan Laravel
        'salary_base' => 'encrypted',
        'is_active' => 'boolean',
    ];
}
```

#### 3. Controller Penyajian Media Bertanda Tangan (`PrivateMediaController.php`):
```php
public function showAttendancePhoto(string $attendanceLogId)
{
    $log = AttendanceLog::findOrFail($attendanceLogId);
    
    // Otorisasi: Hanya pemilik akun, HRD (R-HR), atau Admin/Owner yang boleh melihat
    $currentUser = auth()->user();
    if ($currentUser->employee_id !== $log->employee_id && !$currentUser->hasAnyRole(['R-HR', 'R-ADM', 'R-OWN'])) {
        abort(403, 'Akses ke data biometrik foto absensi ditolak sesuai UU PDP No. 27/2022.');
    }

    if (!Storage::disk('private')->exists($log->photo_path)) {
        abort(404, 'Berkas foto tidak ditemukan atau telah dimusnahkan sesuai masa retensi.');
    }

    return Storage::disk('private')->response($log->photo_path);
}
```

#### 4. Penegakan Validasi Persetujuan Biometrik Harian (`StaffPortalController.php`):
```php
public function clockIn(Request $request)
{
    $employee = auth()->user()->employee;
    
    // Validasi Kepatuhan UU PDP No. 27/2022
    if (!$employee->hasValidBiometricConsent()) {
        return back()->with('error', 'Persetujuan pemrosesan data biometrik (UU PDP No. 27/2022) wajib ditandatangani sebelum melakukan absensi kamera.');
    }
    
    // Proses absensi dan simpan foto ke storage privat...
    $path = $request->file('photo')->store('attendances/' . date('Y/m'), 'private');
    // ...
}
```

---

### 3.4 Penegakan Penguncian Periode Akuntansi via Model Observer

Mencegah pembatalan, pengubahan, atau penghapusan transaksi pada periode akuntansi yang telah ditutup (*Lock Period*):

#### Observer `app/Observers/JournalEntryObserver.php`:
```php
namespace App\Observers;

use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use DomainException;

class JournalEntryObserver
{
    public function creating(JournalEntry $entry): void
    {
        $this->validatePeriod($entry->entry_date);
    }

    public function updating(JournalEntry $entry): void
    {
        $this->validatePeriod($entry->getOriginal('entry_date'));
        $this->validatePeriod($entry->entry_date);
    }

    public function deleting(JournalEntry $entry): void
    {
        $this->validatePeriod($entry->entry_date);
    }

    private function validatePeriod(string $date): void
    {
        if (AccountingPeriod::isDateLocked($date)) {
            throw new DomainException("Transaksi pembukuan diblokir: Tanggal transaksi [{$date}] berada pada periode buku yang telah ditutup (Locked Accounting Period).");
        }
    }
}
```

---

### 3.5 Gerbang Verifikasi Dana Talangan Pabean (Pre-Funding Escrow Gate)

Mencegah pengeluaran kas perusahaan untuk pembayaran billing pabean MPN G3 bernilai besar tanpa kepastian dana importir:

```
[User Clicks: Bayar Billing MPN G3]
                 │
                 ▼
 ┌──────────────────────────────┐
 │ Cek Nilai Billing Pabean     │
 └───────────────┬──────────────┘
                 │
                 ▼
      [Nilai > Rp 5.000.000?] ───(Tidak)──► Eksekusi Pembayaran Kasir
                 │ (Ya)
                 ▼
 ┌──────────────────────────────────────────┐
 │ Status Dana Talangan Klien di Bank:      │
 │ Apakah `FUNDS_CLEARED` di Escrow Ledger? │
 └───────────────┬──────────────────────────┘
                 │
         ┌───────┴───────┐
       (Ya)             (Tidak)
         │               │
         ▼               ▼
 [Eksekusi Bayar]  [Apakah Memiliki Approval Direktur Utama (R-OWN)?]
                         │
                 ┌───────┴───────┐
               (Ya)             (Tidak)
                 │               │
                 ▼               ▼
          [Eksekusi Bayar]  [BLOKIR TRANSAKSI: Tampilkan Peringatan Escrow]
```

---

### 3.6 Spesifikasi CRUD Master Klien & Master Vendor

#### 1. Endpoint & Controller Master Klien (`ClientController.php`):
* `GET /clients` $\rightarrow$ `index()` (Pencarian nama, NPWP, NIB, status Surat Kuasa).
* `POST /clients` $\rightarrow$ `store()` (Pendaftaran klien baru dengan nomor kode otomatis `CLI-YYYYMM-XXXX`).
* `GET /clients/{client}` $\rightarrow$ `show()` (Detail profil, riwayat shipment, berkas legalitas).
* `PUT /clients/{client}` $\rightarrow$ `update()` (Pembaruan kontak PIC, alamat, masa berlaku Surat Kuasa).
* `POST /clients/{client}/toggle-status` $\rightarrow$ `toggleStatus()` (Aktif / Nonaktifkan klien).
* `POST /clients/{client}/upload-legal` $\rightarrow$ `uploadLegalDocument()` (Unggah PDF NIB, NPWP, Surat Kuasa ke storage privat).

#### 2. Endpoint & Controller Master Vendor (`VendorController.php`):
* `GET /vendors` $\rightarrow$ `index()` (Daftar vendor kategori: Pelayaran, Trucking, TPS, Depo).
* `POST /vendors` $\rightarrow$ `store()` (Pendaftaran vendor baru).
* `PUT /vendors/{vendor}` $\rightarrow$ `update()` (Pembaruan rekening bank, email operasional DO, termin pembayaran TOP).
* `POST /vendors/{vendor}/toggle-status` $\rightarrow$ `toggleStatus()`.

---

### 3.7 Integrasi Meta Cloud API WhatsApp WABA & Webhook Handler

#### Arsitektur Client & Webhook (`app/Services/Crm/MetaWabaService.php`):
1. **Transmisi Pesan Keluar (Outbound Graph API):**
   * Endpoint: `POST https://graph.facebook.com/v19.0/{phone-number-id}/messages`
   * Headers: `Authorization: Bearer {waba_token}`, `Content-Type: application/json`
   * Payload: JSON template pesan resmi berparameter dinamis (Nomor Aju, Nomor B/L, Status SPPB, Link Portal Klien).
2. **Webhook Masuk (Inbound Webhook Receiver):**
   * Verifikasi Veritoken: `GET /api/webhook/whatsapp` memvalidasi `hub.challenge`.
   * Verifikasi Integritas Pesan: Memvalidasi header `X-Hub-Signature-256` menggunakan kunci HMAC-SHA256.
   * Auto-Reply Bot: Jika klien mengirim pesan berformat *"Status {BL_NUMBER}"*, sistem mengekstrak nomor B/L dan membalas status kargo terkini secara otomatis.

---

### 3.8 Mesin Rekonsiliasi Pajak Tahunan PPh 21 Masa Desember (1721-A1)

#### Algoritma Service `AnnualTaxReconciliationService.php`:
1. Ambil seluruh data `PayslipLine` karyawan dari Masa Pajak Januari s.d. Desember.
2. Hitung **Penghasilan Bruto Setahun** = Total Gaji Pokok + Tunjangan Tetap + Lembur + Premi Asuransi JKK/JKM/Kesehatan Perusahaan.
3. Hitung **Pengurang Penghasilan**:
   * Biaya Jabatan Setahun: $5\% \times \text{Bruto Setahun}$ (Maksimal Rp 6.000.000).
   * Iuran JHT & JP Porsi Karyawan Setahun.
4. Hitung **Penghasilan Neto Setahun** = $\text{Bruto} - \text{Pengurang}$.
5. Hitung **Penghasilan Kena Pajak (PKP)** = $\text{Neto Setahun} - \text{PTKP Setahun}$ (sesuai status kawin/tanggungan).
6. Hitung **PPh 21 Terutang Setahun** menggunakan tarif progresif **Pasal 17 ayat (1) huruf a UU PPh**:
   * Lapisan I: $0 \text{ s.d. Rp } 60.000.000 \rightarrow 5\%$
   * Lapisan II: $> \text{Rp } 60.000.000 \text{ s.d. Rp } 250.000.000 \rightarrow 15\%$
   * Lapisan III: $> \text{Rp } 250.000.000 \text{ s.d. Rp } 500.000.000 \rightarrow 25\%$
   * Lapisan IV: $> \text{Rp } 500.000.000 \text{ s.d. Rp } 5.000.000.000 \rightarrow 30\%$
   * Lapisan V: $> \text{Rp } 5.000.000.000 \rightarrow 35\%$
7. Hitung **PPh 21 Masa Desember** = $\text{PPh 21 Terutang Setahun} - \text{Akumulasi PPh 21 yang Telah Dipotong (Jan–Nov)}$.
8. Terbitkan berkas PDF **Formulir 1721-A1** resmi menggunakan engine `Barryvdh\DomPDF\Facade\Pdf`.

---

## 4. DIAGRAM HUBUNGAN ENTITAS BASIS DATA (ERD)

```
┌─────────────────┐       1:N       ┌────────────────────────┐
│     clients     │────────────────►│       shipments        │
└─────────────────┘                 └───────────┬────────────┘
                                                │ 1:1
                                                ▼
┌─────────────────┐       1:N       ┌────────────────────────┐
│ shipping_vendors│────────────────►│    ceisa_documents     │
└─────────────────┘                 └───────────┬────────────┘
                                                │ 1:N
                                                ▼
┌─────────────────┐       1:N       ┌────────────────────────┐
│  vendor_bills   │◄────────────────│    client_invoices     │
└─────────────────┘                 └───────────┬────────────┘
                                                │ 1:1
                                                ▼
┌─────────────────┐       1:N       ┌────────────────────────┐
│ accounting_per. │────────────────►│     journal_entries    │
└─────────────────┘                 └───────────┬────────────┘
                                                │ 1:N
                                                ▼
                                    ┌────────────────────────┐
                                    │     journal_lines      │
                                    └────────────────────────┘
```

---

## 5. SPESIFIKASI RUTE & MATRIKS OTORISASI PERMISSION

| Rute URL | HTTP Method | Nama Rute | Controller & Method | Permission yang Dibutuhkan |
| :--- | :---: | :--- | :--- | :--- |
| `/core/documents` | `GET` | `core.document.index` | `ShipmentProjectController@index` | `document.view` |
| `/core/documents/{id}/submit-ceisa`| `POST` | `core.document.submit-ceisa` | `ShipmentProjectController@generateAndSubmitCeisa` | `ceisa.submit` |
| `/core/do/{id}/send-email` | `POST` | `core.do.send-email` | `DoWorkflowController@sendVendorEmail` | `do.manage` |
| `/core/traffic/{id}/gate-out`| `POST` | `core.traffic.gate-out` | `TrafficWorkflowController@gateOut` | `traffic.manage` |
| `/clients` | `GET` | `crm.clients.index` | `ClientController@index` | `crm.view` |
| `/clients` | `POST` | `crm.clients.store` | `ClientController@store` | `crm.manage` |
| `/clients/{client}` | `PUT` | `crm.clients.update` | `ClientController@update` | `crm.manage` |
| `/vendors` | `GET` | `vendors.index` | `VendorController@index` | `do.view` / `finance.view` |
| `/vendors` | `POST` | `vendors.store` | `VendorController@store` | `do.manage` / `finance.manage` |
| `/finance/invoices/{id}/pay`| `POST` | `finance.invoices.pay` | `ClientInvoiceController@markAsPaid` | `finance.manage` |
| `/finance/bills/{id}/approve`| `POST` | `finance.bills.approve`| `VendorBillController@approve` | `finance.manage` (`R-OWN` jika >10jt) |
| `/accounting/close-period` | `POST` | `accounting.close-period` | `AccountingController@closePeriod` | `accounting.manage` |
| `/hrm/payroll/generate` | `POST` | `hrm.payroll.generate` | `HrmController@generatePayrollRun` | `hrm.manage` |
| `/hrm/payroll/1721-a1/{id}` | `GET` | `hrm.payroll.1721` | `HrmController@download1721A1` | `hrm.view` / `hrm.manage` |
| `/portal/track/{token}` | `GET` | `portal.track` | `ClientPortalController@track` | *Public (Rate Limited: 60/min)* |

---

## 6. PENANGANAN EKSEPSI & KETAHANAN KESALAHAN

1. **Penanganan Kegagalan Gateway CEISA 4.0:**
   * Jika server Bea Cukai mengembalikan error HTTP 500 atau timeout, sistem tidak membatalkan proyek shipment, melainkan mencatat status dokumen sebagai `SUBMISSION_PENDING_RETRY` dan menjadwalkan polling status otomatis dalam waktu 15 menit.
2. **Penanganan Kegagalan Pengiriman Email DO:**
   * Jika mailer SMTP gagal mengirimkan pesan ke agen pelayaran, sistem mencatat pesan error pada tabel `shipment_events`, mengembalikan status ke `DO_EMAIL_FAILED`, dan menerbitkan tombol *"Kirim Ulang Email"* pada antarmuka Tim DO.
3. **Penanganan Pelanggaran Penguncian Periode Akuntansi:**
   * Setiap mutasi pada periode terkunci akan melempar `DomainException`. Controller dilarang menelan (*swallow*) exception ini; wajib mengembalikan flash error ke antarmuka pengguna: *"Transaksi ditolak: Periode akuntansi telah ditutup secara resmi"*.

---
*Dokumen Spesifikasi Fungsional (FSD) ini disahkan sebagai pedoman implementasi kode bagi seluruh teknisi pengembang sistem GENIE ERP v2.0.*  
*PT Agung Trans Solusindo & Cooca.id — All Rights Reserved © 2026.*