# RENCANA IMPLEMENTASI, ROADMAP SPRINT & TECHNICAL RUNBOOK
## PENYEMPURNAAN SISTEM ERP LOGISTIK & KEPABEANAN: GENIE (PATS_CEISA) v2.0
**PT AGUNG TRANS SOLUSINDO & COOCA.ID**  
*Klasifikasi: Rencana Kerja Rekayasa Perangkat Lunak, Jadwal Rilis & Prosedur Operasional Standar (SOP)*  
*Target Durasi: 4 Sprint / 8 Minggu Kerja*  
*Tanggal Berlaku: 15 November 2026*  
*Versi Dokumen: 2.0-Implementation*  
*Dokumen Rujukan: docs/FEATURE_AUDIT_REPORT.md, docs/BRD_GENIE_ERP_v2.0.md, docs/PRD_GENIE_ERP_v2.0.md, docs/FSD_GENIE_ERP_v2.0.md*

---

## DAFTAR ISI
1. [Ringkasan Eksekutif & Sasaran Rilis v2.0](#1-ringkasan-eksekutif--sasaran-rilis-v20)
2. [Peta Jalan Rilis per Sprint (4 Sprints / 8 Weeks Roadmap)](#2-peta-jalan-rilis-per-sprint-4-sprints--8-weeks-roadmap)
3. [Rincian Rencana Kerja per Sprint (Detailed Work Breakdown Structure)](#3-rincian-rencana-kerja-per-sprint-detailed-work-breakdown-structure)
   - [Sprint 1 (Minggu 1–2): Hotfix Kritis, Kepatuhan UU PDP & Pengerasan Akuntansi (P0)](#sprint-1-minggu-12-hotfix-kritis-kepatuhan-uu-pdp--pengerasan-akuntansi-p0)
   - [Sprint 2 (Minggu 3–4): Fondasi Asinkron Queue F15, Lompat Tahap F16 & Master Data (P0/P1)](#sprint-2-minggu-34-fondasi-asinkron-queue-f15-lompat-tahap-f16--master-data-p0p1)
   - [Sprint 3 (Minggu 5–6): Integrasi Operasional Riil & Penjaminan Mutu QA (P1)](#sprint-3-minggu-56-integrasi-operasional-riil--penjaminan-mutu-qa-p1)
   - [Sprint 4 (Minggu 7–8): Optimasi UI/UX Apple HIG, Predictive Demurrage & UAT (P1/P2)](#sprint-4-minggu-78-optimasi-uiux-apple-hig-predictive-demurrage--uat-p1p2)
4. [Prosedur Operasional Standar Penerapan (Technical Deployment Runbook)](#4-prosedur-operasional-standar-penerapan-technical-deployment-runbook)
5. [Rencana Kontinjensi, Manajemen Risiko & Prosedur Rollback](#5-rencana-kontinjensi-manajemen-risiko--prosedur-rollback)
6. [Matriks Kriteria Keberhasilan & Tanda Tangan Penerimaan (Sign-Off Matrix)](#6-matriks-kriteria-keberhasilan--tanda-tangan-penerimaan-sign-off-matrix)

---

## 1. RINGKASAN EKSEKUTIF & SASARAN RILIS v2.0

Dokumen ini memetakan rencana kerja terstruktur untuk menyelesaikan **28 temuan audit teknis (`FIX-01` s.d. `FIX-28`)** yang didokumentasikan dalam `docs/FEATURE_AUDIT_REPORT.md`. Pelaksanaan proyek dibagi secara bertahap dalam **4 Sprint selama 8 minggu**, mematuhi prinsip *Zero Breaking Changes* dan *Zero Data Loss*, guna memastikan operasional harian kepabeanan PT Agung Trans Solusindo tetap berjalan lancar tanpa gangguan.

### Sasaran Kunci Rilis v2.0:
1. Menghilangkan 100% bug penyebab crash HTTP 500 dan deadlock alur kerja pabean.
2. Mencapai 100% kepatuhan hukum terhadap **UU PDP No. 27/2022** untuk perlindungan data biometrik karyawan.
3. Mengaktifkan pemrosesan asinkron berbasis background queue worker pada modul Batch Import F15.
4. Mengubah seluruh fitur yang masih berupa simulasi/mock (Email DO pelayaran, WABA Meta, Face Attendance) menjadi fungsionalitas riil.
5. Menaikkan cakupan pengujian otomatis (*automated test coverage*) modul akuntansi dan finansial dari 0% menjadi minimal 85%.

---

## 2. PETA JALAN RILIS PER SPRINT (4 SPRINTS / 8 WEEKS ROADMAP)

```
[SPRINT 1: W1-W2] ──► Hotfix Kritis, Kepatuhan UU PDP & Pengerasan Akuntansi (Prioritas: P0)
        │
        ▼
[SPRINT 2: W3-W4] ──► Fondasi Asinkron Queue F15, Lompat Tahap F16 & Master Data CRUD (Prioritas: P0/P1)
        │
        ▼
[SPRINT 3: W5-W6] ──► Integrasi Operasional Riil (Email DO, Meta WABA, Form 1721-A1) & QA (Prioritas: P1)
        │
        ▼
[SPRINT 4: W7-W8] ──► Optimasi UI/UX Apple HIG, Predictive Demurrage Engine, Hardening & UAT (Prioritas: P1/P2)
```

---

## 3. RINCIAN RENCANA KERJA PER SPRINT (WORK BREAKDOWN STRUCTURE)

### SPRINT 1 (Minggu 1–2): Hotfix Kritis, Kepatuhan UU PDP & Pengerasan Akuntansi (P0)
*Fokus: Stabilitas Sistem, Eliminasi Celah Hukum Pidana UU PDP, dan Integritas Finansial.*

| ID Tiket | Modul Terkait | Estimasi | Penanggung Jawab | Deskripsi Pekerjaan & Kriteria Selesai |
| :---: | :--- | :---: | :--- | :--- |
| **SP1-01** | Core Dokumen | 4 Jam | Backend Engineer | **Perbaikan RouteNotFoundException (`FIX-01`):** Ubah `route('core.document.sync-all-ceisa')` menjadi `route('config.sync-all')` pada `resources/views/core/document_index.blade.php:39`. Halaman dokumen terbuka HTTP 200. |
| **SP1-02** | Lompat Tahap | 4 Jam | Backend Engineer | **Perbaikan Deadlock Seeder F16 (`FIX-02`):** Ubah `'approver_role' => 'R-SPV'` menjadi `'approver_role' => 'R-DOC-SPV'` pada `database/seeders/StagePrerequisiteSeeder.php:39, 48`. Supervisor dokumen dapat meng-approve tiket lompat. |
| **SP1-03** | Akuntansi | 4 Jam | Backend Engineer | **Perbaikan Variabel `$entryDate` (`FIX-03`):** Inisialisasi `$entryDate = $entryDate ?? now()->toDateString();` pada `AccountingService.php:371, 418`. Meniadakan fatal error undefined variable. |
| **SP1-04** | Test CRM & Pabean | 4 Jam | QA Engineer | **Penyelarasan Data Uji CRM & Pabean (`FIX-04, FIX-05`):** Potong string nomor aju pada `tests/Feature/CrmTest.php:28` menjadi 26 digit numerik dan perbarui assersi slot dokumen `Bc20CompleteLifecycleTest.php:121` menjadi 14 berkas. |
| **SP1-05** | HRM / UU PDP | 16 Jam | Security & Backend | **Relokasi & Enkripsi Media Absensi (`FIX-06, FIX-07`):** Pindahkan penyimpanan foto absensi dari `public/uploads/attendances` ke disk privat `storage/app/private/attendances/`. Tambahkan `'face_data' => 'encrypted'` pada model `Employee`. Alihkan tampilan melalui streaming controller bertanda tangan `PrivateMediaController`. URL publik mengembalikan 404/403. |
| **SP1-06** | HRM / Portal | 8 Jam | Backend Engineer | **Penegakan Persetujuan Biometrik Harian (`FIX-08`):** Pasang pengecekan `$employee->hasValidBiometricConsent()` pada method `clockIn` dan `clockOut` di `StaffPortalController.php`. Clock-in ditolak jika consent belum disetujui. |
| **SP1-07** | HRM / Cron | 8 Jam | Backend Engineer | **Scheduler Pemusnahan Data Retensi 2 Tahun (`FIX-09`):** Buat artisan command `biometric:purge-expired` untuk menghapus berkas foto dan data biometrik karyawan yang telah melewati batas retensi 2 tahun. Daftarkan di scheduler kernel harian. |
| **SP1-08** | Akuntansi / Lock | 12 Jam | Backend Engineer | **Model Observer Penguncian Buku (`FIX-21`):** Pasang `JournalEntryObserver` dan `JournalLineObserver` pada event `creating`, `updating`, dan `deleting` yang memblokir mutasi pada periode akuntansi tertutup (`is_closed = true`). |

---

### SPRINT 2 (Minggu 3–4): Fondasi Asinkron Queue F15, Lompat Tahap F16 & Master Data (P0/P1)
*Fokus: Skalabilitas Pemrosesan Massal Dokumen dan Kelengkapan Master Entitas Bisnis.*

| ID Tiket | Modul Terkait | Estimasi | Penanggung Jawab | Deskripsi Pekerjaan & Kriteria Selesai |
| :---: | :--- | :---: | :--- | :--- |
| **SP2-01** | F15 Batch Import | 24 Jam | Backend Engineer | **Pembangunan Laravel Queue Jobs F15 (`FIX-14`):** Buat folder `app/Jobs/Import/` berisi `ExtractBatchZipJob.php`, `DocumentClassificationJob.php`, `DocumentOcrParserJob.php`, dan `CrossDocumentValidationJob.php`. Migrasikan perulangan sinkron ke antrean worker. Upload ZIP 20 dokumen merespons HTTP dalam < 2 detik. |
| **SP2-02** | F15 UI / Polling | 12 Jam | Frontend Engineer | **Progress Bar Realtime Batch Import:** Pasang polling AJAX / WebSocket Alpine.js pada view batch import untuk menampilkan status ekstraksi dokumen secara dinamis per baris. |
| **SP2-03** | F15 Validasi Silang | 12 Jam | Backend Engineer | **Ekstensi Aturan Validasi Silang Pabean (V08–V10):** Lengkapi aturan pengecekan konsistensi valuta, keselarasan kode pelabuhan muat/bongkar, dan verifikasi batas lartas. |
| **SP2-04** | Master Klien | 16 Jam | Fullstack Engineer | **CRUD Lengkap & Dokumen Legalitas Klien (`FIX-16`):** Bangun controller dan view untuk Edit Klien, Nonaktifkan Klien, Hapus Klien, serta modal upload berkas PDF NIB, NPWP, dan Surat Kuasa PPJK. |
| **SP2-05** | Master Vendor | 16 Jam | Fullstack Engineer | **Menu & CRUD Master Vendor (`FIX-17`):** Buat Controller `VendorController` dan view `resources/views/vendors/` untuk mengelola data agen pelayaran, trucking, rekening bank pembayaran, dan kontak operasional rilis DO. |
| **SP2-06** | Finance / Escrow | 12 Jam | Backend Engineer | **Pre-Funding Escrow Verification Gate (`FIX-18`):** Pasang kunci validasi pada pembayaran billing MPN G3: pembayaran diblokir kecuali saldo talangan kargo berstatus `FUNDS_CLEARED` di bank atau ada otorisasi tertulis Direktur Utama (`R-OWN`). |

---

### SPRINT 3 (Minggu 5–6): Integrasi Operasional Riil & Penjaminan Mutu QA (P1)
*Fokus: Eliminasi Mock Stubs, Koneksi API Eksternal Riil, dan Pengujian Otomatis Finansial.*

| ID Tiket | Modul Terkait | Estimasi | Penanggung Jawab | Deskripsi Pekerjaan & Kriteria Selesai |
| :---: | :--- | :---: | :--- | :--- |
| **SP3-01** | DO Pelayaran | 12 Jam | Backend Engineer | **Pengiriman Email DO Riil ke Pelayaran (`FIX-10`):** Implementasikan kelas mailable `App\Mail\VendorDoRequestMail` dan hubungkan `ShipmentWorkflowService::sendVendorEmailForDo` dengan driver mail resmi `Mail::to()` dengan lampiran PDF B/L. |
| **SP3-02** | HRM / Biometrik | 20 Jam | Fullstack Engineer | **Integrasi Engine Biometrik Wajah Riil (`FIX-11`):** Singkirkan skor kecocokan hardcoded. Pasang modul ekstraksi vektor wajah di sisi klien ponsel menggunakan WebAssembly/MediaPipe atau integrasikan engine biometrik server-side. |
| **SP3-03** | CRM / WhatsApp | 24 Jam | Backend Engineer | **Integrasi Meta Cloud Graph API v19 (`FIX-12`):** Hubungkan controller WhatsApp ke endpoint resmi Meta Cloud API, buat handler webhook penerima pesan masuk dengan verifikasi token HMAC-SHA256, dan bangun bot balasan otomatis status kontainer. |
| **SP3-04** | Media Sosial | 8 Jam | Backend Engineer | **Pembersihan Metrik Acak Media Sosial (`FIX-13`):** Singkirkan pemanggilan `rand()` pada `SocialMediaController.php` dan gantikan dengan metrik analitik riil atau label transparan estimasi. |
| **SP3-05** | Payroll / Pajak | 20 Jam | Backend Engineer | **Rekonsiliasi PPh 21 Masa Desember & Cetak Form 1721-A1 PDF (`FIX-15`):** Bangun kalkulator pajak progresif Pasal 17 setahun penuh pada `AnnualTaxReconciliationService.php` dan rancang template PDF Form 1721-A1 resmi standar Ditjen Pajak. |
| **SP3-06** | QA Akuntansi | 24 Jam | QA Engineer | **Penyusunan Test Suite Otomatis Akuntansi (`FIX-22`):** Tulis rangkaian pengujian otomatis feature tests di `tests/Feature/Accounting/` (Buku Besar, Kas/Bank, Utang Vendor, Penguncian Buku) untuk menaikkan cakupan pengujian dari 0% menjadi minimal 85%. |

---

### SPRINT 4 (Minggu 7–8): Optimasi UI/UX Apple HIG, Predictive Demurrage & UAT (P1/P2)
*Fokus: Ergonomi Pengguna, Pencegahan Denda Pelayaran, Hardening Keamanan, dan UAT Final.*

| ID Tiket | Modul Terkait | Estimasi | Penanggung Jawab | Deskripsi Pekerjaan & Kriteria Selesai |
| :---: | :--- | :---: | :--- | :--- |
| **SP4-01** | UX Dokumen | 12 Jam | Frontend Engineer | **Auto-Save Draft & Dirty-Form Guard (`FIX-23`):** Pasang debounced auto-save berkala 15 detik pada formulir pabean BC 2.0 / BC 3.0 dan event listener `beforeunload` untuk mencegah kehilangan data draf. |
| **SP4-02** | UX Dokumen | 16 Jam | Frontend Engineer | **Dekomposisi View Monolitik Dokumen (`FIX-24`):** Pecah berkas raksasa `document_index.blade.php` (150 KB) dan `document_show.blade.php` (135 KB) menjadi komponen Blade modular dengan pemuatan modal berbasis lazy-loading AJAX. FCP < 1,2 detik. |
| **SP4-03** | UI Tipografi | 8 Jam | Frontend Engineer | **Standardisasi Angka Tabular Monospace (`FIX-25`):** Terapkan kelas `font-mono tabular-nums tracking-tight` pada seluruh kolom tabel yang memuat data angka teknis pabean (Nomor Aju 26-digit, Nomor Kontainer, HS Code, Devisa, PPN). |
| **SP4-04** | UI Token Desain | 8 Jam | Frontend Engineer | **Penyelarasan Warna Token Desain & Mobile Sheet (`FIX-26, FIX-27`):** Ganti seluruh hex `#007AFF` dengan token semantik Sky Blue (`text-sky-600`, `bg-sky-600`) dan ubah menu mobile tab bar menjadi iOS Native Bottom Sheet Modal. |
| **SP4-05** | DO / Logistik | 16 Jam | Backend Engineer | **Predictive Demurrage Ticker (`FIX-20`):** Buat artisan command `CheckDemurrageAlertsCommand` yang memicu notifikasi peringatan eskalasi otomatis pada H-3, H-2, dan H-1 ke WhatsApp Manager Operasional dan Klien sebelum denda pelayaran berjalan. |
| **SP4-06** | Keamanan Siber | 4 Jam | Security Engineer | **Rate Limiting Portal Publik (`FIX-28`):** Pasang middleware `throttle:60,1` pada seluruh rute publik `/portal/track/{token}` untuk mencegah serangan brute-force enumerasi token UUID. |
| **SP4-07** | QA & UAT | 20 Jam | QA & All Leads | **End-to-End Regresion Test & UAT Direksi:** Jalankan seluruh 116 test PHPUnit dan eksekusi skrip `scripts/verify_feature_audit.ps1` (14/14 PASS). Uji coba operasional penuh bersama Direksi dan Pimpinan PT Agung Trans Solusindo. |

---

## 4. PROSEDUR OPERASIONAL STANDAR PENERAPAN (TECHNICAL DEPLOYMENT RUNBOOK)

Setiap penerapan kode pembaruan (*deployment*) ke lingkungan server produksi wajib mematuhi protokol teknis berikut:

### Langkah 1: Pencadangan Penuh Basis Data & Berkas (Backup Snapshot)
```powershell
# Snapshot database MySQL produksi sebelum migrasi
mysqldump -u root -p PATS_CEISA --routines --triggers --single-transaction > "C:\laragon\www\GENIE\storage\backups\PATS_CEISA_pre_sprint_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"

# Cadangkan berkas media privat
Copy-Item -Path "storage\app\private" -Destination "storage\backups\private_storage_$(Get-Date -Format 'yyyyMMdd')" -Recurse
```

### Langkah 2: Pembaruan Kode Sumber & Migrasi Basis Data
```powershell
# Aktifkan mode pemeliharaan jika diperlukan
php artisan down --secret="agungtrans-deploy-2026"

# Tarik pembaruan kode dan jalankan migrasi
git pull origin master
php artisan migrate --force

# Jalankan seeder koreksi peran F16 jika diperlukan
php artisan db:seed --class=StagePrerequisiteSeeder --force
```

### Langkah 3: Optimasi Konfigurasi & Antrean Worker
```powershell
# Hapus dan kompilasi ulang cache produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart antrean background worker queue
php artisan queue:restart

# Pastikan worker Redis/Database aktif
# php artisan queue:work --tries=3 --timeout=120
```

### Langkah 4: Verifikasi Forensik Otomatis Pasca-Deploy
```powershell
# Eksekusi skrip verifikasi mandiri 14-Check
powershell -ExecutionPolicy Bypass -File scripts\verify_feature_audit.ps1

# Nonaktifkan mode pemeliharaan
php artisan up
```

---

## 5. RENCANA KONTINJENSI, MANAJEMEN RISIKO & PROSEDUR ROLLBACK

1. **Jendela Pembatalan Penerapan (Rollback Window):**
   * Setiap deployment memiliki masa evaluasi 2 jam pasca-rilis. Jika terjadi error kritis (HTTP 500 pada dokumen, kegagalan submit CEISA, atau data loss), tim IT berwenang melakukan rollback seketika.
2. **Prosedur Pemulihan Basis Data (Database Rollback):**
   ```powershell
   php artisan down
   mysql -u root -p PATS_CEISA < "C:\laragon\www\GENIE\storage\backups\PATS_CEISA_pre_sprint_latest.sql"
   git checkout HEAD~1
   php artisan config:clear
   php artisan up
   ```
3. **Penyelamatan Berkas Sementara F15:**
   * Jika ekstraksi batch ZIP gagal di tengah jalan, berkas sementara pada `storage/app/private/imports/temp_{batch_id}` otomatis dihapus agar tidak memenuhi kapasitas disk server.

---

## 6. MATRIKS KRITERIA KEBERHASILAN & TANDA TANGAN PENERIMAAN (SIGN-OFF MATRIX)

| Tahap Rilis Sprint | Target Tanggal Selesai | Kriteria Keberhasilan (DoD) | Penanggung Jawab Verifikasi | Status Persetujuan |
| :--- | :---: | :--- | :--- | :---: |
| **Sprint 1 (Hotfix & UU PDP)** | Minggu ke-2 | • Eliminasi error 500 view dokumen.<br>• Foto absensi 100% aman di disk privat.<br>• Supervisor dapat approve F16.<br>• Unit tests 100% lolos. | Security Lead & HR Lead | `[ ] PENDING` |
| **Sprint 2 (Queue & Master Data)**| Minggu ke-4 | • Background queue F15 aktif.<br>• Upload ZIP 20 file respons < 2s.<br>• CRUD Klien & Vendor berfungsi penuh.<br>• Escrow gate talangan aktif. | IT Lead & Customs Lead | `[ ] PENDING` |
| **Sprint 3 (Integrasi Riil & QA)**| Minggu ke-6 | • Email DO pelayaran terkirim riil.<br>• Live Meta WABA terhubung.<br>• Form 1721-A1 PDF siap cetak.<br>• Test coverage akuntansi > 85%. | Finance Lead & Operations Mgr | `[ ] PENDING` |
| **Sprint 4 (UI/UX & UAT Final)** | Minggu ke-8 | • Predictive Demurrage Engine aktif.<br>• View monolitik tuntas didekomposisi.<br>• Seluruh 14 checks verify script PASS.<br>• Berita Acara UAT disetujui Direksi. | Direktur Utama (Owner) | `[ ] PENDING` |

---

### Lembar Pengesahan Rencana Kerja
Disetujui dan disahkan di Jakarta, 15 November 2026:

| Pihak Pengembang (Cooca.id) | Pihak Pemilik Sistem (PT Agung Trans Solusindo) |
| :---: | :---: |
| <br><br>___________________________<br>**Lead System Architect**<br>Cooca.id Software Engineering | <br><br>___________________________<br>**Bpk. Soehartono**<br>Direktur Utama / Pimpinan |

---
*Dokumen Rencana Implementasi & SOP Teknis ini menjadi acuan operasional resmi dalam pengembangan sistem GENIE ERP v2.0.*  
*PT Agung Trans Solusindo & Cooca.id — All Rights Reserved © 2026.*