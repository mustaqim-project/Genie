# DOKUMEN PERSYARATAN PRODUK (PRODUCT REQUIREMENT DOCUMENT - PRD)
## SISTEM ERP LOGISTIK & KEPABEANAN GENIE (PATS_CEISA) v2.0
**PT AGUNG TRANS SOLUSINDO & COOCA.ID**  
*Klasifikasi: Dokumen Rekayasa Produk & Spesifikasi Persyaratan Perangkat Lunak*  
*Versi Produk: 2.0-Enterprise*  
*Tanggal Rilis: 15 November 2026*  
*Dokumen Rujukan: docs/FEATURE_AUDIT_REPORT.md, docs/BRD_GENIE_ERP_v2.0.md, docs/DESIGN_RULES_PATS_CEISA.md*

---

## DAFTAR ISI
1. [Visi Produk & Ringkasan Solusi](#1-visi-produk--ringkasan-solusi)
2. [Matriks Persona Pengguna & Peran Resmi (12 Roles)](#2-matriks-persona-pengguna--peran-resmi-12-roles)
3. [Daftar Persyaratan Fungsional Produk (Functional Requirements FR-01 s.d. FR-28)](#3-daftar-persyaratan-fungsional-produk-functional-requirements-fr-01-sd-fr-28)
   - [3.1 Modul Dokumen Pabean & CEISA 4.0](#31-modul-dokumen-pabean--ceisa-40)
   - [3.2 Modul Batch Import Cerdas (F15)](#32-modul-batch-import-cerdas-f15)
   - [3.3 Modul Lompat Tahap Historis (F16)](#33-modul-lompat-tahap-historis-f16)
   - [3.4 Modul Delivery Order (DO) & Pelayaran](#34-modul-delivery-order-do--pelayaran)
   - [3.5 Modul Traffic, Armada & Lapangan](#35-modul-traffic-armada--lapangan)
   - [3.6 Modul HRM, Absensi & Privasi Biometrik (UU PDP)](#36-modul-hrm-absensi--privasi-biometrik-uu-pdp)
   - [3.7 Modul Penggajian (Payroll) & Perpajakan](#37-modul-penggajian-payroll--perpajakan)
   - [3.8 Modul Keuangan (Finance) & Talangan Pabean](#38-modul-keuangan-finance--talangan-pabean)
   - [3.9 Modul Akuntansi (Accounting) & Penguncian Buku](#39-modul-akuntansi-accounting--penguncian-buku)
   - [3.10 Modul Master Data Klien & Master Vendor](#310-modul-master-data-klien--master-vendor)
   - [3.11 Modul Komunikasi Pelanggan: CRM & WhatsApp WABA](#311-modul-komunikasi-pelanggan-crm--whatsapp-waba)
   - [3.12 Modul Antarmuka Pengguna & Pengalaman Visual (UI/UX)](#312-modul-antarmuka-pengguna--pengalaman-visual-uiux)
4. [Persyaratan Non-Fungsional (Non-Functional Requirements - NFR)](#4-persyaratan-non-fungsional-non-functional-requirements---nfr)
5. [Spesifikasi Desain Antarmuka (UI/UX Guidelines)](#5-spesifikasi-desain-antarmuka-uiux-guidelines)
6. [Kriteria Penerimaan Produk & Matriks Definition of Done (DoD)](#6-kriteria-penerimaan-produk--matriks-definition-of-done-dod)

---

## 1. VISI PRODUK & RINGKASAN SOLUSI

**Visi Produk:**  
Menjadikan **GENIE (PATS_CEISA)** sebagai platform *Autonomous Enterprise Logistics & Customs Operating System* paling andal di Indonesia—menghubungkan seluruh rantai operasional forwarder, kepabeanan CEISA 4.0 DJBC, pelayaran, pergudangan TPS, keuangan berpasangan, perpajakan mutakhir, serta portal transparansi klien dalam satu arsitektur mandiri berstandar korporat dunia.

**Ringkasan Solusi v2.0:**  
Sistem menyempurnakan fondasi yang ada dengan menyelesaikan 28 temuan audit teknis:
1. Menghadirkan pemrosesan asinkron berbasis background worker untuk modul Batch Import F15.
2. Memperbaiki otorisasi supervisor pada F16 Lompat Tahap dan eliminasi seluruh bug kode sumber.
3. Menegakkan perlindungan data biometrik karyawan sesuai standar hukum **UU PDP No. 27/2022**.
4. Melengkapi modul penggajian dengan rekonsiliasi PPh 21 tahunan dan Formulir 1721-A1 PDF resmi.
5. Memasang gerbang pengamanan kas operasional (*Pre-Funding Escrow Gate*) sebelum pembayaran billing pabean MPN G3.
6. Menyediakan antarmuka CRUD lengkap untuk Master Klien dan Master Vendor.
7. Menghubungkan modul komunikasi WhatsApp ke Meta Cloud API resmi serta modul email DO pelayaran riil.
8. Meningkatkan ergonomi UI/UX ke standar Apple HIG Bento Grid dengan font tabular monospace dan fitur *Auto-Save Draft*.

---

## 2. MATRIKS PERSONA PENGGUNA & PERAN RESMI (12 ROLES)

Sistem GENIE menerapkan Role-Based Access Control (RBAC) pada 12 peran resmi organisasi:

| Kode Peran | Nama Peran | Departemen / Level | Hak Akses Utama & Fitur yang Digunakan |
| :--- | :--- | :--- | :--- |
| **`R-ADM`** | Administrator Sistem | IT & Infrastructure | Full Access konfigurasi sistem, manajemen user, role permissions matrix, audit logs, CEISA API keys. |
| **`R-OWN`** | Pimpinan (Owner / Direksi) | Executive Management | Dashboard eksekutif, KPI laba rugi, approval tagihan vendor > Rp 10jt, approval diskon quotation > 10%, audit trail. |
| **`R-DOC-SPV`**| Supervisor Dokumen | Customs / PPJK | Review draf PIB/PEB, validasi Pos Tarif HS Code, approval kirim data CEISA 4.0, approval tiket lompat tahap F16. |
| **`R-DOC`** | Staff Dokumen | Customs / PPJK | Pembuatan proyek shipment, drafting PIB BC 2.0 / PEB BC 3.0, OCR dokumen PL/Invoice, request submit CEISA. |
| **`R-DO`** | Staff Delivery Order | Shipping Lines | Monitoring free time demurrage/detention, pengiriman email permintaan rilis DO ke agen pelayaran, input rilis DO. |
| **`R-TRF`** | Staff Traffic | Land Logistics | Monitoring penjaluran pabean (SPPB/Merah), penugasan trucking armada, konfirmasi pengeluaran gerbang Gate Out. |
| **`R-FLD`** | Staff Lapangan | Port Operations | Pemeriksaan fisik behandle TPS pelabuhan via mobile PWA, pengambilan 4-stage foto fisik kontainer & segel baru. |
| **`R-MGR`** | Operations Manager | Operations Lead | Eskalasi SLA pengapalan, reassign PIC kargo darurat, monitoring keterlambatan free time pelayaran. |
| **`R-FIN`** | Finance Officer | Finance & Treasury | Invoicing klien P19, pencatatan vendor bills, validasi escrow talangan, pembayaran billing MPN G3, kas/bank. |
| **`R-HR`** | Human Resources (HRD) | HR & General Affairs | Pengelolaan karyawan, kontrak PKWT, persetujuan cuti/lembur/kasbon, persetujuan koreksi absensi, payroll run. |
| **`R-CS`** | Customer Service | CRM & Communications | Respon percakapan WhatsApp Omnichannel, penanganan komplain klien, informasi posisi kargo. |
| **`R-AM`** | Account Manager | Sales & Commercial | Manajemen prospek klien, perekaman sales visits (GPS 200m), pembuatan Quotation penawaran harga. |

---

## 3. DAFTAR PERSYARATAN FUNGSIONAL PRODUK (FUNCTIONAL REQUIREMENTS)

### 3.1 Modul Dokumen Pabean & CEISA 4.0
* **FR-DOC-01 (Hotfix Rute View Indeks):** Sistem wajib memperbaiki tombol sinkronisasi massal pada `document_index.blade.php:39` mengarah ke rute resmi `config.sync-all` untuk meniadakan error `RouteNotFoundException` (Memenuhi **FIX-01**).
* **FR-DOC-02 (Flat Root Payload Engine):** Service `DocumentBC20Service` dan `DocumentBC30Service` wajib mempertahankan struktur data Flat Root Object sesuai JSON Schema resmi DJBC (Draft-07), menyertakan pos manifes BC 1.1 dan entitas pemilik barang kode '7'.
* **FR-DOC-03 (Auto-Save Draft Formulir Panjang):** Sistem wajib mengimplementasikan mekanisme auto-save draf secara berkala (interval 15 detik dengan status visual tersimpan) pada formulir input BC 2.0 dan BC 3.0 untuk mencegah kehilangan data akibat kendala jaringan (Memenuhi **FIX-23**).
* **FR-DOC-04 (Pencegahan Navigasi Tidak Tersimpan):** Sistem wajib memasang event listener `beforeunload` yang menampilkan dialog konfirmasi jika pengguna mencoba berpindah halaman sebelum form pabean disimpan (Memenuhi **FIX-23**).
* **FR-DOC-05 (Validasi Lartas & BTKI):** Sistem wajib menyediakan modul verifikasi aturan Larangan dan Pembatasan (Lartas) kementerian terkait sebelum data dienkripsi untuk dikirim ke CEISA 4.0.

### 3.2 Modul Batch Import Cerdas (F15)
* **FR-IMP-01 (Pemrosesan Asinkron Background Queue):** Seluruh pemrosesan berkas kompresi ZIP massal, ekstraksi teks, hashing SHA-256, dan OCR wajib dijalankan melalui antrean asinkron Laravel Queue (`app/Jobs/Import/ExtractBatchZipJob.php` dan `ProcessDocumentOcrJob.php`) guna menjamin respons HTTP web server di bawah 3 detik (Memenuhi **FIX-14**).
* **FR-IMP-02 (Proteksi Dekompresi Zip Bomb):** Sistem wajib membatasi rasio kompresi maksimal (uncompressed size max 2GB, depth traversal max 2) untuk mencegah serangan kehabisan memori server.
* **FR-IMP-03 (Split-Screen Review & Bounding Box):** Antarmuka peninjauan hasil ekstraksi wajib menampilkan perbandingan side-by-side 50:50 antara form draf hasil ekstraksi (kiri) dengan penampil PDF interaktif bersorotan kotak koordinat (*bounding box*) (kanan).
* **FR-IMP-04 (Validasi Silang Aturan Pabean V01–V10):** Sistem wajib mengevaluasi 10 aturan validasi kepabeanan otomatis, termasuk verifikasi konsistensi nomor B/L, toleransi bobot koli, dan checksum peti kemas ISO 6346.

### 3.3 Modul Lompat Tahap Historis (F16)
* **FR-JMP-01 (Koreksi Seeder Otorisasi Approval):** File seeder `StagePrerequisiteSeeder.php` wajib menyelaraskan kode peran approver dari `'R-SPV'` menjadi `'R-DOC-SPV'` agar supervisor dokumen memiliki wewenang sah untuk menyetujui pengajuan lompat pos (Memenuhi **FIX-02**).
* **FR-JMP-02 (Isolasi Finansial Data Migrasi):** Setiap kargo yang dimasukkan via mekanisme lompat tahap wajib menyediakan opsi *Opening Balance* (mencatat saldo awal tanpa memicu jurnal historis di periode tertutup).
* **FR-JMP-03 (Pengecualian Metrik SLA):** Tahap yang berlabel `IMPORTED` atau `SKIPPED` wajib dikecualikan secara otomatis dari perhitungan keterlambatan SLA operasional.

### 3.4 Modul Delivery Order (DO) & Pelayaran
* **FR-DO-01 (Pengiriman Email DO Riil):** Method `ShipmentWorkflowService::sendVendorEmailForDo` wajib memanggil mailer resmi `Mail::to()` dengan melampirkan berkas B/L secara otomatis ke alamat email operasional agen pelayaran terkait (Memenuhi **FIX-10**).
* **FR-DO-02 (Predictive Free-Time Demurrage Ticker):** Sistem wajib menjalankan cron harian `CheckDemurrageAlertsCommand` yang menghitung sisa masa bebas kontainer dan memicu notifikasi peringatan eskalasi pada H-3, H-2, dan H-1 ke WhatsApp Manajer Operasional dan Klien (Memenuhi **FIX-20**).
* **FR-DO-03 (Manajemen Dokumen Penebusan DO):** Sistem wajib mencatat nomor kuitansi pelayaran, bukti bayar uang jaminan kontainer, dan tanggal terbit rilis DO resmi.

### 3.5 Modul Traffic, Armada & Lapangan
* **FR-TRF-01 (Pemeriksaan Fisik Behandle 4-Stage):** Sistem wajib menyediakan modul mobile untuk mengunggah 4 tahap foto pemeriksaan fisik behandle di TPS: (1) Tampak Depan Kontainer & Nomor Seri, (2) Kondisi Segel Pelayaran Lama, (3) Pembukaan Pintu & Tumpukan Kargo, (4) Pemasangan Segel Baru Pabean (*Resealing*).
* **FR-TRF-02 (Resiliensi Unggah Foto Offline):** Aplikasi web mobile staf lapangan wajib dilengkapi service worker yang menyimpan foto secara lokal (*IndexedDB*) dan mengunggah ulang secara otomatis saat koneksi internet kembali stabil.
* **FR-TRF-03 (Perekaman Pengeluaran Gate Out & POD):** Sistem wajib mencatat nomor polisi truk pengangkut, identitas pengemudi, tanggal/jam gate out TPS pelabuhan, serta foto bukti serah terima barang (*Proof of Delivery*) di gudang importir.

### 3.6 Modul HRM, Absensi & Privasi Biometrik (UU PDP)
* **FR-HRM-01 (Relokasi Berkas Foto ke Storage Privat):** Seluruh foto presensi kamera wajib dipindahkan dari direktori publik `public/uploads/attendances` ke disk privat `storage/app/private/attendances/` yang hanya dapat diakses melalui signed URL streaming `PrivateMediaController` (Memenuhi **FIX-06**).
* **FR-HRM-02 (Enkripsi Data Biometrik di Database):** Kolom `face_data` pada model `Employee` wajib dideklarasikan dengan cast `'encrypted'` untuk mengenkripsi data wajah menggunakan AES-256 secara native (Memenuhi **FIX-07**).
* **FR-HRM-03 (Penegakan Persetujuan Biometrik Harian):** Sistem wajib memblokir aksi clock-in/out jika karyawan belum menandatangani persetujuan pemrosesan data biometrik aktif (`BiometricConsent`) (Memenuhi **FIX-08**).
* **FR-HRM-04 (Pemusnahan Data Retensi 2 Tahun):** Sistem wajib menjalankan artisan scheduler berkala `biometric:purge-expired` yang menghapus data biometrik karyawan yang telah melewati masa retensi 2 tahun sesuai UU PDP (Memenuhi **FIX-09**).
* **FR-HRM-05 (Geofencing GPS Presisi):** Sistem wajib menghitung jarak radius pegawai terhadap titik koordinat kantor/pelabuhan menggunakan formula sferis Haversine dengan ambang batas radius maksimal yang dapat dikonfigurasi.
* **FR-HRM-06 (Alih Tugas Otomatis PIC Cuti):** Saat pengajuan cuti staf disetujui, seluruh shipment aktif otomatis dialihkan ke *Backup Officer* yang dipilih.

### 3.7 Modul Penggajian (Payroll) & Perpajakan
* **FR-PAY-01 (Presisi Matematis TER PMK 168/2023):** Sistem wajib mengimplementasikan formula Tarif Efektif Rata-Rata Kategori A (44 lapisan), Kategori B (40 lapisan), dan Kategori C (41 lapisan) untuk perhitungan PPh 21 Masa Januari s.d. November.
* **FR-PAY-02 (Rekonsiliasi Pajak Tahunan Masa Desember):** Sistem wajib menghitung PPh 21 Masa Desember berbasis tarif progresif Pasal 17 ayat (1) huruf a UU PPh, mengurangkan biaya jabatan tahunan maksimal Rp 6.000.000, serta mengakumulasi PTKP setahun penuh (Memenuhi **FIX-15**).
* **FR-PAY-03 (Generator Formulir 1721-A1 PDF):** Sistem wajib menyediakan tombol cetak dan pengunduhan massal berkas PDF Formulir Bukti Potong 1721-A1 resmi berstandar Ditjen Pajak (Memenuhi **FIX-15**).
* **FR-PAY-04 (Auto-Journaling Payroll):** Saat pencairan payroll disetujui, sistem wajib otomatis menerbitkan jurnal debit Beban Gaji & Beban BPJS Perusahaan serta kredit Utang Gaji, Utang PPh 21, Utang BPJS, dan Kas/Bank.

### 3.8 Modul Keuangan (Finance) & Talangan Pabean
* **FR-FIN-01 (Invoicing Klien P19 & PPN 1,1%):** Sistem wajib menerbitkan invoice resmi klien dengan memisahkan kolom talangan bea cukai non-pajak dengan pendapatan jasa forwarding yang dikenakan PPN 1,1% sesuai PMK 71/2022.
* **FR-FIN-02 (Pre-Funding Escrow Verification Gate):** Pembayaran billing pabean MPN G3 wajib terkunci secara sistem dan dilarang dieksekusi kecuali dana talangan importir telah berstatus `FUNDS_CLEARED` di bank atau ada approval otorisasi Direktur Utama (Memenuhi **FIX-18**).
* **FR-FIN-03 (Batas Approval Tagihan Vendor > Rp 10 Juta):** Sistem wajib mengunci pembayaran tagihan vendor dengan nominal di atas Rp 10.000.000 dalam status `PENDING_APPROVAL` hingga disetujui oleh Pimpinan (`R-OWN`).
* **FR-FIN-04 (Buku Kas & Rekonsiliasi Bank):** Sistem wajib menyediakan buku kas multi-rekening, pencatatan mutasi operasional, fasilitas pindah buku antar-rekening, dan rekonsiliasi saldo.

### 3.9 Modul Akuntansi (Accounting) & Penguncian Buku
* **FR-ACC-01 (Inisialisasi Variabel Tanggal Akuntansi):** Method `recordClientInvoiceApproved` dan `recordVendorBillApproved` pada `AccountingService.php` wajib menginisialisasi variabel `$entryDate` untuk meniadakan bug fatal *Undefined variable* (Memenuhi **FIX-03**).
* **FR-ACC-02 (Penguncian Periode di Level Database Observer):** Model `JournalEntry` dan `JournalLine` wajib dilindungi oleh Model Observer yang melempar exception pemblokiran mutasi data pada periode akuntansi yang berstatus `is_closed = true` (Memenuhi **FIX-21**).
* **FR-ACC-03 (Jurnal Pembalik Satu-Klik):** Sistem wajib menyediakan fitur pembatalan transaksi dengan tombol *Reverse Entry* yang menerbitkan jurnal pembalik seimbang otomatis.
* **FR-ACC-04 (Automated Test Suite Akuntansi):** Modul Buku Besar, Kas/Bank, dan Lock Period wajib dilengkapi test suite otomatis dengan cakupan pengujian di atas 85% (Memenuhi **FIX-22**).

### 3.10 Modul Master Data Klien & Master Vendor
* **FR-MST-01 (CRUD Lengkap Master Klien):** Sistem wajib menyediakan rute, controller, dan antarmuka untuk Edit Data Klien, Nonaktifkan Klien, Hapus Klien, dan pemantauan masa berlaku Surat Kuasa PPJK (Memenuhi **FIX-16**).
* **FR-MST-02 (Upload Berkas Legalitas Klien):** Master klien wajib menyediakan modal unggah berkas PDF dokumen legalitas: NIB (Nomor Induk Berusaha), NPWP, dan berkas fisik Surat Kuasa PPJK (Memenuhi **FIX-16**).
* **FR-MST-03 (Menu & CRUD Master Vendor):** Sistem wajib menyediakan halaman Menu Master Vendor khusus di admin panel untuk mengelola data agen pelayaran (*Shipping Lines*), vendor trucking armada, nomor rekening bank pembayaran, dan kontak operasional rilis DO (Memenuhi **FIX-17**).

### 3.11 Modul Komunikasi Pelanggan: CRM & WhatsApp WABA
* **FR-CRM-01 (Integrasi Live Meta Cloud WABA):** Sistem wajib menghubungkan controller WhatsApp ke Meta Cloud Graph API v19 resmi untuk pengiriman pesan broadcast, notifikasi milestone kontainer, dan interaksi percakapan (Memenuhi **FIX-12**).
* **FR-CRM-02 (Webhook Receiver Resmi):** Sistem wajib menyediakan endpoint webhook WABA dengan verifikasi signature HMAC-SHA256 untuk menerima pesan masuk pelanggan secara realtime.
* **FR-CRM-03 (Eliminasi Nilai Acak Media Sosial):** Modul media sosial wajib menyingkirkan fungsi acak `rand()` pada metrik performa postingan dan menggantinya dengan analitik riil (Memenuhi **FIX-13**).

### 3.12 Modul Antarmuka Pengguna & Pengalaman Visual (UI/UX)
* **FR-UI-01 (Dekomposisi View Monolitik Dokumen):** View raksasa `document_index.blade.php` (150 KB) dan `document_show.blade.php` (135 KB) wajib didekomposisi menjadi komponen Blade modular dengan pemuatan modal berbasis lazy-loading AJAX guna mempercepat rendering halaman (Memenuhi **FIX-24**).
* **FR-UI-02 (Standardisasi Angka Tabular Monospace):** Seluruh kolom tabel yang memuat data angka pabean (Nomor Aju 26-digit, Nomor Kontainer ISO, HS Code 8-digit, Devisa CIF/FOB, Nilai PPN) wajib menggunakan kelas CSS `font-mono tabular-nums tracking-tight` (Memenuhi **FIX-25**).
* **FR-UI-03 (Eliminasi Nilai Hardcoded Hex):** Seluruh penggunaan hex `#007AFF` dan `#1C1C1E` wajib digantikan dengan token semantik Tailwind resmi Sky Blue (`text-sky-600`, `bg-sky-600`) sesuai panduan desain resmi (Memenuhi **FIX-26**).
* **FR-UI-04 (iOS Native Bottom Sheet pada Mobile):** Tombol menu pada navigasi mobile tab bar wajib memunculkan iOS Native Bottom Sheet Modal dengan backdrop dismiss (Memenuhi **FIX-27**).
* **FR-UI-05 (Proteksi Rate Limiting Portal Publik):** Seluruh rute pelacakan kargo publik `/portal/track/{token}` wajib diproteksi middleware `throttle:60,1` untuk mencegah serangan brute-force (Memenuhi **FIX-28**).

---

## 4. PERSYARATAN NON-FUNGSIONAL (NON-FUNCTIONAL REQUIREMENTS - NFR)

1. **Performa & Responsivitas (NFR-PERF):**
   * *First Contentful Paint (FCP):* Seluruh halaman utama wajib selesai dirender dalam waktu kurang dari **1,2 detik**.
   * *Waktu Eksekusi Background Queue:* Pengunggahan berkas batch ZIP berisi 20 dokumen pabean wajib merespons ke browser dalam waktu $< 2$ detik, sementara proses OCR diselesaikan di antrean antrian dalam waktu $< 60$ detik.
2. **Keamanan & Kepatuhan Data (NFR-SEC):**
   * *Enkripsi Data At-Rest:* Seluruh data biometrik wajah dan berkas sensitif karyawan wajib terenkripsi menggunakan algoritma **AES-256-CBC**.
   * *Otentikasi & Autorisasi:* 100% endpoint mutasi data wajib diproteksi oleh Controller-Level RBAC dan CSRF token.
3. **Ketersediaan & Keandalan (NFR-AVAIL):**
   * Tingkat ketersediaan sistem (*System Uptime*) ditargetkan minimal **99,8%** selama jam kerja operasional logistik pelabuhan (24/7).
4. **Skalabilitas Data (NFR-SCALE):**
   * Basis data MySQL dan arsitektur UUID v4 wajib mampu menangani hingga **500.000 record pengiriman kargo** tanpa penurunan performa indeks kueri.

---

## 5. SPESIFIKASI DESAIN ANTARMUKA (UI/UX GUIDELINES)

Sistem GENIE menerapkan pedoman desain **Apple Human Interface Guidelines (HIG)** dengan spesifikasi:
* **Arsitektur Grid:** Bento Grid modular pada dashboard eksekutif dan kartu ringkasan kargo.
* **Geometri Sudut (Squircle Radius):**
  * Tombol & Input: `rounded-xl` (12px).
  * Kartu Dashboard & Modal: `rounded-2xl` (16–20px).
  * Badge Status & Tag: `rounded-lg` (8px).
* **Palet Warna Resmi:**
  * Primary Accent: **Sky Blue (`#0284c7` / Tailwind `sky-600`)**.
  * Status Hijau (Jalur Hijau / SPPB / Lunas): `emerald-600` (`#059669`).
  * Status Kuning (Jalur Kuning / Hold / Pending): `amber-600` (`#d97706`).
  * Status Merah (Jalur Merah / Reject / Denda): `rose-600` (`#e11d48`).
* **Disiplin Bebas-Slop:**
  * Dilarang menggunakan warna-warni pelangi pada ikon navigasi sidebar.
  * Dilarang menggunakan emoji visual sebagai elemen antarmuka struktural; wajib menggunakan ikon SVG murni (Lucide Icons).

---

## 6. KRITERIA PENERIMAAN PRODUK & MATRIKS DEFINITION OF DONE (DOD)

| Modul / Komponen Target | Kriteria Penerimaan (Acceptance Criteria) | Metode Pengujian & Verifikasi |
| :--- | :--- | :--- |
| **Hotfix Rute View Dokumen** | Halaman `/core/documents` terbuka dengan status HTTP 200 tanpa error `RouteNotFoundException`. | `vendor/bin/phpunit --filter AdminPanelRoutesTest` |
| **Deadlock Seeder F16** | Supervisor dokumen (`R-DOC-SPV`) dapat menyetujui tiket pengajuan lompat tahap. | Uji otorisasi peran via Tinker & Feature test |
| **Kepatuhan Privasi UU PDP** | Foto absensi disimpan di folder privat; URL publik `/uploads/attendances/...` mengembalikan HTTP 404/403. Clock-in ditolak jika consent belum disetujui. | Audit HTTP request publik & pengujian unit consent |
| **Antrean Asinkron F15** | Upload berkas ZIP massal merespons dalam waktu < 2 detik; ekstraksi OCR diproses di antrean background queue tanpa timeout. | Monitor antrean queue `php artisan queue:work` |
| **Email Rilis DO Riil** | Email permintaan rilis DO terkirim ke alamat email vendor pelayaran dengan lampiran berkas B/L resmi. | Pengujian mailer `Mail::fake()` & log mail |
| **Rekonsiliasi Form 1721-A1** | Payroll Masa Desember menghitung PPh 21 tahunan Pasal 17 dan bukti potong Formulir 1721-A1 dapat diunduh dalam format PDF resmi. | Verifikasi perhitungan pajak vs kalkulator resmi DJP |
| **Escrow Gate Talangan** | Tombol pembayaran billing MPN G3 terkunci dan menolak eksekusi jika dana talangan importir belum masuk rekening bank. | Uji coba pembayaran billing dengan saldo kosong |
| **CRUD Master Klien & Vendor**| Tersedia antarmuka lengkap untuk Tambah, Edit, Nonaktifkan, dan Upload berkas legalitas Klien dan Vendor di admin panel. | Pengujian manual UI dan Feature test CRUD |
| **Integritas Lock Period** | Operasi mutasi jurnal atau penagihan invoice pada periode akuntansi tertutup ditolak 100% oleh model observer. | Uji coba penolakan mutasi `assertThrows(DomainException)` |
| **Skor Verifikasi Otomatis** | Seluruh 14 pemeriksaan forensik pada script reproduksi lolos dengan skor 100%. | `powershell scripts\verify_feature_audit.ps1` |

---
*Dokumen Persyaratan Produk (PRD) ini disahkan sebagai cetak biru rekayasa perangkat lunak sistem GENIE ERP v2.0.*  
*PT Agung Trans Solusindo & Cooca.id — All Rights Reserved © 2026.*