# Rencana Implementasi & Perbaikan Sistem (Implementation Plan)
**Genie ERP Import-Export — PT Agung Trans Solusindo & Cooca.Id**
Versi 1.0 | November 2026 | Status: Dokumen Kerja Disetujui
Dokumen Pasangan: BRD v2.0, PRD v2.0, FSD v1.0, Brief Manajemen Risiko, & Laporan Audit Gap

---

## 1. Ringkasan Eksekutif & Tujuan Dokumen

Berdasarkan hasil audit sistem pada repositori `GENIE`, ditemukan kesenjangan (*gap*) antara kapabilitas prototipe awal (PATS_CEISA) dengan spesifikasi kebutuhan pada **BRD/PRD v2.0** dan **FSD v1.0**. 

Kesenjangan paling mendasar mencakup:
1. **0% Implementasi Dua Fitur Inti v2.0**: F15 (*Import Massal & Ekstraksi Asinkron*) dan F16 (*Impor Data Historis & Lompat Tahap*) belum memiliki tabel database, queue worker, maupun antarmuka khusus.
2. **Kepatuhan Pajak & Finansial**: Rumus PPh 21 masih menggunakan nilai taksiran statis (*mock*), iuran BPJS belum lengkap (belum mencakup plafon upah JP Maret 2026 & iuran perusahaan), dan pencairan payroll belum menerbitkan jurnal pembukuan otomatis.
3. **Integritas Periode & Akuntansi**: Ketiadaan penguncian periode akuntansi bulanan (*lock period*), ketiadaan antarmuka jurnal manual dan jurnal pembalik, serta depresiasi aset tetap yang belum terhubung ke buku besar.
4. **Keamanan & Jejak Audit (Audit Trail)**: Tabel `audit_logs` belum diaktifkan dalam bentuk Model dan Event Listener, penyimpanan data foto biometrik masih berada di folder publik, dan isolasi data per klien belum ditegakkan di level kueri pengguna operasional.

Dokumen ini menyusun **rencana kerja bertahap (4 Sprint / 8 Minggu)** yang terstruktur, aman, terukur, dan mematuhi prinsip *Zero Data Loss* serta arsitektur Apple HIG Bento Design.

---

## 2. Strategi Rilis & Peta Jalan Sprint (Roadmap)

```
[SPRINT 1: W1-W2] ──> Integritas Finansial, Kepatuhan Pajak & Forensik Audit (P0)
       │
[SPRINT 2: W3-W4] ──> Fondasi Core v2.0: F15 (Batch Import) & F16 (Lompat Tahap) (P0)
       │
[SPRINT 3: W5-W6] ──> Antarmuka Cerdas: Split-Screen Review, PDF Bounding Box & Wizard (P1)
       │
[SPRINT 4: W7-W8] ──> Modul Ekstensi: To-Do Engine, Sales CRM, Hardening & UAT (P1/P2)
```

---

## 3. Rincian Rencana Implementasi per Sprint

### SPRINT 1: Integritas Finansial, Kepatuhan Pajak & Forensik Audit
*Durasi: Minggu 1 – Minggu 2 | Prioritas: P0 (Kritis Kepatuhan & Regulasi)*

#### 1.1 Aktivasi Jejak Audit Global (*Global Append-Only Audit Trail*)
* **Kebutuhan**: FSD 14.2, BR-02, Mitigasi Risiko R18.
* **Tindakan**:
  1. Buat Model `App\Models\AuditLog` dengan relasi ke `User`.
  2. Buat Trait `App\Traits\Auditable` yang mengaitkan Eloquent Events (`created`, `updated`, `deleted`, `restored`).
  3. Catat payload lengkap: `actor_id`, `actor_type`, `action`, `object_type`, `object_id`, `old_value` (JSON), `new_value` (JSON), `ip_address`, dan `user_agent`.
  4. Pasang `Auditable` pada model inti: `Shipment`, `ShipmentDocument`, `ClientInvoice`, `VendorBill`, `JournalEntry`, `PayrollRun`, `User`, `Role`.
  5. Buat antarmuka viewer Audit Trail di menu Pengaturan Keamanan untuk Pimpinan (Role: `R-OWN`).

#### 1.2 Standardisasi Payroll TER PMK 168/2023 & BPJS 2026
* **Kebutuhan**: FSD 11.4, FR-PAY-01 s/d FR-PAY-05, Mitigasi Risiko R23.
* **Tindakan**:
  1. Buat seeder/tabel konfigurasi parameter tarif (`payroll_tax_parameters`) agar tarif tidak di-hardcode:
     - Kategori TER A, B, C dan tabel lapisan bruto PMK 168/2023.
     - Batas upah BPJS Kesehatan (Rp12.000.000; Karyawan 1%, Perusahaan 4%).
     - Batas upah BPJS Ketenagakerjaan JP (Rp11.086.300 berlaku efektif Maret 2026; Karyawan 1%, Perusahaan 2%).
     - JHT (Karyawan 2%, Perusahaan 3,7%), JKK (0,24%), JKM (0,3%).
     - Lembur: 1/173 × (Gaji Pokok + Tunjangan Tetap) (Jam 1 = 1,5×, Jam 2+ = 2×).
  2. Refaktor `HrmController::generatePayrollRun`:
     - Hitung iuran BPJS karyawan dan beban BPJS perusahaan secara terpisah.
     - Hitung Bruto Pajak = Penghasilan Setelah Alpa + Premi Asuransi JKK/JKM/Kes Perusahaan.
     - Hitung PPh 21 menggunakan tabel TER sesuai status PTKP pegawai.
  3. Integrasikan `HrmController::disbursePayrollRun` dengan `AccountingService::createBalancedEntry`:
     - **Debit**: Beban Gaji Karyawan (`5-2100`) & Beban BPJS Perusahaan (`5-2200`).
     - **Kredit**: Utang Gaji Netto (`2-1200`), Utang BPJS Gabungan (`2-1300`), Utang PPh 21 (`2-1400`), Piutang Karyawan/Kasbon (`1-1300`).
     - Saat transfer kas keluar: Debit Utang Gaji, Kredit Kas/Bank (`1-1130`).
  4. Terbitkan otomatis jadwal setoran pajak (tgl 10) dan BPJS (tgl 10 & 15) di kalender Finance.

#### 1.3 Periode Akuntansi & Penguncian Buku (*Lock Period*)
* **Kebutuhan**: FSD 10.2, FR-ACC-01 s/d FR-ACC-05, Mitigasi Risiko R13.
* **Tindakan**:
  1. Buat migration & model `accounting_periods`:
     - Kolom: `id`, `name` (e.g. "Oktober 2026"), `start_date`, `end_date`, `is_closed`, `closed_at`, `closed_by_id`.
  2. Implementasikan middleware/guardrail `ValidateAccountingPeriod`:
     - Mencegah posting transaksi jurnal baru atau pembalikan jurnal pada periode yang berstatus `is_closed = true`.
  3. Tambahkan fitur Jurnal Penutup (*Period Closing Action*):
     - Validasi neraca saldo seimbang, selisih rekonsiliasi kas nol, seluruh depresiasi dan gaji terposting.
     - Terbitkan jurnal penutup: Pindahkan saldo seluruh akun Pendapatan (4-xxxx) dan Beban (5-xxxx) ke Laba Ditahan (`3-2100`).
  4. Buat fitur input Jurnal Manual (Draf & Posting) serta aksi **Pembalikan Jurnal (*Reverse Entry*)** otomatis satu-klik.

#### 1.4 Kontrol Batas Approval Hutang Vendor (> Rp 10 Juta)
* **Kebutuhan**: FSD 10.4, Aturan Bisnis #10.
* **Tindakan**:
  - Pasang verifikasi pada `VendorBillController::pay`: Jika nominal tagihan `> Rp 10.000.000`, wajib memiliki record approval berstatus `APPROVED` dari Pimpinan (`R-OWN`). Bila belum ada, pembayaran diblokir dan tiket approval diterbitkan otomatis ke dashboard Pimpinan.

#### 1.5 Versioning Dokumen Fisik (`ShipmentDocument`)
* **Kebutuhan**: FSD 3.3, FR-DOC-02 s/d FR-DOC-05.
* **Tindakan**:
  - Tambahkan kolom `version` (integer, default 1) dan `is_current` (boolean, default true) pada tabel `shipment_documents`.
  - Saat pengunggahan berkas pengganti: set berkas lama `is_current = false`, simpan berkas baru dengan `version = version_lama + 1`, dan ubah status menjadi `PENDING_VERIFICATION`.

---

### SPRINT 2: Fondasi Core v2.0 — Arsitektur F15 & F16
*Durasi: Minggu 3 – Minggu 4 | Prioritas: P0 (Core Value v2.0)*

#### 2.1 Database & Queue Engine F15 (Import Massal & Ekstraksi Asinkron)
* **Kebutuhan**: BR-05 s/d BR-08, F15-01 s/d F15-13, FSD Bagian 4.
* **Tindakan**:
  1. Eksekusi Migration Tabel Inti F15:
     - `import_batches`: ID batch, uploader, status, total_files, processed_files, success_count, error_count, metadata.
     - `import_files`: file_name, file_hash (SHA-256), mime_type, file_size, storage_path, status, detected_type, confidence_score.
     - `import_documents`: logical document (split per halaman jika multi-doc), document_type, page_range, project_id, status.
     - `import_fields`: field_name, original_value, corrected_value, confidence_score, page_no, bounding_box (JSON `{x, y, w, h}`), status, edited_by_id.
     - `import_items`: baris item komoditas kargo (uraian, kemasan, bobot, nilai, HS code usulan, confidence).
     - `import_validations`: hasil evaluasi aturan V01 s/d V10 per dokumen.
     - `import_applications`: rekam jejak idempotensi saat data diterapkan ke shipment aktif.
  2. Buat Service Pipeline & Background Queue Workers:
     - `BatchZipExtractionJob`: Proteksi zip-bomb (max depth 2, max uncompressed size 2GB), sanitasi nama path, sanitasi malware.
     - `DocumentClassificationJob`: Klasifikasi jenis dokumen (Invoice, Packing List, BL, PIB, PEB, DO, SPPB, dll) beserta confidence score.
     - `DocumentOcrParserJob`: Ekstraksi teks & koordinat bounding box terstruktur menggunakan adapter pattern (`OcrProviderInterface`).
     - `ShipmentClusteringService`: Algoritma pengelompokan dokumen otomatis ke satu nomor shipment berdasarkan kunci identik (Nomor BL, Nomor Kontainer, Nomor Invoice, atau Nomor Aju).

#### 2.2 Database & Logika Bisnis F16 (Impor Data Historis & Lompat Tahap)
* **Kebutuhan**: BR-09 s/d BR-10, F16-01 s/d F16-10, FSD Bagian 5.
* **Tindakan**:
  1. Eksekusi Migration Tabel Inti F16:
     - `stage_prerequisites`: Daftar dokumen dan field wajib per pos 1 s/d 11 (dapat dikonfigurasi admin).
     - `shipment_stage_entries`: Status historis per tahap per shipment (`NATIVE`, `IMPORTED`, `SKIPPED`), sumber impor, tanggal lampau asli.
     - `project_jump_requests`: Pengajuan lompat tahap, alasan, penyetuju, perlakuan keuangan, status (`PENDING`, `APPROVED`, `REJECTED`).
     - `jump_batches` & `jump_batch_rows`: Fasilitas lompat massal via unggahan template spreadsheet XLSX/CSV.
  2. Implementasikan Service `StageJumpService`:
     - Logika evaluasi prasyarat tahap tujuan: Jika prasyarat pos tujuan belum terpenuhi, tolak eksekusi dan sajikan daftar field yang wajib dilengkapi.
     - Proteksi persetujuan: Lompat yang melintasi tahap kepabeanan (Pos 3 & 4) atau keuangan (Pos 11) mewajibkan tiket approval Supervisor atau Pimpinan. Pemohon dilarang menyetujui tiketnya sendiri (*four-eyes principle*).
     - Kepatuhan SLA/KPI: Tahap bertanda `IMPORTED` atau `SKIPPED` dikecualikan secara absolut dari penghitungan SLA keterlambatan dan KPI produktivitas staf.
  3. Modul Isolasi Finansial Data Migrasi:
     - Opsi **Saldo Awal**: Tidak menerbitkan jurnal baru, melainkan mencatat saldo piutang/utang terbuka (*open item AR/AP*) per cut-off date.
     - Opsi **Posting Historis**: Menerbitkan jurnal bertanggal historis hanya jika periode buku berstatus `Open`. Jika periode berstatus `Closed`, paksa pengguna memilih opsi Saldo Awal.
  4. Fasilitas *Backfill*: Memungkinkan staf melengkapi data tahap lampau kapan saja tanpa merusak tahap aktif yang sedang berjalan di lapangan.

---

### SPRINT 3: Antarmuka Cerdas & User Experience v2.0
*Durasi: Minggu 5 – Minggu 6 | Prioritas: P1 (Fungsional & Usability)*

#### 3.1 Layar Terbagi Peninjauan Dokumen (*Split-Screen Review*)
* **Kebutuhan**: F15-06 s/d F15-08, FR-REV-01 s/d FR-REV-14.
* **Tindakan**:
  1. Bangun komponen Blade + Alpine.js layout split 50:50 (responsif stack di layar kecil):
     - **Panel Kiri (Form Hasil Ekstraksi)**: Rincian field dengan penanda visual (Chip Hijau ≥90, Kuning 70–89, Merah <70, Biru = diedit manual).
     - **Panel Kanan (PDF Viewer Interaktif)**: Penampil berkas PDF dengan canvas overlay yang menggambar kotak sorotan (*bounding box*) berwarna sesuai field aktif.
  2. Interaksi Sinkronisasi Dua Arah:
     - Fokus/klik pada input field di kiri langsung menggulirkan halaman PDF di kanan ke lokasi koordinat teks sumber.
     - Klik pada area kotak di PDF otomatis mengarahkan kursor form ke field input terkait.
  3. Tabel Item Barang Dinamis: Fasilitas tambah baris, hapus, pisah baris, serta re-kalkulasi total otomatis.
  4. Mekanisme Kunci Dokumen (*Document Lock*): Mencegah dua staf meninjau dokumen yang sama secara bersamaan.

#### 3.2 Engine Validasi Silang (Cross-Validation Rules V01–V10)
* **Kebutuhan**: F15-09, FSD 4.11.
* **Tindakan**:
  - Bangun validator service yang mengeksekusi 10 aturan pabean:
    - `V01`: Konsistensi Nomor BL antar-dokumen (Invoice, Packing List, BL) — **Blocking**.
    - `V02`: Toleransi bobot kotor dan koli Packing List vs BL — **Warning**.
    - `V03`: Sinkronisasi total akumulasi item barang terhadap total header invoice — **Warning**.
    - `V04`: Validasi checksum digit kontainer ISO 6346 — **Warning**.
    - `V05`: Validasi keabsahan 8-digit Pos Tarif HS Code BTKI 2022 — **Blocking**.
    - `V07`: Kelengkapan kurs pajak KMK dan valuta pabean — **Blocking**.

#### 3.3 Wizard Interaktif "Mulai dari Tahap" (*Entry Stage Wizard*)
* **Kebutuhan**: F16-01 s/d F16-05, FSD 5.3.
* **Tindakan**:
  - Bangun antarmuka wizard 6 langkah:
    1. *Pilih Sumber*: Dari hasil import massal atau proyek yang sudah ada.
    2. *Pilih Tahap Tujuan*: Visualisasi 11 pos stepper dengan indikator kelayakan (aktif/non-aktif).
    3. *Lengkapi Prasyarat*: Form mini terpadu untuk mengisi kekurangan data minimum secara instan.
    4. *Pilihan Dampak Finansial*: Radio button eksplisit Saldo Awal vs Posting Historis.
    5. *Alasan & Permintaan Approval*: Input justifikasi bisnis wajib dan routing tiket persetujuan.
    6. *Konfirmasi & Mulai*: Pembuatan record `shipment_stage_entries` dan pengalihan PIC pos tujuan.

#### 3.4 Pengamanan Privasi Biometrik & Media Private (UU PDP No. 27/2022)
* **Kebutuhan**: FR-ABS-08, FSD 14.3, Mitigasi Risiko R19.
* **Tindakan**:
  - Pindahkan penyimpanan foto biometrik dari `public/uploads/faces` ke storage terisolasi `storage/app/private/biometrics`.
  - Buat route controller terproteksi yang menyajikan berkas gambar menggunakan URL bertoken sementara (*temporary signed URL*, berlaku 5 menit).

---

### SPRINT 4: Modul Ekstensi, CRM Penjualan & Hardening
*Durasi: Minggu 7 – Minggu 8 | Prioritas: P1 & P2 (Penyempurnaan Bisnis)*

#### 4.1 Modul To-Do & Papan Delegasi Tugas Rekan (F23)
* **Kebutuhan**: BR-21, F23, FSD 7.2.
* **Tindakan**:
  1. Migration tabel `todos` dan `todo_checklists`:
     - Kolom: `title`, `description`, `priority` (HIGH, MEDIUM, LOW), `due_date`, `status` (TODO, IN_PROGRESS, DONE), `assigned_to_user_id`, `created_by_user_id`, `shipment_id`, `is_recurring`, `recurrence_interval`.
  2. Tampilan Papan Kanban 3 Kolom & Integrasi Tugas Pos Otomatis (*Auto-Project Task*): PIC pos otomatis melihat tugas shipment aktifnya pada tab "Tugas Saya".
  3. Fitur pendelegasian tugas rekan kerja lengkap dengan notifikasi in-app dan WhatsApp.

#### 4.2 Multi-Line Quotation & Sales Order (F20)
* **Kebutuhan**: BR-16, F20, FSD Bagian 9.
* **Tindakan**:
  1. Buat tabel `quotation_lines` (deskripsi layanan, kuantitas, harga satuan, diskon persentase, subtotal) dan model `SalesOrder`.
  2. Implementasikan *Approval Guard*: Jika diskon pada baris penawaran melebihi 10%, status quotation beralih ke `PENDING_APPROVAL` dan memerlukan persetujuan Pimpinan sebelum dapat dikirim ke klien.
  3. Otomasi alur konversi: `Quotation (Accepted)` ➔ `Sales Order (Active)` ➔ `Shipment Project (Pos 1 Dokumen)`.

#### 4.3 Modul Kunjungan Sales Lapangan (F18)
* **Kebutuhan**: F18, FSD 11.3.
* **Tindakan**:
  1. Migration tabel `sales_visits`: sales_id, client_id, check_in_time, check_out_time, latitude, longitude, photo_path, is_out_of_radius, result_status, notes.
  2. Validasi geofencing 200 meter dari lokasi koordinat kantor klien. Check-in di luar radius mewajibkan alasan tertulis dan memicu notifikasi peringatan ke Pimpinan.
  3. Dashboard tracker peta rute perjalanan sales lapangan.

#### 4.4 Isolasi Data Klien Berbasis Penugasan (*Client Data Scoping*)
* **Kebutuhan**: BR-02, FSD 1.4, Mitigasi Risiko R20.
* **Tindakan**:
  - Terapkan Global Scope / Query Scope pada model `Shipment`:
    - Pengguna peran operasional (`R-DOC`, `R-DO`, `R-TRF`, `R-SLS`) hanya dapat membaca proyek dari klien yang ditugaskan kepada mereka.
    - Pengguna peran eksekutif (`R-OWN`, `R-MGR`, `R-SYS`) memiliki akses lintas klien (*unrestricted scope*).

#### 4.5 Testing, UAT, & Cut-Over Production
* **Tindakan**:
  - Eksekusi rangkaian Feature Test otomatis (`php artisan test`) untuk seluruh alur baru.
  - Uji beban pemrosesan batch import (500 berkas dokumen campuran).
  - Simulasi migrasi data historis paralel dengan verifikasi rekonsiliasi saldo kas/bank.
  - Pelatihan pengguna (*User Training*) per divisi operasional.

---

## 4. Matriks WBS (Work Breakdown Structure) & File Target

| ID WBS | Komponen | Tipe Berkas | Target Path Berkas |
|---|---|---|---|
| **WBS-1.1** | Model & Trait Audit Trail | PHP Model / Trait | `app/Models/AuditLog.php`, `app/Traits/Auditable.php` |
| **WBS-1.2** | Parameter Pajak TER & BPJS | Migration / Config | `database/migrations/xxxx_create_payroll_parameters_table.php`, `config/payroll.php` |
| **WBS-1.3** | Engine Payroll & Jurnal Gaji | Controller / Service | `app/Http/Controllers/HrmController.php`, `app/Services/Accounting/AccountingService.php` |
| **WBS-1.4** | Periode Akuntansi & Closing | Migration / Controller | `database/migrations/xxxx_create_accounting_periods_table.php`, `app/Http/Controllers/AccountingController.php` |
| **WBS-1.5** | Versioning Dokumen | Migration / Model | `database/migrations/xxxx_add_versioning_to_shipment_documents.php`, `app/Models/ShipmentDocument.php` |
| **WBS-2.1** | Migrations Skema F15 | Migration Files | `database/migrations/xxxx_create_f15_mass_import_tables.php` (7 tabel) |
| **WBS-2.2** | Background Queue Workers F15 | Queue Jobs | `app/Jobs/Import/ExtractBatchFilesJob.php`, `app/Jobs/Import/ProcessOcrParsingJob.php` |
| **WBS-2.3** | Migrations Skema F16 | Migration Files | `database/migrations/xxxx_create_f16_stage_jump_tables.php` (5 tabel) |
| **WBS-2.4** | Service Lompat Tahap F16 | Domain Service | `app/Services/Shipment/StageJumpService.php` |
| **WBS-3.1** | Split-Screen Review View | Blade View & Alpine | `resources/views/import/review_split.blade.php`, `public/js/pdf-highlighter.js` |
| **WBS-3.2** | Cross-Validation Service | Service Class | `app/Services/Shipment/CrossDocumentValidationService.php` |
| **WBS-3.3** | Wizard Mulai Dari Tahap | Blade Component | `resources/views/shipment/wizard_jump_stage.blade.php` |
| **WBS-3.4** | Media Private Storage | Middleware / Controller | `app/Http/Controllers/PrivateMediaController.php`, `routes/web.php` |
| **WBS-4.1** | Modul To-Do | Model / Controller | `app/Models/Todo.php`, `app/Http/Controllers/TodoController.php` |
| **WBS-4.2** | Multi-Line Quotation & SO | Model / Controller | `app/Models/QuotationLine.php`, `app/Models/SalesOrder.php`, `app/Http/Controllers/QuotationController.php` |
| **WBS-4.3** | Kunjungan Sales Tracker | Model / Controller | `app/Models/SalesVisit.php`, `app/Http/Controllers/SalesVisitController.php` |

---

## 5. Tata Kelola Risiko & Kriteria Keberhasilan (Definition of Done)

### Kriteria Penyelesaian Tiap Sprint (Sprint DoD)
1. **Zero Breaking Changes**: Seluruh fitur operasional kepabeanan Fase 1 (CEISA BC 2.0 / BC 3.0, tracking pos, invoice) tetap berjalan normal tanpa regresi.
2. **Test Pass Rate 100%**: Setiap penambahan controller dan service wajib disertai Unit/Feature Test dengan status *PASSED*.
3. **Database Migration Bersih**: Migration dapat dijalankan maju (`migrate`) dan mundur (`migrate:rollback`) tanpa menyisakan *foreign key constraint error*.
4. **Idempotensi Finansial**: Tidak ada pencatatan jurnal ganda (*double booking*) saat mengulang proses atau mengimpor data historis.
5. **Kepatuhan Privasi Data**: Tidak ada berkas identitas pribadi (KTP, NPWP, foto wajah) yang terekspos di URL publik tanpa token otentikasi.

### Rencana Cadangan & Rollback (Contingency Runbook)
- **Database Snapshot**: Sebelum eksekusi migration di setiap awal sprint, lakukan snapshot full database MySQL (`mysqldump`).
- **Jendela Pembatalan Penerapan F15/F16**: Sediakan batas waktu pembatalan 24 jam (*grace period*) untuk membatalkan penerapan batch impor selama belum ada transaksi operasional/keuangan turunan.

---

Dokumen ini menjadi acuan tunggal pengerjaan teknis perbaikan sistem Genie ERP v2.0 bagi tim teknis Cooca.Id dan tim pengawas PT Agung Trans Solusindo.
