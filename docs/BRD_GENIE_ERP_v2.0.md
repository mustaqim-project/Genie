# DOKUMEN KEBUTUHAN BISNIS (BUSINESS REQUIREMENT DOCUMENT - BRD)
## SISTEM ERP LOGISTIK & KEPABEANAN EKSPOR-IMPOR: GENIE (PATS_CEISA) v2.0
**PT AGUNG TRANS SOLUSINDO & COOCA.ID**  
*Klasifikasi: Dokumen Strategis Bisnis & Kebutuhan Operasional Enterprise*  
*Versi: 2.0-Enterprise*  
*Tanggal Rilis: 15 November 2026*  
*Dokumen Rujukan: docs/FEATURE_AUDIT_REPORT.md, docs/PRD_PATS_CEISA.md, Surat Penawaran No. 001/QT/COOCA/X/2026*

---

## DAFTAR ISI
1. [Latar Belakang & Pernyataan Masalah Bisnis](#1-latar-belakang--pernyataan-masalah-bisnis)
2. [Tujuan Bisnis & Sasaran Strategis (Business Objectives)](#2-tujuan-bisnis--sasaran-strategis-business-objectives)
3. [Ruang Lingkup Proyek (Scope of Work)](#3-ruang-lingkup-proyek-scope-of-work)
4. [Analisis Pemangku Kepentingan (Stakeholder Analysis)](#4-analisis-pemangku-kepentingan-stakeholder-analysis)
5. [Daftar Aturan Bisnis Inti (Core Business Rules BR-01 s.d. BR-20)](#5-daftar-aturan-bisnis-inti-core-business-rules-br-01-sd-br-20)
6. [Kebutuhan Bisnis per Domain Operasional](#6-kebutuhan-bisnis-per-domain-operasional)
   - [6.1 Domain Kepabeanan & CEISA 4.0](#61-domain-kepabeanan--ceisa-40)
   - [6.2 Domain Delivery Order & Penanganan Kontainer](#62-domain-delivery-order--penanganan-kontainer)
   - [6.3 Domain Traffic, Lapangan & Pelabuhan](#63-domain-traffic-lapangan--pelabuhan)
   - [6.4 Domain SDM, Presensi Biometrik & UU PDP No. 27/2022](#64-domain-sdm-presensi-biometrik--uu-pdp-no-272022)
   - [6.5 Domain Penggajian (Payroll) & Perpajakan Karyawan](#65-domain-penggajian-payroll--perpajakan-karyawan)
   - [6.6 Domain Keuangan (Finance) & Manajemen Talangan Pabean](#66-domain-keuangan-finance--manajemen-talangan-pabean)
   - [6.7 Domain Akuntansi (Accounting) & Kepatuhan Pembukuan](#67-domain-akuntansi-accounting--kepatuhan-pembukuan)
   - [6.8 Domain Komersial, CRM & Master Entitas Bisnis](#68-domain-komersial-crm--master-entitas-bisnis)
7. [Metrik Keberhasilan & Indikator Kinerja Utama (KPIs)](#7-metrik-keberhasilan--indikator-kinerja-utama-kpis)
8. [Batasan Bisnis, Asumsi & Ketergantungan Eksternal](#8-batasan-bisnis-asumsi--ketergantungan-eksternal)

---

## 1. LATAR BELAKANG & PERNYATAAN MASALAH BISNIS

### 1.1 Latar Belakang Perusahaan
**PT Agung Trans Solusindo** adalah perusahaan nasional yang bergerak di bidang Pengurusan Jasa Kepabeanan (PPJK), Freight Forwarding internasional (ekspor dan impor), pergudangan, dan armada trucking darat. Dengan volume pengurusan peti kemas impor/ekspor yang tinggi di pelabuhan utama (Tanjung Priok Jakarta, Tanjung Perak Surabaya, Tanjung Emas Semarang), operasional bisnis sangat bergantung pada kecepatan, akurasi dokumen pabean, dan koordinasi lintas departemen.

### 1.2 Pernyataan Masalah Bisnis (Problem Statements)
Berdasarkan hasil audit sistem pada `docs/FEATURE_AUDIT_REPORT.md`, operasional perusahaan saat ini menghadapi tantangan kritis:
1. **Risiko Kerugian Finansial Akibat Denda Demurrage & Storage:** Ketiadaan sistem peringatan dini otomatis (*predictive alert*) untuk masa bebas kontainer (*free time*) mengakibatkan keterlambatan penebusan DO dan pengeluaran kargo, memicu denda demurrage rata-rata USD 50–150 per kontainer per hari.
2. **Risiko Hukum & Pidana UU Pelindungan Data Pribadi (UU No. 27/2022):** Penyimpanan foto biometrik absensi di direktori terbuka internet tanpa enkripsi mengekspos direksi dan korporasi pada ancaman sanksi pidana 5 tahun dan denda maksimal 2% omzet tahunan.
3. **Risiko Sanksi Denda Notul Pabean (SPTNP):** Kesalahan klasifikasi Pos Tarif HS Code dapat memicu sanksi denda administrasi Pasal 82 UU Kepabeanan sebesar 100%–1000% selisih Bea Masuk, di mana PPJK memikul tanggung jawab tanggung renteng jika importir wanprestasi.
4. **Risiko Gagal Bayar Talangan Pabean (Reimbursable Funds):** Pembayaran billing pabean MPN G3 bernilai miliaran rupiah sebelum dana talangan dari importir terverifikasi masuk ke rekening perusahaan mengancam likuiditas kas operasional.
5. **Inefisiensi Operasional Akibat Fitur Mock & Inkompatibilitas:** Tahap pengiriman email DO ke agen pelayaran (P09) dan WhatsApp CRM masih berupa simulasi; impor massal F15 rentan timeout karena belum memiliki antrean asinkron; serta master data Klien dan Vendor belum memiliki antarmuka kelola lengkap.

---

## 2. TUJUAN BISNIS & SASARAN STRATEGIS (BUSINESS OBJECTIVES)

Penyempurnaan sistem **GENIE ERP v2.0** bertujuan untuk:
1. **Zero Demurrage Penalty:** Mengurangi insiden keterlambatan penanganan kargo hingga mendekati 0% melalui mesin deteksi free time otomatis berjenjang (H-3, H-2, H-1).
2. **100% Kepatuhan Hukum & Regulasi:**
   - Menjamin kepatuhan mutlak terhadap **UU PDP No. 27/2022** dalam pemrosesan data biometrik karyawan.
   - Menjamin kepatuhan perpajakan **TER PMK 168/2023**, PPh 21 Masa Desember (Form 1721-A1), dan PPN Jasa Forwarder 1,1% **PMK 71/2022**.
   - Menjamin kepatuhan standar format data **Open API CEISA 4.0 DJBC (Flat Root)**.
3. **Perlindungan Kas Operasional (Cash Flow Protection):** Menutup celah pengeluaran talangan pabean tanpa jaminan dana masuk melalui *Pre-Funding Escrow Verification Gate*.
4. **Peningkatan Produktivitas Staf Sebesar 300%:** Mengotomasi pemrosesan dokumen massal melalui modul F15 asinkron dan asistensi AI klasifikasi pos tarif terpercaya.
5. **Transparansi Layanan Klien Kelas Enterprise:** Menyediakan portal mandiri kargo real-time dan notifikasi WhatsApp resmi langsung ke importir/eksportir.

---

## 3. RUANG LINGKUP PROYEK (SCOPE OF WORK)

```
┌────────────────────────────────────────────────────────────────────────┐
│                        RUANG LINGKUP BISNIS v2.0                       │
├────────────────────────────┬───────────────────────────────────────────┤
│ DALAM LINGKUP (IN-SCOPE)   │ 1. Core P01-P19 & CEISA 4.0 Host-to-Host  │
│                            │ 2. F15 Batch Import Asinkron (Queue)      │
│                            │ 3. F16 Lompat Tahap & Approval Role SPV   │
│                            │ 4. Otomasi DO & Email Pelayaran Riil      │
│                            │ 5. HRM Geofencing & Biometrik UU PDP      │
│                            │ 6. Payroll TER & Rekonsiliasi 1721-A1     │
│                            │ 7. Finance Invoicing, AP & Escrow Gate    │
│                            │ 8. Akuntansi Double-Entry & Lock Period   │
│                            │ 9. Master Klien & Master Vendor Lengkap   │
│                            │ 10. WhatsApp Meta WABA Live & MCP Server  │
├────────────────────────────┼───────────────────────────────────────────┤
│ LUAR LINGKUP (OUT-OF-SCOPE)│ • ERP Manufaktur / Modul Pabrikasi Barang │
│                            │ • Pengurusan Kargo Udara (Air Freight AWB)│
│                            │ • Integrasi Core Banking Mandiri API Direct│
│                            │   (Fase 2.0 fokus pada rekonsiliasi CSV)  │
└────────────────────────────┴───────────────────────────────────────────┘
```

---

## 4. ANALISIS PEMANGKU KEPENTINGAN (STAKEHOLDER ANALYSIS)

| Peran Pemangku Kepentingan | Perwakilan / Departemen | Kebutuhan Bisnis Utama | Ekspektasi terhadap GENIE v2.0 |
| :--- | :--- | :--- | :--- |
| **Pimpinan / Owner (`R-OWN`)** | Direktur Utama (Bpk. Soehartono) | Kontrol finansial, mitigasi risiko sanksi hukum, transparansi KPI, dan approval tagihan besar. | Dashboard eksekutif real-time, approval gate utang vendor > Rp 10jt, laporan laba rugi shipment. |
| **Supervisor Dokumen (`R-DOC-SPV`)** | Tim Pabean (Customs Lead) | Validasi kebenaran HS Code, kalkulasi pungutan pabean, persetujuan pengiriman CEISA. | Fitur rekomendasi AI akurat, verifikasi draf split-screen, eliminasi deadlock persetujuan lompat tahap. |
| **Staff Dokumen (`R-DOC`)** | Customs Operators | Kecepatan pembuatan PIB/PEB, pembacaan Packing List/Invoice otomatis. | Zero data loss (auto-save draft), batch import ZIP cepat tanpa crash, shortcut keyboard. |
| **Staff Delivery Order (`R-DO`)** | Shipping Line Liaison | Pemantauan masa bebas kontainer, pengiriman permintaan DO ke pelayaran. | Peringatan dini demurrage, pengiriman email otomatis ke agen pelayaran dengan lampiran B/L. |
| **Staff Traffic & Lapangan (`R-FLD`, `R-TRF`)** | Port Ops & Trucking | Koordinasi pemeriksaan behandle di TPS pelabuhan, konfirmasi pengeluaran Gate Out, POD. | Antarmuka mobile outdoor kontras tinggi, upload foto behandle stabil di sinyal rendah. |
| **Finance & Kasir (`R-FIN`)** | Departemen Keuangan | Penagihan invoice klien (P19), pengawasan umur piutang/hutang, verifikasi talangan pabean. | Escrow gate talangan sebelum bayar billing, aging AP/AR otomatis, ekspor e-Faktur PPN 1.1%. |
| **Akuntan (`R-FIN`, External Auditor)**| Departemen Pembukuan | Integritas laporan keuangan, pembukuan jurnal otomatis, penguncian buku periode. | Lock period yang tidak bisa ditembus, buku besar seimbang, reversal entry 1-klik. |
| **HRD & Personalia (`R-HR`)** | Divisi HR & GA | Manajemen absensi geofence, payroll akurat sesuai pajak terbaru, kepatuhan data pribadi. | Perhitungan TER PMK 168 otomatis, Form 1721-A1 siap cetak, perlindungan data biometrik UU PDP. |
| **Klien Importir / Eksportir** | Klien B2B Eksternal | Transparansi posisi kargo, kejelasan rincian biaya invoice, pelacakan mandiri. | Portal pelacakan tanpa login yang aman, notifikasi milestone kargo via WhatsApp resmi. |

---

## 5. DAFTAR ATURAN BISNIS INTI (CORE BUSINESS RULES BR-01 s.d. BR-20)

* **BR-01 (Ambang Batas Otorisasi Utang Vendor):** Setiap tagihan vendor (*Vendor Bill*) dengan nilai nominal **di atas Rp 10.000.000** wajib berstatus `PENDING_APPROVAL` dan hanya dapat dicairkan setelah disetujui secara digital oleh Pimpinan (`R-OWN`).
* **BR-02 (Ambang Batas Otorisasi Diskon Penawaran):** Penawaran harga (*Quotation*) dengan diskon melebihi **10%** dari tarif dasar forwarder wajib disetujui oleh Pimpinan sebelum dapat dikirimkan ke klien.
* **BR-03 (Masa Berlaku Surat Kuasa PPJK):** Klien dilarang dibuatkan penawaran harga atau proyek shipment jika sisa masa berlaku Surat Kuasa Kepabeanan kurang dari **30 hari kalender**.
* **BR-04 (Prinsip Four-Eyes Klasifikasi Pabean):** Hasil rekomendasi Pos Tarif (HS Code) oleh AI Agent berstatus usulan draf dan **wajib divalidasi oleh Supervisor Dokumen (`R-DOC-SPV`)** sebelum enkripsi dan pengiriman data ke CEISA 4.0.
* **BR-05 (Pre-Funding Escrow Talangan Pabean):** Pembayaran billing pabean MPN G3 menggunakan dana perusahaan dilarang dieksekusi kecuali: (a) Klien telah menyetorkan dana talangan dengan status `FUNDS_CLEARED` di bank, atau (b) Memperoleh *Bypass Approval* tertulis dari Direktur Utama.
* **BR-06 (Alih Tugas Otomatis PIC Cuti):** Saat pengajuan cuti staf operasional disetujui atasan, seluruh penugasan proyek kargo aktif yang dipegang otomatis dilimpahkan ke Petugas Pengganti (*Backup Officer*) guna menjamin kepatuhan SLA.
* **BR-07 (Pemisahan Komponen Penagihan P19):** Tagihan invoice klien wajib memisahkan secara tegas antara **Dana Talangan Pabean (*Reimbursable/Disbursement*)** non-pajak dengan **Pendapatan Jasa Forwarding (*Freight/Handling Services*)** yang dikenakan PPN Jasa Pengurusan Transportasi 1,1% sesuai PMK 71/2022.
* **BR-08 (Kepatuhan Pajak Karyawan TER):** Pemotongan PPh 21 bulanan Masa Januari–November wajib menggunakan tarif efektif rata-rata (TER Kategori A, B, C) sesuai PMK 168/2023, sedangkan Masa Desember wajib menggunakan tarif progresif Pasal 17 UU PPh dengan penerbitan Formulir 1721-A1.
* **BR-09 (Kepatuhan Plafon Jaminan Pensiun BPJS):** Perhitungan iuran Jaminan Pensiun (JP) wajib mematuhi batas upah maksimal yang berlaku (Rp 11.086.300 efektif Maret 2026).
* **BR-10 (Perlindungan Data Biometrik UU PDP):** Berkas foto wajah dan data biometrik karyawan dilarang disimpan di direktori publik internet dan wajib dienkripsi AES-256 pada media penyimpanan dan basis data.
* **BR-11 (Masa Retensi Data Biometrik 2 Tahun):** Data biometrik karyawan yang telah berhenti kerja atau telah melewati masa retensi 2 tahun wajib dimusnahkan secara permanen dari sistem.
* **BR-12 (Integritas Penguncian Buku Akuntansi):** Transaksi keuangan, entri jurnal, atau penagihan invoice dilarang keras dibuat, diubah, atau dihapus pada periode akuntansi yang berstatus `is_closed = true`.
* **BR-13 (Idempotensi dan Pencegahan Jurnal Ganda):** Setiap mutasi operasional (Invoice, Billing, Vendor Bill, Payroll) hanya boleh menerbitkan tepat satu pasang jurnal transaksi seimbang (*balanced double-entry*).
* **BR-14 (Eskalasi Denda Demurrage Berjenjang):** Sistem wajib memicu peringatan otomatis eskalasi masa bebas kontainer pada **H-3, H-2, dan H-1** sebelum denda pelayaran berjalan.
* **BR-15 (Pemrosesan Asinkron Dokumen Massal):** Ekstraksi berkas pabean batch (ZIP) wajib diproses melalui background queue worker guna menjaga responsivitas thread HTTP web server.
* **BR-16 (Isolasi Saldo Awal Data Migrasi F16):** Kargo yang diimpor melalui mekanisme lompat tahap (status `IMPORTED` / `SKIPPED`) wajib dikecualikan secara absolut dari perhitungan metrik keterlambatan SLA staf dan dicatat sebagai saldo awal piutang/utang.
* **BR-17 (Kerahasiaan Akses Portal Klien):** Akses pelacakan kargo klien berbasis token acak kriptografis 64-karakter dengan pembatasan laju akses (*rate limit*) 60 request/menit.
* **BR-18 (Controller-Level RBAC Enkapsulasi):** Seluruh aksi mutasi data wajib diproteksi wewenang permission granular di level controller menggunakan 12 peran resmi organisasi.
* **BR-19 (Pencatatan Jejak Forensik Append-Only):** Setiap aksi penambahan, pengubahan, dan penghapusan data pada 16 model entitas inti wajib tercatat pada tabel `audit_logs` bersama alamat IP dan payload perubahan.
* **BR-20 (Disiplin Desain Korporat Bebas-Slop):** Antarmuka pengguna wajib mematuhi panduan desain Apple HIG Bento Grid, warna aksen tunggal Sky Blue (`#0284c7`), squircle geometry, dan bebas dari warna-warni acak atau emoji struktural.

---

## 6. KEBUTUHAN BISNIS PER DOMAIN OPERASIONAL

### 6.1 Domain Kepabeanan & CEISA 4.0
* Bisnis membutuhkan integrasi host-to-host yang stabil dengan portal Ditjen Bea Cukai.
* Payload data PIB (BC 2.0) dan PEB (BC 3.0) wajib mematuhi skema Flat Root resmi DJBC untuk mencegah penolakan sistem (*Error 400 Bad Request*).
* Operator pabean membutuhkan fitur *Auto-Save Draft* untuk mencegah hilangnya data input panjang saat gangguan jaringan pelabuhan.
* Diperlukan validasi pra-kirim (*Pre-Submission Compliance Check*) untuk mendeteksi barang yang terkena aturan Larangan dan Pembatasan (Lartas) kementerian terkait.

### 6.2 Domain Delivery Order & Penanganan Kontainer
* Bisnis membutuhkan otomatisasi korespondensi dengan agen pelayaran via email resmi bertanda tangan digital dengan lampiran B/L otomatis.
* Diperlukan dashboard pemantau masa bebas kontainer (*Demurrage & Detention Radar*) yang memberikan peringatan dini otomatis kepada tim operasional dan importir.

### 6.3 Domain Traffic, Lapangan & Pelabuhan
* Petugas lapangan membutuhkan aplikasi web mobile yang kontras tinggi di bawah terik matahari dermaga.
* Pengunggahan 4-stage foto pemeriksaan behandle dan segel baru kontainer wajib memiliki resiliensi terhadap sinyal lemah (*offline retry queue*).
* Diperlukan pencatatan gate-out pelabuhan yang terintegrasi dengan data armada truk dan pengemudi.

### 6.4 Domain SDM, Presensi Biometrik & UU PDP No. 27/2022
* Perusahaan wajib terbebas dari tuntutan pidana dan perdata UU PDP dengan mengamankan foto absensi ke penyimpanan privat terenkripsi.
* Karyawan wajib menandatangani lembar persetujuan pemrosesan data biometrik secara digital sebelum diizinkan menggunakan absensi kamera.
* Presensi lokasi wajib memvalidasi radius geofence kantor/pelabuhan secara akurat tanpa manipulasi lokasi (*fake GPS prevention*).

### 6.5 Domain Penggajian (Payroll) & Perpajakan Karyawan
* Penghitungan PPh 21 bulanan wajib akurat sesuai tabel TER Kategori A, B, C (PMK 168/2023).
* Sistem wajib menerbitkan Formulir Bukti Potong 1721-A1 resmi pada Masa Pajak Desember dengan perhitungan tarif progresif Pasal 17 setahun penuh.
* Pemotongan kasbon karyawan wajib otomatis terintegrasi dengan slip gaji bulanan.

### 6.6 Domain Keuangan (Finance) & Manajemen Talangan Pabean
* Manajemen piutang kargo (P19) wajib memisahkan talangan bea masuk/pajak impor dari pendapatan jasa.
* Direksi membutuhkan kunci sistem (*Escrow Verification Gate*) yang mencegah pengeluaran dana talangan kas perusahaan sebelum dana importir masuk.
* Rekapitulasi pajak pertambahan nilai wajib mendukung ekspor skema e-Faktur PPN Jasa Pengurusan 1,1% (PMK 71/2022).

### 6.7 Domain Akuntansi (Accounting) & Kepatuhan Pembukuan
* Pembukuan wajib berpasangan seimbang (*balanced double-entry*) otomatis untuk seluruh transaksi operasional.
* Periode akuntansi bulanan yang telah ditutup (*Lock Period*) wajib terkunci secara permanen dan tidak dapat dimutasi kembali kecuali dibuka resmi oleh Pimpinan.
* Tersedia fitur pembatalan jurnal satu-klik (*Reverse Entry*) yang mencatat jurnal pembalik transparan.

### 6.8 Domain Komersial, CRM & Master Entitas Bisnis
* Pendaftaran Klien Baru wajib mencakup verifikasi masa berlaku Surat Kuasa PPJK (minimal 30 hari).
* Master Klien dan Master Vendor wajib memiliki modul CRUD mandiri di admin panel untuk pengkinian nomor kontak, email operasional, rekening bank, dan dokumen legalitas.
* Saluran komunikasi pelanggan via WhatsApp wajib terhubung resmi ke Meta Cloud API untuk pengiriman notifikasi status pengiriman otomatis.

---

## 7. METRIK KEBERHASILAN & INDIKATOR KINERJA UTAMA (KPIS)

| Indikator Kinerja Bisnis | Kondisi Eksisting (Baseline Audit) | Target Capaian GENIE v2.0 | Metode Pengukuran |
| :--- | :---: | :---: | :--- |
| **Tingkat Kepatuhan UU PDP Biometrik** | 0% (Foto di folder publik) | **100% Patuh** | Audit keamanan berkas & validasi consent. |
| **Insiden Denda Demurrage Kontainer** | Rata-rata 3–5 kasus/bulan | **< 0.5 kasus/bulan** | Rekap biaya operasional tagihan pelayaran. |
| **Waktu Pembuatan Draf PIB BC 2.0** | Rata-rata 45 menit/dokumen | **< 15 menit/dokumen** | Waktu drafting dengan bantuan OCR F15 & AI. |
| **Tingkat Kegagalan Payload CEISA** | 0% (Flat Root tuntas) | **0% Penolakan Skema** | Log gateway CEISA 4.0 status 200/201. |
| **Kepatuhan Pajak Karyawan 1721-A1** | Belum tersedia (0%) | **100% Terbit Tepat Waktu** | Rekap Form 1721-A1 Masa Desember terverifikasi. |
| **Tingkat Gagal Bayar Talangan Klien** | Berisiko tinggi | **0 Kasus (Zero Default)** | Penegakan Escrow Verification Gate Finance. |
| **Cakupan Pengujian Otomatis Akuntansi**| 0% test coverage | **> 85% Test Coverage** | Eksekusi automated PHPUnit feature tests. |

---

## 8. BATASAN BISNIS, ASUMSI & KETERGANTUNGAN EKSTERNAL

### 8.1 Asumsi Bisnis
1. Koneksi jaringan internet stabil tersedia di kantor pusat dan cabang (Jakarta, Surabaya, Semarang).
2. Perangkat ponsel staf lapangan minimal berbasis Android 10 atau iOS 14 dengan fitur kamera dan GPS aktif.
3. Importir klien memiliki kontak penanggung jawab operasional yang aktif menerima korespondensi WhatsApp dan Email.

### 8.2 Ketergantungan Eksternal (External Dependencies)
1. **Server Open API CEISA 4.0 DJBC:** Kelancaran pengiriman dokumen bergantung pada ketersediaan layanan gateway Ditjen Bea Cukai.
2. **Meta Cloud Graph API (WABA):** Akun WhatsApp Business terverifikasi dan kredit template pesan Meta aktif.
3. **Penyedia AI LLM / OCR:** Ketersediaan token API OpenAI-compatible / Gemini untuk modul ekstraksi dokumen cerdas.

---
*Dokumen Kebutuhan Bisnis (BRD) ini disahkan sebagai acuan tunggal pengembangan teknis sistem GENIE ERP v2.0.*  
*PT Agung Trans Solusindo & Cooca.id — All Rights Reserved © 2026.*