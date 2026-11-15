# DOKUMEN MANAJEMEN RISIKO ENTERPRISE & KEPATUHAN REGULASI
## SISTEM ERP LOGISTIK & KEPABEANAN: GENIE (PATS_CEISA)
**PT AGUNG TRANS SOLUSINDO & COOCA.ID**  
*Klasifikasi: Kerangka Manajemen Risiko Korporat, Kepatuhan Hukum, & Ketahanan Operasional*  
*Standar Rujukan: ISO 31000:2018 (Risk Management) & COSO Enterprise Risk Management (ERM)*  
*Tanggal Berlaku: 15 November 2026*  
*Versi Dokumen: 2.0-Risk*  
*Dokumen Terkait: docs/FEATURE_AUDIT_REPORT.md, docs/BRD_GENIE_ERP_v2.0.md, docs/PRD_GENIE_ERP_v2.0.md*

---

## DAFTAR ISI
1. [Prinsip & Kerangka Kerja Manajemen Risiko Korporat](#1-prinsip--kerangka-kerja-manajemen-risiko-korporat)
2. [Matriks Penilaian Risiko Enterprise (Risk Heatmap 5x5)](#2-matriks-penilaian-risiko-enterprise-risk-heatmap-5x5)
3. [Profil & Analisis Mendalam 10 Risiko Utama (Risk Deep Dive)](#3-profil--analisis-mendalam-10-risiko-utama-risk-deep-dive)
   - [3.1 R01: Sanksi Hukum & Pidana Kebocoran Data Biometrik (UU PDP No. 27/2022)](#31-r01-sanksi-hukum--pidana-kebocoran-data-biometrik-uu-pdp-no-272022)
   - [3.2 R02: Sanksi Denda Administrasi Nota Pembetulan Pabean (SPTNP/Notul)](#32-r02-sanksi-denda-administrasi-nota-pembetulan-pabean-sptnpnotul)
   - [3.3 R03: Kerentanan DoS & Crash Server Akibat Ekstraksi Batch Import F15](#33-r03-kerentanan-dos--crash-server-akibat-ekstraksi-batch-import-f15)
   - [3.4 R04: Gagal Bayar Dana Talangan Pabean Miliaran Rupiah (Reimbursable Default)](#34-r04-gagal-bayar-dana-talangan-pabean-miliaran-rupiah-reimbursable-default)
   - [3.5 R05: Kecurangan Pengeluaran Kas Kecil Lapangan (Petty Cash Fraud)](#35-r05-kecurangan-pengeluaran-kas-kecil-lapangan-petty-cash-fraud)
   - [3.6 R06: Deadlock Alur Operasional Lompat Tahap F16 (Role Naming Mismatch)](#36-r06-deadlock-alur-operasional-lompat-tahap-f16-role-naming-mismatch)
   - [3.7 R07: Kerugian Biaya Demurrage & Detention Kontainer Pelayaran](#37-r07-kerugian-biaya-demurrage--detention-kontainer-pelayaran)
   - [3.8 R08: Manipulasi Laporan Keuangan pada Periode Akuntansi Tertutup](#38-r08-manipulasi-laporan-keuangan-pada-periode-akuntansi-tertutup)
   - [3.9 R09: Ketergantungan Tunggal Personel Kargo (Single Point of Failure - SPOF)](#39-r09-ketergantungan-tunggal-personel-kargo-single-point-of-failure---spof)
   - [3.10 R10: Eksploitasi Enumerasi Token Publik Pelacakan Kargo Klien](#310-r10-eksploitasi-enumerasi-token-publik-pelacakan-kargo-klien)
4. [Rencana Mitigasi & Pengendalian Risiko (Risk Treatment & Action Plans)](#4-rencana-mitigasi--pengendalian-risiko-risk-treatment--action-plans)
5. [Protokol Tanggap Darurat & Eskalasi Insiden (Incident Response Protocol)](#5-protokol-tanggap-darurat--eskalasi-insiden-incident-response-protocol)
6. [Tata Kelola Audit Berkala & Pelaporan Direksi (Governance & Monitoring)](#6-tata-kelola-audit-berkala--pelaporan-direksi-governance--monitoring)

---

## 1. PRINSIP & KERANGKA KERJA MANAJEMEN RISIKO KORPORAT

Sebagai perusahaan Forwarder dan PPJK independen, **PT Agung Trans Solusindo** memproses ribuan dokumen pabean, perputaran dana talangan miliaran rupiah, dan pertukaran peti kemas lintas pelabuhan setiap bulannya. Penerapan kerangka manajemen risiko berbasis **ISO 31000:2018** bertujuan untuk:
1. Melindungi aset finansial dan likuiditas kas operasional perusahaan.
2. Memastikan kepatuhan penuh terhadap hukum perundang-undangan Republik Indonesia (UU Kepabeanan, UU Perpajakan, dan UU Pelindungan Data Pribadi).
3. Mencegah gangguan ketersediaan layanan (*business continuity*) akibat kendala teknis atau serangan siber.
4. Menjaga reputasi dan integritas perusahaan di mata Ditjen Bea Cukai (DJBC), maskapai pelayaran, perbankan, dan mitra importir/eksportir.

---

## 2. MATRIKS PENILAIAN RISIKO ENTERPRISE (RISK HEATMAP 5x5)

Penilaian risiko dikuantifikasi melalui perkalian antara **Tingkat Probabilitas (*Likelihood* 1–5)** dengan **Tingkat Dampak (*Severity* 1–5)**:
* **Skor 15–25 (EXTREME):** Membutuhkan tindakan mitigasi darurat dan intervensi langsung Direksi.
* **Skor 8–14 (HIGH):** Membutuhkan kontrol preventif ketat dan pengawasan mingguan level Manajer.
* **Skor 4–7 (MEDIUM):** Dikelola melalui prosedur standar operasional (SOP) dan pemantauan berkala.
* **Skor 1–3 (LOW):** Risiko dapat diterima (*acceptable*) dengan pencatatan log rutin.

```
TINGKAT DAMPAK (SEVERITY)
  ▲
5 │                     [R01:UU PDP]   [R03:Queue Crash]
4 │      [R05:Fraud Lapangan]          [R02:Denda Notul]   [R04:Talangan Pabean]
3 │                     [R07:Demurrage][R06:Deadlock F16]
2 │      [R09:SPOF Personel]           [R08:Bocor Lock]
1 │                                    [R10:Bebas Rate Limit]
  └─────────────────────────────────────────────────────────────►
     1 (Sangat Jarang)  2 (Jarang)     3 (Sedang)    4 (Sering)    5 (Sangat Sering)
                           PROBABILITAS (LIKELIHOOD)
```

| ID Risiko | Kategori Risiko | Deskripsi Singkat Risiko | Probabilitas (1–5) | Dampak (1–5) | Skor Risiko | Tingkat Risiko |
| :---: | :--- | :--- | :---: | :---: | :---: | :---: |
| **R01** | **Hukum / Kepatuhan** | Kebocoran foto biometrik absensi di webroot publik (Sanksi UU PDP 27/2022). | 3 | 5 | **15** | **EXTREME** |
| **R02** | **Kepatuhan Pabean** | Kesalahan Pos Tarif HS Code memicu SPTNP / Denda Notul 100%–1000% selisih BM. | 4 | 4 | **16** | **EXTREME** |
| **R03** | **Stabilitas Sistem** | Crash memori & HTTP 504 Timeout akibat Batch Import F15 tanpa background worker. | 4 | 5 | **20** | **EXTREME** |
| **R04** | **Finansial / Likuiditas** | Pembayaran billing pabean MPN G3 tanpa jaminan dana talangan klien (*Default*). | 5 | 4 | **20** | **EXTREME** |
| **R05** | **Kecurangan (Fraud)** | Penggelembungan kwitansi petty cash operasional trattic/behandle lapangan. | 2 | 4 | **8** | **HIGH** |
| **R06** | **Operasional** | Deadlock persetujuan lompat tahap F16 akibat salah kode role seeder (`R-SPV`). | 3 | 3 | **9** | **HIGH** |
| **R07** | **Operasional Pelayaran** | Denda Demurrage & Detention kontainer akibat email DO pelayaran berupa mock stub. | 3 | 3 | **9** | **HIGH** |
| **R08** | **Integritas Finansial** | Mutasi jurnal pembukuan pada periode akuntansi tertutup akibat celah event DB. | 3 | 2 | **6** | **MEDIUM** |
| **R09** | **Operasional SDM** | Proyek shipment terbengkalai saat staf dokumen cuti/sakit mendadak (*SPOF*). | 1 | 2 | **2** | **LOW** |
| **R10** | **Keamanan Siber** | Brute-force token UUID pelacakan kargo publik akibat ketiadaan rate limiter. | 3 | 1 | **3** | **LOW** |

---

## 3. PROFIL & ANALISIS MENDALAM 10 RISIKO UTAMA (RISK DEEP DIVE)

### 3.1 R01: Sanksi Hukum & Pidana Kebocoran Data Biometrik (UU PDP No. 27/2022)
* **Kategori Risiko:** Kepatuhan Hukum & Pidana Korporat (Skor: **15 - EXTREME**).
* **Fakta Temuan Forensik:** `StaffPortalController.php` baris 296 dan 380 menulis foto wajah selfie karyawan langsung ke folder publik `public/uploads/attendances/` tanpa enkripsi. Foto tersebut dapat diakses oleh publik internet melalui browser tanpa login. Selain itu, form clock-in harian portal staf mengabaikan validasi status persetujuan (*consent*).
* **Eksposur Hukum & Finansial:**
  - **Pasal 67 ayat (2) UU PDP:** Setiap orang yang sengaja dan melawan hukum mengungkapkan Data Pribadi Spesifik dipidana penjara maksimal **4 tahun** dan/atau denda maksimal **Rp 4 Miliar**.
  - **Pasal 68:** Memperoleh atau menggunakan data pribadi secara melawan hukum dipidana penjara maksimal **5 tahun** dan/atau denda maksimal **Rp 5 Miliar**.
  - Bagi korporasi, sanksi administratif dapat mencapai denda **2% dari total pendapatan tahunan** atau pembekuan izin usaha perusahaan.

### 3.2 R02: Sanksi Denda Administrasi Nota Pembetulan Pabean (SPTNP/Notul)
* **Kategori Risiko:** Kepatuhan Regulasi Pabean & Kewajiban Finansial (Skor: **16 - EXTREME**).
* **Fakta Temuan Forensik:** Klasifikasi barang impor yang rumit (kimia, elektronik, mesin industri) rentan salah penetapan 8-digit Pos Tarif HS Code BTKI.
* **Eksposur Hukum & Finansial:**
  - Sesuai **Pasal 82 ayat (5) UU No. 17 Tahun 2006 tentang Kepabeanan**, importir/PPJK yang salah memberitahukan jenis/nilai barang yang mengakibatkan kekurangan pembayaran Bea Masuk dikenakan denda administrasi sebesar **minimal 100% hingga maksimal 1000%** dari Bea Masuk yang kurang dibayar.
  - Sesuai regulasi kepabeanan, PPJK memikul tanggung jawab tanggung renteng jika importir pailit atau menolak membayar tagihan SPTNP, berujung pada pemblokiran Nomor Pokok PPJK oleh Ditjen Bea Cukai.

### 3.3 R03: Kerentanan DoS & Crash Server Akibat Ekstraksi Batch Import F15
* **Kategori Risiko:** Ketersediaan Sistem & Keamanan Siber (Skor: **20 - EXTREME**).
* **Fakta Temuan Forensik:** Repositori tidak memiliki direktori `app/Jobs`. Proses ekstraksi ZIP massal dan OCR dokumen pabean berjalan 100% sinkron di dalam satu thread HTTP web browser.
* **Eksposur Operasional:**
  - Pengunggahan berkas batch berisi 20–50 dokumen PDF atau berkas ZIP berukuran besar akan langsung menghabiskan memori PHP dan batas waktu eksekusi web server (`max_execution_time`), memicu **HTTP 504 Gateway Timeout**. Seluruh staf kantor yang sedang mengakses sistem akan mengalami *Denial of Service* (sistem tidak dapat diakses).

### 3.4 R04: Gagal Bayar Dana Talangan Pabean Miliaran Rupiah (Reimbursable Default)
* **Kategori Risiko:** Likuiditas Finansial & Risiko Kredit Klien (Skor: **20 - EXTREME**).
* **Fakta Temuan Forensik:** Modul Finance saat ini mengizinkan pencairan pembayaran billing pabean MPN G3 tanpa melakukan validasi apakah dana talangan dari importir telah disetorkan dan masuk (*cleared*) ke rekening bank perusahaan.
* **Eksposur Finansial:**
  - Nilai tagihan billing pabean (Bea Masuk, PPN Impor 11%, PPh Pasal 22) seringkali mencapai ratusan juta hingga miliaran rupiah per proyek. Jika importir mengalami gagal bayar (*default*) atau sengketa komersial setelah billing dibayarkan, kas operasional PT Agung Trans Solusindo langsung mengalami krisis likuiditas parah.

### 3.5 R05: Kecurangan Pengeluaran Kas Kecil Lapangan (Petty Cash Fraud)
* **Kategori Risiko:** Kecurangan Internal & Kebocoran Biaya (Skor: **8 - HIGH**).
* **Fakta Temuan Forensik:** Pencatatan biaya operasional lapangan di dermaga (biaya buruh kuli TPS, sewa forklift penanganan behandle, timbangan kontainer) pada `TrafficWorkflowController::storeOperationCost` diinput manual tanpa validasi geotag foto kuitansi digital.
* **Eksposur Finansial:**
  - Membuka celah *moral hazard* berupa penggelembungan (*mark-up*) biaya lapangan atau pembuatan kuitansi fiktif yang mengikis margin laba kargo forwarder.

### 3.6 R06: Deadlock Alur Operasional Lompat Tahap F16 (Role Naming Mismatch)
* **Kategori Risiko:** Kelancaran Alur Kerja Operasional (Skor: **9 - HIGH**).
* **Fakta Temuan Forensik:** `StagePrerequisiteSeeder.php` baris 39 dan 48 menetapkan syarat persetujuan lompat tahap kepabeanan ke `'approver_role' => 'R-SPV'`. Namun, kode peran resmi supervisor di sistem adalah `'R-DOC-SPV'`.
* **Eksposur Operasional:**
  - Pengajuan lompat tahap oleh staf dokumen menggantung tanpa bisa disetujui oleh supervisor pabean (*deadlock*), menghentikan proses pengeluaran kontainer di lapangan kecuali ada intervensi manual dari Administrator.

### 3.7 R07: Kerugian Biaya Demurrage & Detention Kontainer Pelayaran
* **Kategori Risiko:** Efisiensi Logistik & Sanksi Denda Vendor (Skor: **9 - HIGH**).
* **Fakta Temuan Forensik:** Method pengiriman email permohonan DO ke pelayaran (`ShipmentWorkflowService::sendVendorEmailForDo`) berstatus mock stub (email tidak pernah terkirim ke pelayaran). Selain itu, belum ada sistem alarm peringatan otomatis saat masa bebas kontainer (*free time*) mendekati kedaluwarsa.
* **Eksposur Finansial:**
  - Maskapai pelayaran mengenakan denda penahanan kontainer rata-rata **USD 50 s.d. USD 150 per kontainer per hari**. Keterlambatan penebusan DO selama 5 hari pada pengapalan 10 kontainer memicu kerugian hingga **USD 7.500 (~Rp 118 Juta)**.

### 3.8 R08: Manipulasi Laporan Keuangan pada Periode Akuntansi Tertutup
* **Kategori Risiko:** Integritas Pelaporan Keuangan & Auditabilitas (Skor: **6 - MEDIUM**).
* **Fakta Temuan Forensik:** Penguncian periode akuntansi bulanan (*Lock Period*) belum diproteksi di level database model event, dan `ClientInvoiceController` menelan (*swallow*) exception saat posting jurnal gagal di periode terkunci.
* **Eksposur Finansial:**
  - Jurnal transaksi masa lampau yang telah dilaporkan ke kantor pajak dapat diubah atau dihapus secara diam-diam melalui baris kode program, memicu selisih neraca saldo dan kegagalan audit akuntan publik (*adverse audit opinion*).

### 3.9 R09: Ketergantungan Tunggal Personel Kargo (Single Point of Failure - SPOF)
* **Kategori Risiko:** Operasional SDM & Kepatuhan SLA (Skor: **2 - LOW**).
* **Fakta Analisis:** Kargo forwarder seringkali dipersonalisasi oleh 1 orang staf dokumen. Jika staf tersebut sakit atau cuti tanpa pendelegasian, kargo terancam tertahan di pelabuhan. Sistem GENIE telah memitigasi risiko ini secara baik melalui fitur `reassignPic` pada pengajuan cuti.

### 3.10 R10: Eksploitasi Enumerasi Token Publik Pelacakan Kargo Klien
* **Kategori Risiko:** Kerahasiaan Data Komersial & Siber (Skor: **3 - LOW**).
* **Fakta Temuan Forensik:** Rute publik `/portal/track/{token}` belum dilindungi middleware pembatas laju akses (`throttle`).
* **Eksposur Bisnis:**
  - Pihak kompetitor atau penyerang dapat menjalankan skrip otomatis *dictionary attack* untuk menebak token dan mencuri data rahasia manifes kargo importir bernilai tinggi.

---

## 4. RENCANA MITIGASI & PENGENDALIAN RISIKO (RISK TREATMENT & ACTION PLANS)

| ID Risiko | Penanggung Jawab (Owner) | Pengendalian Preventif (Preventive Control) | Pengendalian Detektif (Detective Control) | Pengendalian Korektif (Corrective Control) |
| :---: | :--- | :--- | :--- | :--- |
| **R01** | IT Lead (`R-ADM`) & HRD (`R-HR`) | • Pindahkan foto ke storage privat terenkripsi.<br>• Wajibkan persetujuan Biometric Consent sebelum clock-in. | Audit log akses berkas foto via signed URL `PrivateMediaController`. | Hapus seketika berkas foto yang terekspos di direktori publik; jalankan scheduler auto-purge 2 tahun. |
| **R02** | Supervisor Dokumen (`R-DOC-SPV`) | • Wajibkan verifikasi ganda (*Four-Eyes Principle*) draf pabean.<br>• Gunakan AI klasifikasi BTKI 2022. | Pengecekan pra-kirim CEISA 4.0 skema `/document/check`. | Pengajuan sanggahan resmi/keberatan pabean terotomasi jika SPTNP terbit keliru. |
| **R03** | IT Lead (`R-ADM`) | • Bangun antrean asinkron Laravel Queue (`app/Jobs`).<br>• Pasang limit kompresi ZIP max 2GB uncompressed. | Monitoring metrik antrean Redis & utilisasi CPU server. | *Job Retry Mechanism* (3 kali percobaan dengan interval eksponensial). |
| **R04** | Finance Lead (`R-FIN`) & Owner (`R-OWN`) | • Pasang **Pre-Funding Escrow Verification Gate** di controller.<br>• Blokir pembayaran billing jika dana talangan kosong. | Rekonsiliasi harian mutasi rekening koran bank vs saldo talangan klien. | Otorisasi khusus tertulis Direktur Utama untuk klien korporasi berperingkat kredit prima. |
| **R05** | Traffic Lead (`R-TRF`) & Finance | • Wajibkan unggah foto nota digital ber-geotag GPS & timestamp.<br>• Tetapkan plafon petty cash per shipment. | Sampling audit fisik nota lapangan oleh auditor internal keuangan. | Penolakan klaim reimbursable dan pemotongan kasbon staf jika nota tidak valid. |
| **R06** | IT Lead (`R-ADM`) | • Perbaiki seeder `StagePrerequisiteSeeder` menjadi `'R-DOC-SPV'`. | Test suite integrasi verifikasi role permission Matrix. | Reset seeder via `php artisan db:seed --class=StagePrerequisiteSeeder`. |
| **R07** | Tim DO (`R-DO`) & Operations Manager | • Implementasikan mailer SMTP riil `Mail::to()` dengan lampiran B/L.<br>• Aktifkan cron peringatan free time H-3/H-1. | Dashboard radar masa bebas kontainer dengan indikator warna berkedip. | Eskalasi darurat ke importir untuk penebusan DO kilat via transfer instant. |
| **R08** | Accounting Lead (`R-FIN`) | • Pasang model observer pada `JournalEntry` dan `JournalLine` penolak mutasi periode terkunci. | Pengecekan neraca saldo berimbang dan selisih rekonsiliasi kas nol. | Fitur *Reverse Entry* resmi bertanggal periode berjalan untuk koreksi jurnal lampau. |
| **R09** | HRD (`R-HR`) & Operations Manager | • Alih tugas otomatis PIC kargo forwarder saat cuti disetujui. | Notifikasi email/WhatsApp ke Backup Officer kargo. | Pengambilalihan manual darurat (*Take-Over PIC*) oleh Manager Operasional. |
| **R10** | IT Lead (`R-ADM`) | • Pasang middleware `throttle:60,1` pada rute grup portal publik. | Monitoring log akses server Nginx/Apache untuk mendeteksi IP mencurigakan. | Blokir otomatis IP address penyerang (*Fail2Ban / Cloudflare Rule*). |

---

## 5. PROTOKOL TANGGAP DARURAT & ESKALASI INSIDEN (INCIDENT RESPONSE PROTOCOL)

```
┌────────────────────────────────────────────────────────────────────────┐
│               ALUR ESKALASI INSIDEN OPERASIONAL & RISIKO               │
├────────────────────┬───────────────────────────────────┬───────────────┤
│ TINGKAT SEVERITY   │ WAKTU TANGGAP MAKSIMAL (SLA)      │ PIHAK TERLIBAT│
├────────────────────┼───────────────────────────────────┼───────────────┤
│ LEVEL 1 (CRITICAL) │ < 15 Menit                        │ Direktur Utama│
│ Kebocoran UU PDP,  │ (Server Down, Lapor Pimpinan,     │ Manager Ops   │
│ Crash Batch Import,│ Isolasi Jaringan)                 │ IT Lead       │
│ Gagal Bayar Talangan                                                   │
├────────────────────┼───────────────────────────────────┼───────────────┤
│ LEVEL 2 (MAJOR)    │ < 1 Jam                           │ Manager Ops   │
│ SPTNP Pabean,      │ (Investigasi Akar Masalah,        │ Lead Terkait  │
│ Deadlock F16,      │ Alternatif Jalur Manual)          │ Staf Terkait  │
│ Free Time Kritis H-1                                                   │
├────────────────────┼───────────────────────────────────┼───────────────┤
│ LEVEL 3 (MINOR)    │ < 4 Jam                           │ Supervisor    │
│ Bug Tampilan UI,   │ (Pencatatan Tiket, Hotfix Sprint) │ Staf Dokumen  │
│ Format Font Angka                                                      │
└────────────────────┴───────────────────────────────────┴───────────────┘
```

---

## 6. TATA KELOLA AUDIT BERKALA & PELAPORAN DIREKSI (GOVERNANCE & MONITORING)

1. **Rapat Tinjauan Risiko Mingguan (Weekly Risk Review):**
   * Manajer Operasional dan Manajer Keuangan wajib memaparkan laporan status kargo berisiko tinggi (kargo tertahan di pelabuhan $> 5$ hari, tagihan piutang talangan macet, status notul pabean) kepada Pimpinan setiap hari Senin pukul 09.00 WIB.
2. **Audit Forensik Bulanan (Monthly Forensic Audit):**
   * Tim IT mengeksekusi skrip verifikasi otomatis `scripts/verify_feature_audit.ps1` dan memeriksa tabel `audit_logs` untuk memastikan tidak ada anomali manipulasi data atau akses tidak sah.
3. **Penyelarasan Regulasi Tahunan (Annual Regulatory Alignment):**
   * Meninjau ulang tabel tarif pajak TER PMK 168, batas upah iuran BPJS terbaru, serta pembaruan Buku Tarif Kepabeanan Indonesia (BTKI) Ditjen Bea Cukai.

---
*Dokumen Manajemen Risiko Enterprise ini disahkan sebagai pedoman tata kelola dan perlindungan hukum bagi PT Agung Trans Solusindo.*  
*PT Agung Trans Solusindo & Cooca.id — All Rights Reserved © 2026.*