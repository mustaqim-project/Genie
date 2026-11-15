# LAPORAN AUDIT FITUR, KODE SUMBER, DAN KEPATUHAN SISTEM SECARA KOMPREHENSIF
## SISTEM ERP KEPABEANAN & LOGISTIK EKSPOR-IMPOR: PATS_CEISA (GENIE)
**PT AGUNG TRANS SOLUSINDO & COOCA.ID**  
*Klasifikasi: Laporan Audit Arsitektur, Kepatuhan Regulasi, Integritas Fungsional, dan Kualitas Perangkat Lunak*  
*Status: Audit Forensik Menyeluruh (Strictly Read-Only)*  
*Tanggal Rilis: 15 November 2026*  
*Versi Dokumen: 2.0-Master*  

---

## DAFTAR ISI

1. [Ringkasan Eksekutif (Executive Summary)](#1-ringkasan-eksekutif-executive-summary)
   - [1.1 Latar Belakang & Ruang Lingkup Audit](#11-latar-belakang--ruang-lingkup-audit)
   - [1.2 Skor Kesehatan Sistem (System Health Score)](#12-skor-kesehatan-sistem-system-health-score)
   - [1.3 Prestasi Kunci Arsitektur (Key Architectural Achievements)](#13-prestasi-kunci-arsitektur-key-architectural-achievements)
   - [1.4 Kerentanan & Gap Kritis (Critical Vulnerabilities & Gaps)](#14-kerentanan--gap-kritis-critical-vulnerabilities--gaps)
2. [Matriks Status Seluruh Modul (Master Feature Status Matrix)](#2-matriks-status-seluruh-modul-master-feature-status-matrix)
3. [Rincian Temuan Gap per Modul & Analisis Mendalam (In-Depth Gap Analysis)](#3-rincian-temuan-gap-per-modul--analisis-mendalam-in-depth-gap-analysis)
   - [3.1 Modul Kepabeanan & CEISA 4.0 DJBC (BC 2.0 & BC 3.0)](#31-modul-kepabeanan--ceisa-40-djbc-bc-20--bc-30)
   - [3.2 Modul Operasional Berantai P01 s.d. P19](#32-modul-operasional-berantai-p01-sd-p19)
   - [3.3 Modul F15: Batch Import & Ekstraksi Asinkron](#33-modul-f15-batch-import--ekstraksi-asinkron)
   - [3.4 Modul F16: Lompat Tahap (Stage Skipping Engine)](#34-modul-f16-lompat-tahap-stage-skipping-engine)
   - [3.5 Modul Delivery Order (DO) & Pelayaran](#35-modul-delivery-order-do--pelayaran)
   - [3.6 Modul Traffic, Dispatch Armada & Lapangan](#36-modul-traffic-dispatch-armada--lapangan)
   - [3.7 Modul Human Resource Management (HRM) & Absensi](#37-modul-human-resource-management-hrm--absensi)
   - [3.8 Modul Penggajian (Payroll) & Pajak Karyawan](#38-modul-penggajian-payroll--pajak-karyawan)
   - [3.9 Modul Keuangan Operasional (Finance) & Kas/Bank](#39-modul-keuangan-operasional-finance--kasbank)
   - [3.10 Modul Akuntansi (Accounting) & Penguncian Buku (Lock Period)](#310-modul-akuntansi-accounting--penguncian-buku-lock-period)
   - [3.11 Modul Customer Relationship Management (CRM) & WABA](#311-modul-customer-relationship-management-crm--waba)
   - [3.12 Modul AI Agent & Model Context Protocol (MCP) Server](#312-modul-ai-agent--model-context-protocol-mcp-server)
   - [3.13 Modul Keamanan: RBAC 12 Roles & Audit Trail](#313-modul-keamanan-rbac-12-roles--audit-trail)
   - [3.14 Modul Mesin Cetak Dokumen PDF Resmi](#314-modul-mesin-cetak-dokumen-pdf-resmi)
4. [Evaluasi Kepatuhan Regulasi, Aturan Bisnis, dan Keamanan](#4-evaluasi-kepatuhan-regulasi-aturan-bisnis-dan-keamanan)
   - [4.1 Kepatuhan UU Pelindungan Data Pribadi (UU No. 27 Tahun 2022)](#41-kepatuhan-uu-pelindungan-data-pribadi-uu-no-27-tahun-2022)
   - [4.2 Kepatuhan Pajak Penghasilan TER PMK 168/2023 & Batas BPJS 2026](#42-kepatuhan-pajak-penghasilan-ter-pmk-1682023--batas-bpjs-2026)
   - [4.3 Integritas Akuntansi & Mekanisme Penguncian Periode (Lock Period)](#43-integritas-akuntansi--mekanisme-penguncian-periode-lock-period)
   - [4.4 Standar Open API Gateway CEISA 4.0 DJBC (Katalog 1–6)](#44-standar-open-api-gateway-ceisa-40-djbc-katalog-16)
   - [4.5 Penegakan Controller-Level RBAC 12 Roles & Jejak Forensik](#45-penegakan-controller-level-rbac-12-roles--jejak-forensik)
5. [Analisis Eksekusi Automated Test Suite](#5-analisis-eksekusi-automated-test-suite)
   - [5.1 Inventarisasi & Metrik Eksekusi Pengujian Otomatis](#51-inventarisasi--metrik-eksekusi-pengujian-otomatis)
   - [5.2 Otentisitas Pengujian: Authentic Behavioral Tests vs Mock](#52-otentisitas-pengujian-authentic-behavioral-tests-vs-mock)
   - [5.3 Analisis Akar Masalah Kegagalan Automated Tests](#53-analisis-akar-masalah-kegagalan-automated-tests)
   - [5.4 Analisis Kesenjangan Cakupan Pengujian (0% Coverage Areas)](#54-analisis-kesenjangan-cakupan-pengujian-0-coverage-areas)
6. [Prioritized Remediation Roadmap & Backlog Perbaikan](#6-prioritized-remediation-roadmap-peta-jalan-perbaikan)
   - [6.1 Fase 1: Perbaikan Cepat & Hotfix Kritis (Minggu 1)](#61-fase-1-perbaikan-cepat--hotfix-kritis-minggu-1)
   - [6.2 Fase 2: Kepatuhan Regulasi & Pengerasan Keamanan (Minggu 2–3)](#62-fase-2-kepatuhan-regulasi--pengerasan-keamanan-minggu-23)
   - [6.3 Fase 3: Fondasi Arsitektur Inti & Penyelesaian Fitur (Minggu 4–6)](#63-fase-3-fondasi-arsitektur-inti--penyelesaian-fitur-minggu-46)
   - [6.4 Matriks Definition of Done (DoD) & Acceptance Criteria](#64-matriks-definition-of-done-dod--acceptance-criteria)
   - [6.5 Daftar Lengkap Item yang Perlu Diperbaiki (Actionable Repair Backlog)](#65-daftar-lengkap-item-yang-perlu-diperbaiki-actionable-repair-backlog)
7. [Audit Kelengkapan Fitur Secara User Experience (UX)](#7-audit-kelengkapan-fitur-secara-user-experience-ux)
   - [7.1 Evaluasi Ergonomi Alur Kerja (Workflow Ergonomics) P01–P19 & Cockpit 11 Tahap](#71-evaluasi-ergonomi-alur-kerja-workflow-ergonomics-p01p19--cockpit-11-tahap)
   - [7.2 Zero Data Loss & Mekanisme Pencegahan Kesalahan (Error Prevention)](#72-zero-data-loss--mekanisme-pencegahan-kesalahan-error-prevention)
   - [7.3 Beban Kognitif (Cognitive Load), View Monolitik & Ergonomi Keyboard](#73-beban-kognitif-cognitive-load-view-monolitik--ergonomi-keyboard)
   - [7.4 Evaluasi Pengalaman Pengguna Staf Lapangan (Mobile Port Ops UX)](#74-evaluasi-pengalaman-pengguna-staf-lapangan-mobile-port-ops-ux)
   - [7.5 Evaluasi Portal Mandiri Klien (Client Experience & Transparansi)](#75-evaluasi-portal-mandiri-klien-client-experience--transparansi)
   - [7.6 Feedback Loops, State Komunikatif & Penanganan Empty State](#76-feedback-loops-state-komunikatif--penanganan-empty-state)
8. [Audit Kelengkapan Fitur Secara User Interface (UI)](#8-audit-kelengkapan-fitur-secara-user-interface-ui)
   - [8.1 Kepatuhan Sistem Token 3-Layer & Eliminasi Nilai Hardcoded](#81-kepatuhan-sistem-token-3-layer--eliminasi-nilai-hardcoded)
   - [8.2 Arsitektur Apple HIG Bento Grid & Konsistensi Geometri Squircle](#82-arsitektur-apple-hig-bento-grid--konsistensi-geometri-squircle)
   - [8.3 Tipografi, Hierarki Visual & Angka Tabular Monospace](#83-tipografi-hierarki-visual--angka-tabular-monospace)
   - [8.4 Disiplin Anti-Slop: Audit Warna Pelangi, Fake Telemetry & Emojis](#84-disiplin-anti-slop-audit-warna-pelangi-fake-telemetry--emojis)
   - [8.5 Aksesibilitas Kontras Warna (WCAG 2.1 AA) & Keselarasan Dark Mode](#85-aksesibilitas-kontras-warna-wcag-21-aa--keselarasan-dark-mode)
   - [8.6 Responsivitas Multi-Perangkat (Mobile PWA, iPadOS Tablet, Desktop Workspace)](#86-responsivitas-multi-perangkat-mobile-pwa-ipados-tablet-desktop-workspace)
9. [Audit Menyeluruh Manajemen Risiko (Risk Management Audit)](#9-audit-menyeluruh-manajemen-risiko-risk-management-audit)
   - [9.1 Matriks Evaluasi Risiko Enterprise (Risk Heatmap 5x5)](#91-matriks-evaluasi-risiko-enterprise-risk-heatmap-5x5)
   - [9.2 Analisis Risiko Operasional, Demurrage & Penumpukan Pelabuhan](#92-analisis-risiko-operasional-demurrage--penumpukan-pelabuhan)
   - [9.3 Analisis Risiko Kepatuhan Pabean (Customs Regulatory Risk & Sanksi Notul/SPTNP)](#93-analisis-risiko-kepatuhan-pabean-customs-regulatory-risk--sanksi-notulsptnp)
   - [9.4 Analisis Risiko Finansial, Fraud Talangan Pabean & Arus Kas](#94-analisis-risiko-finansial-fraud-talangan-pabean--arus-kas)
   - [9.5 Analisis Risiko Hukum & Kepatuhan Perlindungan Data Pribadi (UU PDP No. 27/2022)](#95-analisis-risiko-hukum--kepatuhan-perlindungan-data-pribadi-uu-pdp-no-272022)
   - [9.6 Analisis Risiko Keamanan Siber, Penetrasi & Ketersediaan Sistem](#96-analisis-risiko-keamanan-siber-penetrasi--ketersediaan-sistem)
10. [Kerangka Optimasi Menuju Level Fitur Maksimal (Feature Maximization Framework)](#10-kerangka-optimasi-menuju-level-fitur-maksimal-feature-maximization-framework)
    - [10.1 Model Kematangan Fitur Enterprise Logistik (Maturity Levels 1 s.d. 5)](#101-model-kematangan-fitur-enterprise-logistik-maturity-levels-1-sd-5)
    - [10.2 Analisis Kesenjangan (Gap Analysis) Menuju Level Fitur Maksimal per Modul](#102-analisis-kesenjangan-gap-analysis-menuju-level-fitur-maksimal-per-modul)
    - [10.3 Blueprint Spesifikasi Fitur Generasi Lanjutan (Next-Gen Autonomous Logistics)](#103-blueprint-spesifikasi-fitur-generasi-lanjutan-next-gen-autonomous-logistics)
    - [10.4 Arsitektur Target Ekosistem Digital 2026/2027 (PATS_CEISA Apex)](#104-arsitektur-target-ekosistem-digital-20262027-pats_ceisa-apex)
11. [Kesimpulan Akhir & Rekomendasi Terpadu (Unified Final Conclusion)](#11-kesimpulan-akhir--rekomendasi-terpadu-unified-final-conclusion)

---

## 1. RINGKASAN EKSEKUTIF (EXECUTIVE SUMMARY)

### 1.1 Latar Belakang & Ruang Lingkup Audit
Audit komprehensif ini dilakukan secara independen terhadap repositori kode sumber **PATS_CEISA (GENIE)** di direktori `c:\laragon\www\GENIE`. Sistem ini dibangun di atas kerangka kerja **Laravel 11**, **PHP 8.3.28**, basis data **MySQL 8.4.3**, dan **Tailwind CSS / Apple HIG Bento Design**, yang dirancang untuk mengotomasi operasional logistik ekspor-impor, Pengurusan Jasa Kepabeanan (PPJK), pengurusan Delivery Order (DO), traffic armada pelabuhan, manajemen SDM & absensi, penggajian, penagihan, serta pembukuan akuntansi berpasangan (double-entry bookkeeping) untuk **PT Agung Trans Solusindo**.

Ruang lingkup audit mencakup 31 sub-modul fungsional yang tersebar di 8 domain utama:
1. Alur Operasional Berantai Logistik Inti (**P01 s.d. P19**).
2. Integrasi Open API Kepabeanan **CEISA 4.0 DJBC** (BC 2.0 PIB & BC 3.0 PEB).
3. Modul Core v2.0: **F15** (Batch Import & Ekstraksi Asinkron) dan **F16** (Lompat Tahap & Impor Historis).
4. Pengurusan **Delivery Order (DO)** pelayaran dan **Traffic / Dispatch** kontainer.
5. Manajemen SDM (**HRM**), Geofencing GPS, Face Attendance, dan Kepatuhan **UU PDP No. 27/2022**.
6. Penggajian (**Payroll**) berskema **TER PMK 168/2023**, iuran **BPJS Maret 2026**, dan pajak tahunan **Form 1721-A1**.
7. **Finance & Accounting**: Invoicing klien, talangan pabean, kas/bank, bagan akun (COA), serta penguncian periode buku (**Lock Period**).
8. Ekstensi Cerdas: **CRM Sales Pipeline & WABA**, **AI Agent OpenAI-compatible**, **Model Context Protocol (MCP) Server**, **RBAC 12 Roles**, dan **Mesin PDF**.

### 1.2 Skor Kesehatan Sistem & Evaluasi Multi-Dimensi (System Health & Multi-Dimensional Score)
Berdasarkan pembobotan forensik kode sumber aktual, kualitas antarmuka visual (UI), pengalaman alur kerja pengguna (UX), tata kelola kepatuhan regulasi, serta kematangan manajemen risiko:

| No | Dimensi Evaluasi | Bobot | Skor Aktual | Kategori Kesiapan | Catatan Evaluasi Forensik |
| :---: | :--- | :---: | :---: | :---: | :--- |
| **1** | **Arsitektur Inti & Kepabeanan (CEISA 4.0, P01–P19)** | 18% | **86 / 100** | **Sangat Baik** | Remediasi Flat Root tuntas; kalkulasi pabean & lifecycle dokumen riil. |
| **2** | **Kepatuhan Pajak & Finansial (TER PMK 168, COA, GL)** | 15% | **78 / 100** | **Baik** | TER matematis akurat; namun Form 1721-A1 hilang & lock period bocor di DB event. |
| **3** | **Kepatuhan Regulasi Data & Keamanan (UU PDP, RBAC)** | 15% | **65 / 100** | **Perhatian Kritis** | Foto absensi di webroot publik; verifikasi wajah mock; consent form di-bypass. |
| **4** | **Fitur Lanjutan v2.0 & Integrasi (F15, F16, AI, MCP, CRM)** | 15% | **74 / 100** | **Baik** | AI & MCP production-grade; F15 tanpa queue; WABA simulasi lokal. |
| **5** | **Integritas Automated Test Suite & QA** | 10% | **72 / 100** | **Cukup** | 100% unit tests lolos; 0% cakupan pengujian pada modul akuntansi & kas/bank. |
| **6** | **User Experience (UX) & Ergonomi Kerja** | 10% | **68 / 100** | **Perlu Peningkatan** | View monolitik 150KB; ketiadaan auto-save draft; ketiadaan hotkeys pabean. |
| **7** | **User Interface (UI) & Desain Token Apple HIG** | 7% | **71 / 100** | **Cukup Baik** | Bento grid rapi; namun terdapat hardcoded hex `#007AFF` & angka sans non-tabular. |
| **8** | **Tata Kelola Manajemen Risiko (Risk Management)** | 10% | **64 / 100** | **Perhatian Kritis** | Belum ada early-warning demurrage D-3, escrow gate talangan, & proteksi DoS F15. |
| **TOTAL** | **SKOR KESEHATAN KESELURUHAN (OVERALL SCORE)** | **100%** | **73.4 / 100** | **Siap UAT dengan Remediasi Kritis (Production-Ready with Remediation)** |

### 1.3 Prestasi Kunci Arsitektur (Key Architectural Achievements)
1. **Remediasi Tuntas Payload Flat Root CEISA 4.0 DJBC:**  
   Kesenjangan fatal penolakan 100% (*Error 400 Bad Request*) yang dicatat pada dokumen `docs/AUDIT_GAP_CEISA_40.md` telah diselesaikan sepenuhnya pada `DocumentBC20Service.php` dan `DocumentBC30Service.php`. Seluruh properti header kini berada pada tingkat akar (*Flat Root Level*) sesuai JSON Schema resmi DJBC (BC 2.0 v.0.5.7.23 & BC 3.0 v.0.5.29), dilengkapi data manifes kapal BC 1.1 dan entitas pemilik barang.
2. **Implementasi Resmi Model Context Protocol (MCP) Server & AI Customs Agent:**  
   `McpCustomsAgentController.php` mengimplementasikan protokol standar MCP (versi `2024-11-05`) dengan transport ganda Server-Sent Events (SSE) dan JSON-RPC 2.0. Sebanyak 29 berkas kelas domain (27 tools aktif) telah didaftarkan dalam `McpToolRegistry.php`, mencakup ranah pabean, shipment, invoice, kas/bank, HRM, hingga tata kelola destructive actions dengan tiket persetujuan (*Human-in-the-Loop*).
3. **Penegakan Hak Akses Berlapis (Controller-Level RBAC 12 Roles):**  
   Seluruh 12 peran organisasi (`R-ADM` hingga `R-CS`) telah didaftarkan secara presisi dalam seeder. Controllers memanfaatkan arsitektur native Laravel 11 `HasMiddleware` serta `Gate::authorize()` pada method level, mencegah eskalasi wewenang secara murni tanpa ketergantungan paket eksternal.
4. **Jejak Forensik Global (Global Append-Only Audit Trail):**  
   Trait `Auditable` terpasang pada 16 model entitas utama, merekam perubahan state (`created`, `updated`, `deleted`) secara otomatis ke tabel `audit_logs` dengan format JSON diff (`old_value` vs `new_value`), alamat IP, dan user-agent.
5. **Kalkulator TER PPh 21 PMK 168/2023 & Plafon BPJS Terkini:**  
   `TaxTerCalculatorService.php` memformulasikan seluruh 44 lapisan Kategori A, 40 lapisan Kategori B, dan 41 lapisan Kategori C secara presisi matematis, terintegrasi dengan plafon iuran Jaminan Pensiun efektif Maret 2026 (Rp11.086.300) dan BPJS Kesehatan (Rp12.000.000).
6. **Mesin Cetak PDF Berstandar Kedinasan DJBC:**  
   `PdfController.php` bersama 6 template Blade di `resources/views/pdf/` menghasilkan dokumen PIB, PEB, Invoice, Billing MPN G3, Quotation, dan DO Request dengan akurasi tata letak tinggi sesuai format standar kementerian/instansi.

### 1.4 Kerentanan & Gap Kritis (Critical Vulnerabilities & Gaps)
1. **[CRITICAL - Pelanggaran UU PDP No. 27/2022] Kebocoran Foto Absensi & Ketiadaan Consent:**  
   Pada `StaffPortalController.php` baris 296 dan 380, berkas foto absensi kamera selfie disimpan langsung ke `public_path('uploads/attendances')` tanpa enkripsi. Foto biometrik wajah karyawan dapat diakses langsung oleh publik internet melalui peramban web tanpa autentikasi. Selain itu, form clock-in harian portal staf **tidak memeriksa validitas persetujuan biometrik (`BiometricConsent`)**, melanggar asas pemrosesan data spesifik UU PDP.
2. **[CRITICAL - Ketiadaan Background Queue Worker F15] Risiko 504 Timeout:**  
   Repositori sama sekali tidak memiliki direktori `app/Jobs` (`Test-Path app/Jobs` $\rightarrow$ `False`). Proses ekstraksi file ZIP massal, parsing OCR, dan validasi silang pada `BatchImportService.php` berjalan secara 100% sinkron di dalam satu thread HTTP request. Pengunggahan dokumen pabean dalam volume besar dipastikan memicu crash memori atau HTTP Gateway Timeout.
3. **[MAJOR - Deadlock Otorisasi F16] Ketidakcocokan Nama Role Approver Seeder:**  
   `StagePrerequisiteSeeder.php` baris 39 dan 48 menetapkan `'approver_role' => 'R-SPV'`. Padahal, peran resmi supervisor pada sistem adalah `R-DOC-SPV` (kode `R-SPV` tidak ada di tabel `roles`). Akibatnya, supervisor dokumen tidak dapat menyetujui pengajuan lompat tahap (*stuck in PENDING*), kecuali disetujui manual oleh SuperAdmin.
4. **[MAJOR - Stub Email Permintaan DO Pelayaran (P09)]:**  
   Method `ShipmentWorkflowService::sendVendorEmailForDo` (baris 737–772) hanya merangkai teks email di memori dan mengupdate status database ke `EMAIL_SENT`, tanpa memanggil `Mail::to()` atau `Mail::send()`. Vendor pelayaran tidak pernah menerima email permintaan rilis DO secara aktual.
5. **[MAJOR - Simulasi WhatsApp Omnichannel (WABA)]:**  
   `WhatsAppController.php` dan `CrmController.php` tidak terhubung ke Meta Cloud Graph API. Baris 162 `CrmController.php` secara eksplisit mencantumkan komentar `// Simulasi integrasi Meta Cloud API`, pengiriman pesan hanya menulis status `sent` langsung ke tabel lokal, dan metrik analitik media sosial menggunakan angka acak `rand()`.
6. **[MAJOR - Celah Penguncian Buku (Lock Period) & Bug Undefined Variable]:**  
   Penguncian periode akuntansi tidak diproteksi pada Eloquent Model Event level (operasi `$model->delete()` dapat menghapus jurnal periode terkunci). Pada `ClientInvoiceController.php`, kegagalan posting jurnal di periode tertutup ditelan (*swallowed*) hanya sebagai `Log::warning()`. Selain itu, terdapat bug fatal pemanggilan `$entryDate` yang belum diinisialisasi pada `AccountingService.php` baris 371 dan 418.
7. **[MAJOR - Ketiadaan Formulir 1721-A1 & Rekonsiliasi Tahunan]:**  
   Kalkulasi PPh 21 Masa Desember (Pasal 17 tarif progresif akumulatif, biaya jabatan tahunan max Rp6 juta, pengurang PTKP setahun, dan formulir bukti potong 1721-A1) sama sekali belum diimplementasikan di kode sumber.
8. **[CRITICAL BUG - RouteNotFoundException pada Dokumen View]:**  
   Template `resources/views/core/document_index.blade.php` baris 39 memanggil `route('core.document.sync-all-ceisa')`, padahal rute resmi pada `routes/web.php` adalah `config.sync-all`. Kesalahan ini menyebabkan crash HTTP 500 fatal saat pengguna dengan izin `setting.manage` membuka halaman indeks dokumen.
9. **[MAJOR - 0% Automated Test Coverage pada Modul Akuntansi]:**  
   Buku besar, jurnal umum, neraca saldo, neraca lajur, arus kas, rekonsiliasi bank, dan lock period memiliki nol berkas pengujian otomatis di `tests/`.

---

## 2. MATRIKS STATUS SELURUH MODUL (MASTER FEATURE STATUS MATRIX)

Tabel berikut menyajikan pemetaan 100% fitur sistem berdasarkan hasil verifikasi forensik kode sumber:

| No | Modul / Fitur | Status Klasifikasi | Bukti Kode Sumber (File, Class, Method, Baris) | Tingkat Keparahan Gap | Ringkasan Temuan & Gap |
| :---: | :--- | :---: | :--- | :---: | :--- |
| **1** | **Core Operations: P01–P07 (Tim Dokumen)** | **Real Functionality** | `ShipmentWorkflowService::createProject`, `detectHsCodeFromPackingList`, `submitCeisaAndAdvanceToDo` | **Minor** | Alur pembuatan proyek, deteksi HS code, validasi 3-way match, dan pengajuan CEISA berjalan riil. Inisialisasi slot dokumen menghasilkan 11-14 slot (PRD mensyaratkan 9 slot), memicu assertion mismatch pada test. |
| **2** | **Core Operations: P08, P10–P12 (Tim DO)** | **Real Functionality** | `DoWorkflowController::releaseDo`, `ShipmentWorkflowService::releaseDo`, `Shipment.php:845` | **Minor** | Pencatatan vendor pelayaran, perhitungan mundur free time demurrage, dan unggah berkas rilis DO berfungsi nyata sebagai sistem tracking operasional. |
| **3** | **Core Operations: P09 (Otomasi Email DO)** | **Mock / Placeholder / Stub** | `ShipmentWorkflowService.php:737-772` (`sendVendorEmailForDo`) | **Major** | Hanya menyusun string subject dan body di memori serta mengubah status database ke `EMAIL_SENT`; tidak pernah memanggil `Mail::send` atau `Mail::to`. Email tidak pernah terkirim ke pihak pelayaran. |
| **4** | **Core Operations: P13–P17 (Traffic & Lapangan)** | **Real Functionality** | `TrafficWorkflowController.php:33-140`, `InspectionPhoto.php`, `advanceTraffic` | **Minor** | Penjaluran status pabean, unggah foto behandle 4-stage segel fisik kontainer (`InspectionPhoto`), pencatatan biaya operasional, dan konfirmasi rilis POD berfungsi penuh. |
| **5** | **Core Operations: P18–P19 (Finance Invoicing)** | **Real Functionality** | `ShipmentWorkflowService::generateClientInvoice`, `ClientInvoiceController::markAsPaid` | **Aman** | Menghitung pemisahan talangan pabean (reimbursable) vs jasa kena pajak (PPN 1.1% PMK 71/2022) dan memicu auto-journal pembukuan. |
| **6** | **CEISA 4.0: Skema Payload BC 2.0 (PIB)** | **Real Functionality** | `DocumentBC20Service.php:320-372` (`buildPayload`) | **Aman** | Struktur payload telah diremediasi menjadi Flat Root Object murni sesuai JSON Schema resmi DJBC v.0.5.7.23, memuat elemen manifes BC 1.1, tarif barang, dan entitas pemilik kode 7. |
| **7** | **CEISA 4.0: Skema Payload BC 3.0 (PEB)** | **Real Functionality** | `DocumentBC30Service.php:145-193` (`buildPayload`) | **Aman** | Struktur payload datar sesuai skema PEB v.0.5.29, mencakup kesiapan barang, bank devisa, kantor periksa/muat, dan flag komoditas curah/migas. |
| **8** | **CEISA 4.0: Gateway Client & Auth Lifecycle** | **Real Functionality** | `CeisaAuthService.php:61-151`, `CeisaClientService.php:57-118` | **Minor** | Implementasi autentikasi `/user/login`, perpanjangan token `/user/update-token`, pre-flight schema check `/document/check`, dan polling status. Default aman di `mock_mode = true` dan `read_only = true`. |
| **9** | **CEISA 4.0: Manifes Kapal & TPS Online H2H** | **Missing** | `docs/AUDIT_GAP_CEISA_40.md` Katalog 3 & 4 | **Major** | Integrasi H2H langsung ke OpenAPI Manifes BC 1.1 kapal (`openapi-manifes`) dan data feed otomatis TPS Online Gate Out (`openapi-tpsonline`) belum dibuat. |
| **10** | **F15: Batch Import - Logika Bisnis & Aturan** | **Real Functionality** | `BatchImportService.php:35-150`, `CrossDocumentValidationService.php:20-180` | **Minor** | Ekstraksi teks berkas, deteksi duplikat SHA-256 hash, koordinat bounding box, dan aturan validasi silang pabean V01–V07 berjalan riil. Aturan V08–V10 belum diimplementasikan. |
| **11** | **F15: Batch Import - Background Queue Workers** | **Missing** | `app/Jobs` (Direktori tidak ditemukan di repositori) | **Critical** | Tidak ada queue jobs (`BatchZipExtractionJob`, dll.). Seluruh pemrosesan batch berjalan sinkron dalam satu thread HTTP web, memicu risiko timeout 504 pada unggahan berkas besar. |
| **12** | **F16: Lompat Tahap - Stage Skipping Engine** | **Real Functionality** | `StageJumpService.php:25-180`, `project_jump_requests` | **Aman** | Mekanisme lompat tahap menandai data lampau sebagai `IMPORTED`, memvalidasi kelengkapan berkas wajib, dan menegakkan isolasi finansial `OPENING_BALANCE` pada periode buku tertutup. |
| **13** | **F16: Lompat Tahap - Otorisasi Role Approval** | **Partial / In-Progress** | `StagePrerequisiteSeeder.php:39, 48`, `StageJumpService.php:80` | **Major** | Seeder menetapkan approver role `R-SPV` yang tidak pernah ada di tabel roles (seharusnya `R-DOC-SPV`), mengakibatkan deadlock otorisasi sehingga hanya SuperAdmin yang dapat meng-approve. |
| **14** | **Delivery Order: Shipping Line API & PIN DO** | **Missing** | `DoWorkflowController.php` | **Major** | Tidak ada integrasi API ke shipping line internasional (Maersk, ONE, dll.) maupun sistem PIN DO elektronik pelabuhan; murni pencatatan form manual. |
| **15** | **Traffic & Armada: Fleet Dispatch & GPS** | **Partial / In-Progress** | `TrafficWorkflowController.php:87-111` (`gateOut`) | **Major** | Tidak ada entitas model `Driver` atau `Vehicle`, tidak ada pelacakan GPS telematika real-time. Gate out hanya mencatat string plat nomor truk dari input form. |
| **16** | **HRM: GPS Geofencing Presensi** | **Real Functionality** | `StaffPortalController.php:229-239`, `WorkLocation.php:66-85` | **Aman** | Implementasi otentik rumus Haversine bola bumi ($R = 6.371.000\text{ m}$) untuk menghitung jarak presisi terhadap radius kantor/pelabuhan. |
| **17** | **HRM: Face Attendance & Biometrik** | **Mock / Placeholder / Stub** | `StaffPortalController.php:315, 398`, `HrmController.php:372` | **Critical** | Skor pencocokan wajah di-hardcode (0.95, 0.96, dan 0.965). Sistem hanya menyimpan unggahan foto selfie tanpa mesin pengenalan wajah biometrik nyata (OpenCV/ML). |
| **18** | **HRM: Kepatuhan UU PDP No. 27/2022** | **Partial / In-Progress** | `StaffPortalController.php:296`, `Employee.php:37`, `BiometricConsent.php` | **Critical** | Foto selfie absensi disimpan tanpa enkripsi di `public/uploads/attendances/` (bocor ke publik). Clock-in harian portal staf tidak memeriksa consent biometrik. Tidak ada worker retensi. |
| **19** | **Payroll: TER PMK 168/2023 & BPJS 2026** | **Real Functionality** | `TaxTerCalculatorService.php:1-253`, `HrmController.php:572-672` | **Aman** | Seluruh 44 lapisan Kategori A, 40 lapisan Kategori B, dan 41 lapisan Kategori C diterapkan matematis; mencakup plafon upah JP Maret 2026 (Rp11.086.300) dan penalti non-NPWP 20%. |
| **20** | **Payroll: Rekonsiliasi Tahunan & Form 1721-A1** | **Missing** | `TaxTerCalculatorService.php`, `HrmController.php` | **Major** | Rekonsiliasi PPh 21 Pasal 17 progresif Masa Pajak Desember, pengurang biaya jabatan tahunan max Rp6 juta, akumulasi PTKP setahun, dan pencetakan Form 1721-A1 belum ada. |
| **21** | **Finance: Invoicing, Talangan, Kas/Bank** | **Real Functionality** | `ClientInvoiceController.php`, `CashBankController.php`, `VendorBillController.php` | **Aman** | Manajemen penagihan P19, pencatatan mutasi kas bank MPN G3, manajemen hutang vendor pelayaran, dan rekapitulasi aging AR/AP berfungsi penuh. |
| **22** | **Accounting: Bagan Akun COA & Jurnal** | **Real Functionality** | `AccountingService.php:54-93`, `ChartOfAccount.php`, `JournalEntry.php` | **Minor** | Pembukuan berpasangan seimbang (debit = kredit), auto-journal operasional otomatis. Terdapat bug variabel `$entryDate` belum terdefinisi pada baris 371 & 418. |
| **23** | **Accounting: Mekanisme Lock Period** | **Partial / In-Progress** | `AccountingPeriod.php:45-53`, `AccountingController.php:393-487` | **Major** | Penutupan buku dan jurnal pemindah Laba Ditahan (`3-2100`) berfungsi; namun penguncian tidak dipasang di level Eloquent model event, dan `ClientInvoiceController` menelan exception. |
| **24** | **CRM: Sales Pipeline & Quotation Generator** | **Real Functionality** | `QuotationController.php:53-270`, `SalesVisitController.php` | **Aman** | Pembuatan quotation, aturan diskon >10% wajib approval Owner, verifikasi masa berlaku Surat Kuasa PPJK, konversi ke Sales Order dan Proyek Shipment berjalan riil. |
| **25** | **CRM: WhatsApp Omnichannel (WABA)** | **Mock / Placeholder / Stub** | `WhatsAppController.php:1-168`, `CrmController.php:162`, `InboxController.php` | **Major** | Simulasi Meta Cloud API murni; pengiriman pesan hanya menulis status `sent` langsung ke database lokal tanpa HTTP request; tanpa webhook receiver; metrik sosial media menggunakan `rand()`. |
| **26** | **AI Agent: Intelijen Kepabeanan & HS Code** | **Real Functionality** | `OpenAiCustomsService.php:8-683`, `AiAgentController.php` | **Aman** | Client cURL OpenAI-compatible lengkap dengan hierarki model fallback, parsing SSE, klasifikasi pos tarif 8-digit BTKI 2022, ekstraksi dokumen, dan konfirmasi *Human-in-the-Loop*. |
| **27** | **AI Agent: Model Context Protocol (MCP)** | **Real Functionality** | `McpCustomsAgentController.php:1-500`, `app/Domain/Mcp/Tools/*` | **Aman** | Spesifikasi protokol MCP standar 2024-11-05 (SSE + JSON-RPC), 29 berkas kelas domain (27 tools), otentikasi Bearer token SHA-256, dan tiket approval untuk aksi destruktif. |
| **28** | **Keamanan: RBAC 12 Roles & Middleware** | **Real Functionality** | `DatabaseSeeder.php:24-175`, `app/Http/Controllers/*`, `AppServiceProvider.php` | **Aman** | 12 peran organisasi terdaftar; diproteksi menggunakan native Laravel 11 `HasMiddleware` dan `Gate::authorize()` granular di controller level. |
| **29** | **Keamanan: Global Audit Trail Forensik** | **Partial / In-Progress** | `AuditLog.php`, `Auditable.php` | **Minor** | Perekaman mutasi `created`, `updated`, `deleted` pada 16 model inti ke tabel `audit_logs` berjalan aktif; namun antarmuka UI Viewer di menu Admin Panel belum tersedia. |
| **30** | **Mesin Cetak: PDF Dokumen Resmi** | **Real Functionality** | `PdfController.php:1-137`, `resources/views/pdf/*.blade.php` | **Aman** | Menghasilkan 6 template cetak presisi resmi (PIB BC 2.0 barcode, PEB BC 3.0, Invoice Klien, Surat DO, Billing MPN G3, Forwarder Quotation) via DomPDF. |
| **31** | **Kualitas: Automated Test Suite** | **Real Functionality** | `tests/Unit/*`, `tests/Feature/*` (24 berkas, 116 tests) | **Major** | Pengujian integrasi nyata memeriksa transaksi DB dan izin peran (Unit tests 100% lulus). Namun terdapat route bug pada view dokumen, truncation string pada test CRM, dan 0% cakupan akuntansi. |

---

## 3. RINCIAN TEMUAN GAP PER MODUL & ANALISIS MENDALAM (IN-DEPTH GAP ANALYSIS)

### 3.1 Modul Kepabeanan & CEISA 4.0 DJBC (BC 2.0 & BC 3.0)
- **Status Integrasi Payload:**  
  Pemeriksaan pada `app/Services/Ceisa/DocumentBC20Service.php` (baris 320–372) dan `DocumentBC30Service.php` (baris 145–193) mengonfirmasi bahwa struktur data telah direfaktor sepenuhnya menjadi **Flat Root Object**:
  ```php
  // app/Services/Ceisa/DocumentBC20Service.php:320-372
  return [
      'asalData' => 'S',
      'nomorAju' => $nomorAju,
      'kodeDokumen' => '20',
      'kodeKantor' => $kodeKantor,
      'ndpbm' => (float) ($shipment->ndpbm ?? 15500),
      'cif' => (float) ($shipment->cif_total ?? 0),
      'fob' => (float) ($shipment->fob ?? 0),
      'freight' => (float) ($shipment->freight ?? 0),
      'asuransi' => (float) ($shipment->asuransi ?? 0),
      'bruto' => (float) ($shipment->bruto ?? 0),
      'netto' => (float) ($shipment->netto ?? 0),
      'nomorBc11' => $shipment->nomor_bc11 ?? null,
      'tanggalBc11' => $shipment->tanggal_bc11?->format('Y-m-d'),
      'posBc11' => $shipment->pos_bc11 ?? null,
      'subposBc11' => $shipment->subpos_bc11 ?? null,
      ...
  ```
  Struktur ini secara resmi menyelesaikan temuan *reject* skema pada `docs/AUDIT_GAP_CEISA_40.md`.
- **Kesenjangan Katalog Layanan (Katalog 3 & 4):**  
  Meskipun Katalog 1 (`openapi-auth`) dan Katalog 2 (`openapi` Pabean) telah berfungsi secara arsitektur, Katalog 3 (`openapi-manifes`) dan Katalog 4 (`openapi-tpsonline`) belum dihubungkan. Data manifes kedatangan kapal dan status rilis kontainer TPS Online masih diinput secara manual oleh staf traffic tanpa validasi otomatis H2H.
- **Mode Eksekusi Gateway:**  
  Konfigurasi pada `config/ceisa.php` menetapkan default `mock_mode = true` dan `read_only = true`. Pada `CeisaClientService.php`, setiap permintaan kirim dokumen non-GET diblokir dengan HTTP 403 demi keamanan lokal sebelum tersambung ke jaringan resmi Bea Cukai.

### 3.2 Modul Operasional Berantai P01 s.d. P19
- **Inkonsistensi Slot Dokumen Checklist (P02):**  
  Dokumen `docs/PRD_PATS_CEISA.md` (baris 67–96) menetapkan 9 slot dokumen wajib: `INVOICE`, `PACKING_LIST`, `BILL_OF_LADING`, `CERTIFICATE_OF_ORIGIN`, `IMPORT_PERMIT`, `SURAT_KUASA`, `DOKUMEN_PELENGKAP`, `DRAFT_PIB_PEB`, dan `BILLING_CEISA`.  
  Namun, pada `ShipmentWorkflowService::initializeChecklistDocuments` (baris 117–142), inisialisasi default menghasilkan 11 slot (dan 12 jika ekspor). Ketidaksesuaian ini menyebabkan automated test `tests/Feature/Bc20CompleteLifecycleTest.php:121` gagal:
  ```
  Failed asserting that 14 matches expected 9.
  ```
- **Integritas Penegakan Transisi Tahap (State Machine):**  
  Sistem penguncian billing (`isBillingLocked()`) bekerja dengan baik pada `Shipment.php:835-843`, memblokir pengeditan atau rollback tahap ketika kode billing MPN G3 telah terbit. Namun pada `ProjectTrackingController::quickUpdateTrackingDok` (baris 299–375), terdapat celah pembaruan status dokumen via AJAX yang dapat memotong urutan alur normal.

### 3.3 Modul F15: Batch Import & Ekstraksi Asinkron
- **Ketiadaan Background Queue Worker (Gap Kritis):**  
  Direktori `app/Jobs` tidak ditemukan di repositori (`Test-Path app/Jobs` $\rightarrow$ `False`).  
  Pada `BatchImportService.php` (baris 35–74):
  ```php
  public function createBatch(array $uploadedFiles, string $importSource, string $type, ?string $userId = null): ImportBatch
  {
      ...
      foreach ($uploadedFiles as $file) {
          $this->processUploadedFile($batch, $file); // Berjalan sinkron 100%!
      }
      $this->runValidations($batch);
      return $batch;
  }
  ```
  *Dampak:* Pemrosesan file ZIP massal berisi puluhan dokumen PDF/gambar berjalan langsung di thread HTTP web request. Hal ini melanggar FSD Bagian 4 dan dokumen perbaikan v2.0, serta rentan mengakibatkan `504 Gateway Timeout` dan kehabisan memori (*memory exhaustion*).
- **Aturan Validasi Silang Dokumen:**  
  Aturan V01 (konsistensi B/L), V02 (toleransi berat), V03 (sinkronisasi total header vs item), V04 (ISO 6346 kontainer), V05 (validasi 8-digit HS code), V06 (kurs KMK), dan V07 (NPWP 16 digit PMK 112/2022) telah diimplementasikan dengan baik pada `CrossDocumentValidationService.php`. Aturan V08 (Lartas), V09 (Pelabuhan), dan V10 (Perizinan Impor) belum diimplementasikan.

### 3.4 Modul F16: Lompat Tahap (Stage Skipping Engine)
- **Deadlock Otorisasi Supervisor pada Seeder:**  
  Pada `database/seeders/StagePrerequisiteSeeder.php` baris 39 dan 48:
  ```php
  'approver_role' => 'R-SPV', // BUG: Role ini tidak terdaftar di sistem!
  ```
  Pada `DatabaseSeeder.php` baris 28 dan PRD, peran resmi supervisor dokumen didefinisikan sebagai `R-DOC-SPV`.  
  Akibatnya, pengecekan otorisasi pada `StageJumpService.php` baris 80:
  ```php
  $isApprover = $user && ($user->isSuperAdmin() || ($validation['approver_role'] && $user->hasRole($validation['approver_role'])));
  ```
  Pengecekan `$user->hasRole('R-SPV')` akan selalu mengembalikan `false` bagi pengguna bersertifikat `R-DOC-SPV`, sehingga permintaan lompat tahap tertahan di status `PENDING` kecuali disetujui langsung oleh SuperAdmin.
- **Isolasi Finansial:**  
  `StageJumpService.php` (baris 124–130) berhasil menegakkan aturan bahwa jika periode akuntansi saat ini terkunci (`AccountingPeriod::isDateLocked(now())`), sistem secara otomatis memaksa penyesuaian melalui mode `OPENING_BALANCE` untuk mencegah pencatatan transaksi liar ke periode lampau.

### 3.5 Modul Delivery Order (DO) & Pelayaran
- **Stub Email Pengiriman Permintaan Invoice DO (P09):**  
  Pada `ShipmentWorkflowService.php` baris 737–772 (`sendVendorEmailForDo`):
  ```php
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
  ```
  *Temuan:* Fungsi ini hanya menyusun template teks di memori dan mengupdate kolom database, **tanpa memanggil `Mail::send()` atau `Mail::to()`**. Tidak ada pengiriman email nyata yang terjadi ke vendor pelayaran.

### 3.6 Modul Traffic, Dispatch Armada & Lapangan
- **Ketiadaan Entitas Master Armada & Supir:**  
  Repositori tidak memiliki model `Driver`, `Vehicle`, `Fleet`, atau `Truck`.  
  Pada `TrafficWorkflowController::gateOut` (baris 87–111), informasi armada diterima sebagai teks bebas:
  ```php
  $validated = $request->validate([
      'truck_plate_number' => 'required|string|max:20',
      'driver_name' => 'required|string|max:100',
      'surat_jalan_number' => 'required|string|max:100',
  ]);
  ```
  Data ini tidak divalidasi ke tabel master armada mana pun dan tidak tersimpan ke kolom terdedikasi di tabel `shipments`. Tidak ada integrasi pelacakan GPS telematika real-time.

### 3.7 Modul Human Resource Management (HRM) & Absensi
- **Penyimpanan Foto Selfie Absensi di Folder Publik (Pelanggaran UU PDP):**  
  Pada `StaffPortalController.php` baris 296 dan 380:
  ```php
  $destDir = public_path('uploads/attendances');
  if (!file_exists($destDir)) {
      mkdir($destDir, 0755, true);
  }
  file_put_contents($destDir . '/' . $imageName, $imageData);
  $attendance->photo_url = asset('uploads/attendances/' . $imageName);
  ```
  *Temuan Kritis:* Foto biometrik wajah karyawan disimpan di folder publik tanpa enkripsi dan dapat diakses siapa pun di internet tanpa login.
- **Skor Pencocokan Wajah Palsu (Hardcoded Face Match):**  
  Pada `StaffPortalController.php` baris 315 dan 398, kolom `face_match` diisi nilai statis:
  ```php
  'face_match' => 0.96, // Clock In
  'face_match' => 0.95, // Clock Out
  ```
  Dan pada `HrmController.php` baris 372:
  ```php
  'face_match' => $validated['face_match'] ?? 0.965,
  ```
  Tidak ada model machine learning, OpenCV, AWS Rekognition, atau ekstraksi vektor biometrik di repositori.
- **Ketiadaan Validasi Consent Biometrik pada Portal Staf:**  
  Meskipun `HrmController::clockAttendance` memeriksa `$employee->hasValidBiometricConsent()`, pada `StaffPortalController::clockIn` dan `clockOut` (tempat di mana karyawan operasional sebenarnya melakukan absensi mandiri setiap hari), **sama sekali tidak ada pengecekan consent**. Karyawan dapat mengunggah foto tanpa persetujuan biometrik yang sah.

### 3.8 Modul Penggajian (Payroll) & Pajak Karyawan
- **Implementasi Matematis TER PMK 168/2023:**  
  `TaxTerCalculatorService.php` mengimplementasikan 3 kategori TER secara penuh:
  - Kategori A (`TK/0`, `TK/1`, `K/0`): 44 lapisan tarif (0% s.d. 34%).
  - Kategori B (`TK/2`, `TK/3`, `K/1`, `K/2`): 40 lapisan tarif (0% s.d. 34%).
  - Kategori C (`K/3`): 41 lapisan tarif (0% s.d. 35%).
  - Plafon BPJS Ketenagakerjaan JP per Maret 2026 disetel ke `Rp 11.086.300`.
  - Gross taxable income secara tepat memperhitungkan premi asuransi dibayar perusahaan (JKK, JKM, BPJS Kesehatan ER) serta penalti non-NPWP sebesar 20%.
- **Ketiadaan Rekonsiliasi Tahunan & Formulir 1721-A1:**  
  Sistem hanya menghitung PPh 21 bulanan (Januari–November). Mekanisme rekonsiliasi Masa Pajak Desember (perhitungan pajak setahun dengan tarif progresif Pasal 17 ayat (1) huruf a UU PPh, pengurang Biaya Jabatan maksimal Rp6.000.000/tahun, pengurang PTKP setahun, dan penerbitan bukti potong Form 1721-A1) **sama sekali tidak ditemukan di repositori**.
- **Bug Fatal Variabel Belum Terdefinisi:**  
  Pada `AccountingService.php` baris 418:
  ```php
  public function recordPayrollDisbursement(PayrollRun $run, string $bankAccountId, ?string $disbursedById = null): JournalEntry
  {
      if (AccountingPeriod::isDateLocked($entryDate)) { // BUG: $entryDate belum diinisialisasi!
          throw new DomainException("Periode akuntansi untuk tanggal {$entryDate} telah dikunci.");
      }
      $entryDate = now()->toDateString(); // Variabel baru dibuat di baris 464!
  ```
  Hal ini memicu error fatal `Undefined variable $entryDate` setiap kali fungsi pencairan gaji dieksekusi. Hal serupa terjadi pada baris 371 (`recordOpeningBalance`).

### 3.9 Modul Keuangan Operasional (Finance) & Kas/Bank
- **Pemisahan Talangan vs Jasa Kena Pajak:**  
  `ClientInvoiceController.php` dan `ShipmentWorkflowService::generateClientInvoice` memisahkan secara tepat antara pos *Reimbursable Customs Outlays* (bea masuk, PPN impor, PPh pasal 22, biaya penumpukan TPS) yang tidak terutang PPN, dengan pos *Freight & Handling Services* yang dikenakan PPN 1.1% sesuai PMK 71/2022.
- **Pengelolaan Rekening Kas/Bank:**  
  `CashBankController.php` mengelola akun bank operasional perusahaan (Bank BCA Operasional, Bank Mandiri MPN G3 Bea Cukai, Kas Kecil) dengan tracking saldo, transfer antar-kas, dan pencatatan mutasi otomatis dari pelunasan invoice klien dan pembayaran billing pabean.

### 3.10 Modul Akuntansi (Accounting) & Penguncian Buku (Lock Period)
- **Pembukuan Berpasangan (Double-Entry Balance):**  
  `AccountingService::createBalancedEntry` memvalidasi keseimbangan total debit dan kredit dengan toleransi pembulatan $0.05$ (`abs($totalDebit - $totalCredit) <= 0.05`).
- **Mekanisme Penutupan Buku & Jurnal Penutup:**  
  `AccountingController::closePeriod` (baris 393–487) menghitung laba/rugi bersih seluruh akun pendapatan (4-xxxx) dan beban (5-xxxx), memposting jurnal penutup ke Laba Ditahan (`3-2100`), dan mengunci status periode (`is_closed = true`). Pembukaan kembali (*reopen period*) dilindungi hak akses pimpinan `R-OWN`.
- **Celah Penguncian Buku (Lock Period Loopholes):**  
  1. *Ketiadaan Model Event Observer:* Penguncian periode hanya dicek di level service dan form controller tertentu. Jika kode memanggil `$journalEntry->delete()` atau mengubah relasi `lines` secara langsung via Eloquent, penguncian periode terlewati (*bypassed*).
  2. *Exception Swallowed pada Invoicing:* Pada `ClientInvoiceController.php` baris 106–111, ketika pembuatan auto-journal gagal karena periode akuntansi terkunci, exception ditangkap dan hanya dicatat sebagai `Log::warning(...)`. Akibatnya, invoice operasional berhasil terbit tetapi buku besar akuntansi menjadi tidak sinkron (*out-of-sync*).

### 3.11 Modul Customer Relationship Management (CRM) & WABA
- **Simulasi Meta Cloud API:**  
  Pada `CrmController.php` baris 162, terdapat komentar verbatim pengembang:
  ```php
  // Simulasi integrasi Meta Cloud API
  ```
- **Ketiadaan Panggilan HTTP Nyata pada WhatsAppController:**  
  Method `sendTest` (baris 153–167), `storeBroadcast` (baris 73–118), dan `sendWhatsAppMessage` pada `InboxController.php` hanya memasukkan data ke tabel `crm_messages` dan `whatsapp_broadcast_recipients` dengan status `'sent'` tanpa pernah memanggil endpoint Meta Graph API.
- **Ketiadaan Webhook Handler:**  
  Tidak ada endpoint verifikasi webhook token (`hub.verify_token`) maupun penerima payload pesan masuk (*incoming message*) dari Meta WhatsApp.
- **Metrik Media Sosial Acak:**  
  Pada `CrmController.php` baris 310 dan 364, data impresi, reach, dan engagement di-generate menggunakan `rand(150, 400)` dan `rand(500, 3500)`.

### 3.12 Modul AI Agent & Model Context Protocol (MCP) Server
- **Client OpenAI-Compatible & Intelijen Kepabeanan:**  
  `OpenAiCustomsService.php` (baris 8–683) dan `config/ai.php` mengimplementasikan koneksi cURL lengkap ke endpoint AI mandiri (`https://r4g77gv.abc-tunnel.us/v1`), mendukung streaming Server-Sent Events (SSE), fallback rantai model multi-tier (`cx/gpt-6-astra`, `cx/gpt-5.6-terra`, `cx/gpt-5.6-sol`, `ag/gemini-3.8-flash-high`), klasifikasi pos tarif 8-digit BTKI 2022, ekstraksi berkas packing list, dan alur validasi *Human-in-the-Loop* sebelum data diaplikasikan ke dokumen pabean.
- **Server Model Context Protocol (MCP):**  
  `McpCustomsAgentController.php` (baris 1–500) mengimplementasikan spesifikasi resmi MCP (versi `2024-11-05`) dengan protokol ganda SSE (`/api/mcp/sse`) dan JSON-RPC 2.0 (`/api/mcp/execute`). Sebanyak 29 berkas kelas domain (27 tools) terdaftar di `app/Domain/Mcp/Tools/`, mencakup aksi inspeksi kepabeanan, operasional, keuangan, dan HRM. Aksi berbahaya seperti penghapusan data (`McpDeleteRecordTool`) atau persetujuan mutasi (`McpApproveActionTool`) diwajibkan melalui tiket persetujuan (*approval tickets*).

### 3.13 Modul Keamanan: RBAC 12 Roles & Audit Trail
- **12 Peran Organisasi:**  
  `DatabaseSeeder.php` baris 24–36 mendaftarkan seluruh 12 peran resmi:
  `R-ADM` (Admin Sistem), `R-OWN` (Pimpinan), `R-AM` (Account Manager), `R-DOC` (Staff Dokumen), `R-DOC-SPV` (Supervisor Dokumen), `R-DO` (Staff DO), `R-TRF` (Staff Traffic), `R-FLD` (Staff Lapangan), `R-MGR` (Manager Tim), `R-FIN` (Finance), `R-HR` (HR), dan `R-CS` (Customer Service).
- **Penegakan Hak Akses di Controller:**  
  Setiap controller mengimplementasikan `HasMiddleware` dengan middleware otentikasi serta pemanggilan `Gate::authorize()` di dalam fungsi mutasi data.
- **Audit Trail Forensik:**  
  Trait `Auditable.php` mengikat event `created`, `updated`, dan `deleted` pada 16 model entitas. Namun, sistem belum memiliki antarmuka Audit Log Viewer di Admin Panel untuk memudahkan Owner mencari dan memfilter log mutasi data.

### 3.14 Modul Mesin Cetak Dokumen PDF Resmi
- `PdfController.php` bersama 6 template Blade di `resources/views/pdf/` (`pib.blade.php`, `peb.blade.php`, `invoice.blade.php`, `quotation.blade.php`, `do_request.blade.php`, `billing_mpn.blade.php`) berhasil merender dokumen cetak resmi berstandar A4 dengan barcode 26-digit, kolom perhitungan bea dan pajak, serta nomor NTPN/MPN G3.

---

## 4. EVALUASI KEPATUHAN REGULASI, ATURAN BISNIS, DAN KEAMANAN

### 4.1 Kepatuhan UU Pelindungan Data Pribadi (UU No. 27 Tahun 2022)
1. **Penyimpanan Data Biometrik Terbuka (Pasal 27 & 35 UU PDP):**  
   Data biometrik wajah diklasifikasikan sebagai **Data Pribadi yang Bersifat Spesifik** (Pasal 4 ayat (2) huruf c UU PDP). Penempatan berkas foto absensi pada `public_path('uploads/attendances')` merupakan bentuk kegagalan serius dalam menjamin keamanan data pribadi (*data breach vulnerability*). Berkas wajib dipindahkan ke penyimpanan privat (`storage/app/private/biometrics/`) dan dienkripsi saat diam (*encryption at rest*).
2. **Kewajiban Persetujuan Pemrosesan (Pasal 20 & 22 UU PDP):**  
   Pengendali data pribadi wajib memiliki dasar pemrosesan yang sah berupa persetujuan tertulis atau elektronik yang terekam eksplisit. Ketidakhadiran validasi `BiometricConsent` pada method `StaffPortalController::clockIn` dan `clockOut` melanggar asas legalitas pemrosesan data biometrik.
3. **Penyusutan & Retensi Data (Pasal 43 UU PDP):**  
   Meskipun kolom `retention_until` telah tersedia pada tabel `biometric_consents`, sistem belum memiliki scheduler otomatis (*automated retention worker*) untuk memusnahkan data biometrik dan foto absensi yang telah melewati batas simpan 2 tahun.

### 4.2 Kepatuhan Pajak Penghasilan TER PMK 168/2023 & Batas BPJS 2026
1. **Penerapan Tarif Efektif Rata-Rata (TER):**  
   PATS_CEISA telah 100% mematuhi formula TER bulanan sesuai PMK No. 168 Tahun 2023. Pemetaan status PTKP ke Kategori A, B, dan C serta implementasi 125 lapisan tarif bruto berjalan secara tepat.
2. **Plafon Upah Jaminan Pensiun (JP) BPJS Ketenagakerjaan 2026:**  
   Konstanta `BPJS_JP_MAX_BASE` telah diperbarui ke nilai Rp 11.086.300 (efektif penyesuaian inflasi Maret 2026).
3. **Ketiadaan Rekonsiliasi Tahunan (Pasal 17 UU PPh):**  
   Sistem belum memenuhi ketentuan pemotongan PPh 21 Masa Pajak Terakhir (Desember), di mana perusahaan wajib menghitung ulang seluruh penghasilan neto setahun, dikurangi PTKP setahun, dikenakan tarif progresif Pasal 17 ayat (1) huruf a UU PPh, dan memperhitungkan kredit pajak TER masa Januari–November. Bukti potong resmi Formulir 1721-A1 wajib diintegrasikan sebelum sistem digunakan untuk penutupan tahun pajak.

### 4.3 Integritas Akuntansi & Mekanisme Penguncian Periode (Lock Period)
1. **Prinsip Tutup Buku Akuntansi:**  
   Fungsi `AccountingController::closePeriod` telah memenuhi standar akuntansi keuangan dengan menerbitkan jurnal penutup penyeimbang pendapatan dan beban ke akun Laba Ditahan (`3-2100`).
2. **Penegakan Model-Level Guard:**  
   Pencegahan posting transaksi pada periode terkunci saat ini hanya ditegakkan di sebagian controller. Pengamanan wajib diturunkan ke level Eloquent Model Observer pada model `JournalEntry`, `JournalLine`, `ClientInvoice`, dan `VendorBill` agar tidak ada celah manipulasi data melalui jalur API, bulk update, atau artisan command.
3. **Eliminasi Pengabaian Exception:**  
   `ClientInvoiceController` dilarang menangkap `DomainException` dari `AccountingService` secara pasif; pembuatan invoice operasional wajib dibatalkan (*rollback*) jika periode akuntansi tanggal tersebut telah berstatus terkunci.

### 4.4 Standar Open API Gateway CEISA 4.0 DJBC (Katalog 1–6)
1. **Validasi Skema Dokumen JSON (BC 2.0 & BC 3.0):**  
   Struktur Flat Root yang diimplementasikan pada `DocumentBC20Service.php` dan `DocumentBC30Service.php` telah mematuhi spesifikasi JSON Schema Draft-07 resmi Bea Cukai.
2. **Siklus Hidup Token & Caching:**  
   Sistem telah mematuhi aturan masa kedaluwarsa token DJBC dengan menerapkan pembaruan token berkala ke `/user/update-token` sebelum token habis (*proactive renewal* dengan cache TTL 4 menit).
3. **Penyelarasan Katalog Layanan:**  
   Untuk mencapai integrasi kepabeanan penuh, modul operasional perlu melengkapi integrasi ke OpenAPI Katalog 3 (Manifes Kapal BC 1.1) dan Katalog 4 (TPS Online Gate Out).

### 4.5 Penegakan Controller-Level RBAC 12 Roles & Jejak Forensik
1. **Pemisahan Tugas (Segregation of Duties):**  
   Sistem telah memisahkan dengan tegas kewenangan antar divisi (Staff Dokumen `R-DOC` menyusun draf, Supervisor `R-DOC-SPV` menyetujui, Finance `R-FIN` membayar billing, Traffic `R-TRF` menangani gate out).
2. **Perbaikan Seeder Role Deadlock:**  
   Nilai `'approver_role' => 'R-SPV'` pada `StagePrerequisiteSeeder.php` wajib segera diganti dengan `'R-DOC-SPV'` agar kewenangan supervisor dokumen dalam alur lompat tahap (F16) dapat berfungsi secara operasional.

---

## 5. ANALISIS EKSEKUSI AUTOMATED TEST SUITE

### 5.1 Inventarisasi & Metrik Eksekusi Pengujian Otomatis
Pemeriksaan menyeluruh pada direktori `tests/` mengidentifikasi total **24 berkas test** dengan **116 method pengujian otomatis**:

```
tests/
├── Unit/                          (3 berkas, 10 tests)
│   ├── ExampleTest.php            (1 test)
│   ├── HrmModelTest.php           (5 tests)
│   └── OpenAiCustomsServiceTest.php (4 tests)
└── Feature/                       (21 berkas, 106 tests)
    ├── AdminPanelRoutesTest.php   (1 test)
    ├── Bc20CompleteLifecycleTest.php (3 tests)
    ├── Bc20WizardAndPreviewTest.php (2 tests)
    ├── BulkDraftAndDeleteAssetsTest.php (8 tests)
    ├── BundleTrackingAndInspectionTest.php (2 tests)
    ├── CeisaSyncAndCallbackTest.php (12 tests)
    ├── ClientInvoiceTest.php      (2 tests)
    ├── ClientPortalTokenWorkflowTest.php (6 tests)
    ├── CrmTest.php                (3 tests)
    ├── CustomsTrackingIntegrationTest.php (6 tests)
    ├── DocumentEditAndRollbackPermissionsTest.php (6 tests)
    ├── ExampleTest.php            (1 test)
    ├── HrmFeatureTest.php         (6 tests)
    ├── LoginOptimizationAndOtpTest.php (6 tests)
    ├── PdfAndWorkflowTest.php     (4 tests)
    ├── ProjectTrackingTest.php    (5 tests)
    ├── RbacAuthenticationTest.php (10 tests)
    ├── SettingCeisaDatabaseTest.php (2 tests)
    ├── Sprint3And4ModulesTest.php (3 tests)
    ├── TimDocumentWorkflowTest.php (13 tests)
    └── WeeklyMondaySessionResetTest.php (5 tests)
```

Eksekusi Unit Test Suite menghasilkan kelulusan mutlak:
- Perintah: `vendor/bin/phpunit tests/Unit`
- Hasil: **10 tests, 50 assertions, 10 Passed, 0 Failed (100% Pass Rate)** dalam durasi 104 detik.

### 5.2 Otentisitas Pengujian: Authentic Behavioral Tests vs Mock
Analisis mendalam terhadap struktur pengujian pada `Bc20CompleteLifecycleTest.php`, `BulkDraftAndDeleteAssetsTest.php`, `TimDocumentWorkflowTest.php`, dan `DocumentEditAndRollbackPermissionsTest.php` membuktikan bahwa test suite yang ada adalah **pengujian integrasi otentik (Authentic Behavioral Tests)**:
1. Menguji persistensi basis data relasional secara nyata menggunakan assersi `assertDatabaseHas` dan `assertDatabaseMissing`.
2. Menguji integritas transaksi database (rollback dan commit multi-tahap dari P01 hingga P19).
3. Menguji manipulasi berkas fisik pada filesystem publik menggunakan `Storage::disk('public')->assertExists()` dan `assertMissing()`.
4. Menguji penegakan aturan 3-way match dokumen pabean sebelum izin pengiriman CEISA diberikan.
5. Menguji respon HTTP 403 Forbidden secara nyata pada upaya pelanggaran peran dan pengeditan shipment terkunci.

### 5.3 Analisis Akar Masalah Kegagalan Automated Tests
Meskipun unit test lulus 100%, beberapa feature test mengalami kegagalan saat dieksekusi dengan akar masalah berikut:

1. **RouteNotFoundException pada Indeks Dokumen:**
   - *Lokasi:* `resources/views/core/document_index.blade.php:39`
   - *Kutipan Kode:* `<form action="{{ route('core.document.sync-all-ceisa') }}" method="POST" class="inline">`
   - *Akar Masalah:* Rute resmi pada `routes/web.php` baris 289 didefinisikan dengan nama `config.sync-all` di dalam prefix grup `config`. Karena nama rute `core.document.sync-all-ceisa` tidak pernah didaftarkan, pemanggilan view ini memicu fatal error `Symfony\Component\Routing\Exception\RouteNotFoundException: Route [core.document.sync-all-ceisa] not defined`.
   - *Dampak:* Mengakibatkan kegagalan HTTP 500 pada test suite `AdminPanelRoutesTest`, `CeisaSyncAndCallbackTest`, dan `BundleTrackingAndInspectionTest`.

2. **Truncation String Nomor Aju pada Test CRM:**
   - *Lokasi:* `tests/Feature/CrmTest.php:28`
   - *Kutipan Kode:* `'nomor_aju' => '040300-123456-20261020-000001'` (29 karakter).
   - *Akar Masalah:* Skema tabel database `ceisa_documents` pada migrasi `2026_10_12_000001_create_ceisa_tables.php:16` menetapkan kolom `nomor_aju` dengan panjang maksimal 26 karakter (`$table->string('nomor_aju', 26)`). Input test sepanjang 29 karakter memicu `SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column 'nomor_aju' at row 1`.

3. **Assertion Mismatch Slot Dokumen Checklist:**
   - *Lokasi:* `tests/Feature/Bc20CompleteLifecycleTest.php:121`
   - *Kutipan Kode:* `$this->assertEquals(9, $shipment->documents()->count());`
   - *Akar Masalah:* Inisialisasi slot dokumen pada `ShipmentWorkflowService` versi v2.0 telah diperluas menjadi 11 slot bawaan ditambah berkas pengujian sehingga menghasilkan 14 berkas, sedangkan unit test masih mengasumsikan 9 slot dokumen PRD awal.

4. **Karakteristik Tabrakan Tabel RefreshDatabase MySQL:**
   - *Akar Masalah:* Pada `phpunit.xml`, pengujian dikonfigurasi menggunakan koneksi MySQL ke basis data bersama `PATS_CEISA`. Trait `RefreshDatabase` pada MySQL mengeksekusi perintah DDL non-transaksional (`DROP TABLE`, `CREATE TABLE`) yang memicu implicit commit. Eksekusi paralel beruntun menimbulkan *race condition* dan kegagalan foreign key constraint antar-tabel.

### 5.4 Analisis Kesenjangan Cakupan Pengujian (0% Coverage Areas)
Pemeriksaan cakupan pengujian mengidentifikasi area bisnis krusial yang memiliki **0% automated test coverage**:
- **Buku Besar Akuntansi (General Ledger & Lock Period):** `app/Http/Controllers/AccountingController.php` (0 test files).
- **Manajemen Kas & Bank:** `app/Http/Controllers/CashBankController.php` (0 test files).
- **Hutang Usaha / Vendor Bills (AP):** `app/Http/Controllers/VendorBillController.php` (0 test files).
- **Manajemen Pajak Perusahaan:** `app/Http/Controllers/TaxController.php` (0 test files).
- **Unit Test Matematis TER PMK 168/2023:** Tidak ada unit test spesifik yang menguji 125 lapisan tarif A/B/C secara matematis di luar integrasi payroll umum.
- **WhatsApp Omnichannel WABA:** `WhatsAppController.php` dan `InboxController.php` (0 test files).
- **MCP Server Protocol:** Pengujian format pesan JSON-RPC 2.0 dan streaming SSE pada `McpCustomsAgentController.php` (0 test files).

---

## 6. PRIORITIZED REMEDIATION ROADMAP (PETA JALAN PERBAIKAN)

Rencana aksi perbaikan disusun dalam 3 fase berbasis prioritas tingkat keparahan risiko bisnis:

```
┌────────────────────────────────────────────────────────────────────────┐
│ FASE 1: PERBAIKAN CEPAT & HOTFIX KRITIS (Minggu 1)                     │
│ Target: Stabilitas Aplikasi, Eliminasi Crash 500 & Deadlock Seeder     │
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │
                                   ▼
┌────────────────────────────────────────────────────────────────────────┐
│ FASE 2: KEPATUHAN REGULASI & KEAMANAN DATA (Minggu 2 - 3)              │
│ Target: Penegakan UU PDP 27/2022, Enkripsi Biometrik & Lock Period     │
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │
                                   ▼
┌────────────────────────────────────────────────────────────────────────┐
│ FASE 3: FONDASI ARSITEKTUR INTI & PENYELESAIAN FITUR (Minggu 4 - 6)    │
│ Target: Background Queue F15, Form 1721-A1, Live WABA & Test Coverage  │
└────────────────────────────────────────────────────────────────────────┘
```

### 6.1 Fase 1: Perbaikan Cepat & Hotfix Kritis (Minggu 1)
- **Tugas 1.1: Perbaikan RouteNotFoundException pada View Dokumen**  
  *Lokasi:* `resources/views/core/document_index.blade.php:39`  
  *Aksi:* Ubah `route('core.document.sync-all-ceisa')` menjadi `route('config.sync-all')`.
- **Tugas 1.2: Perbaikan Deadlock Seeder Approval F16**  
  *Lokasi:* `database/seeders/StagePrerequisiteSeeder.php:39, 48`  
  *Aksi:* Ubah `'approver_role' => 'R-SPV'` menjadi `'approver_role' => 'R-DOC-SPV'`.
- **Tugas 1.3: Perbaikan Bug Undefined Variable `$entryDate` pada AccountingService**  
  *Lokasi:* `app/Services/Accounting/AccountingService.php:371, 418`  
  *Aksi:* Inisialisasi `$entryDate = now()->toDateString();` sebelum pemanggilan `AccountingPeriod::isDateLocked($entryDate)`.
- **Tugas 1.4: Perbaikan String Nomor Aju pada Test CRM**  
  *Lokasi:* `tests/Feature/CrmTest.php:28`  
  *Aksi:* Potong nomor aju menjadi 26 karakter numerik standar CEISA (`04030012345620261020000001`).
- **Tugas 1.5: Penyelarasan Assersi Slot Dokumen pada Lifecycle Test**  
  *Lokasi:* `tests/Feature/Bc20CompleteLifecycleTest.php:121`  
  *Aksi:* Perbarui assersi jumlah dokumen sesuai inisialisasi pipeline v2.0 (14 dokumen).

### 6.2 Fase 2: Kepatuhan Regulasi & Pengerasan Keamanan (Minggu 2–3)
- **Tugas 2.1: Relokasi & Enkripsi Berkas Foto Biometrik Absensi (UU PDP)**  
  *Lokasi:* `app/Http/Controllers/StaffPortalController.php:296, 380`, `app/Models/Employee.php`  
  *Aksi:* Pindahkan target penyimpanan foto dari `public_path('uploads/attendances')` ke disk privat `storage/app/private/attendances/`. Tambahkan `'face_data' => 'encrypted'` pada properti `$casts` di model `Employee`. Alihkan tampilan foto melalui rute streaming bertanda tangan (`PrivateMediaController::showBiometric`).
- **Tugas 2.2: Penegakan Validasi Biometric Consent pada Portal Staf**  
  *Lokasi:* `app/Http/Controllers/StaffPortalController.php::clockIn, clockOut`  
  *Aksi:* Tambahkan validasi:
  ```php
  if (!$employee->hasValidBiometricConsent()) {
      return back()->with('error', 'Persetujuan pemrosesan data biometrik (UU PDP No. 27/2022) wajib ditandatangani sebelum melakukan absensi kamera.');
  }
  ```
- **Tugas 2.3: Pembuatan Scheduled Task Pembersihan Retensi Data Biometrik**  
  *Lokasi:* `app/Console/Commands/PurgeExpiredBiometricsCommand.php`  
  *Aksi:* Buat artisan command berkala untuk menghapus foto absensi dan data wajah yang telah melewati masa retensi 2 tahun (`retention_until < now()`).
- **Tugas 2.4: Pengerasan Penguncian Periode Akuntansi di Level Model Event**  
  *Lokasi:* `app/Observers/JournalEntryObserver.php`, `app/Observers/ClientInvoiceObserver.php`  
  *Aksi:* Pasang observer pada event `creating`, `updating`, dan `deleting` yang melempar exception pemblokiran mutasi jika tanggal transaksi berada pada periode terkunci. Perbaiki `ClientInvoiceController` agar tidak mengabaikan error jurnal penutup.

### 6.3 Fase 3: Fondasi Arsitektur Inti & Penyelesaian Fitur (Minggu 4–6)
- **Tugas 3.1: Pembangunan Laravel Queue Jobs untuk F15 Batch Import**  
  *Lokasi:* `app/Jobs/Import/ExtractBatchZipJob.php`, `app/Jobs/Import/ProcessDocumentOcrJob.php`, `app/Jobs/Import/RunCrossValidationJob.php`  
  *Aksi:* Buat folder `app/Jobs` dan migrasikan perulangan `BatchImportService::createBatch` ke worker asinkron berbasis database/redis queue dengan indikator progres realtime di UI antarmuka.
- **Tugas 3.2: Implementasi Pengiriman Email Riil pada Rilis DO Pelayaran (P09)**  
  *Lokasi:* `app/Services/Shipment/ShipmentWorkflowService.php::sendVendorEmailForDo`  
  *Aksi:* Integrasikan dengan `Mail::to($recipient)->queue(new VendorDoRequestMail($shipment, $body))`, menyertakan lampiran berkas B/L secara otomatis.
- **Tugas 3.3: Implementasi Rekonsiliasi PPh 21 Tahunan & Formulir 1721-A1**  
  *Lokasi:* `app/Services/Hrm/AnnualTaxReconciliationService.php`, `resources/views/pdf/form_1721_a1.blade.php`  
  *Aksi:* Buat kalkulator PPh 21 Masa Desember berbasis tarif progresif Pasal 17 ayat (1) huruf a UU PPh, pengurang biaya jabatan tahunan maksimal Rp6 juta, akumulasi PTKP setahun, dan cetak bukti potong PDF 1721-A1.
- **Tugas 3.4: Integrasi Live WhatsApp Business API (WABA) Meta Cloud**  
  *Lokasi:* `app/Services/Crm/MetaWabaService.php`, `app/Http/Controllers/Api/WabaWebhookController.php`  
  *Aksi:* Hubungkan `WhatsAppController` ke endpoint resmi Graph API Meta (`https://graph.facebook.com/v19.0/{phone-number-id}/messages`), buat handler webhook dengan verifikasi token HMAC-SHA256, dan singkirkan nilai acak pada metrik analitik media sosial.
- **Tugas 3.5: Penambahan Automated Test Suite Modul Finansial & Akuntansi**  
  *Lokasi:* `tests/Feature/AccountingGeneralLedgerTest.php`, `tests/Feature/CashBankTreasuryTest.php`, `tests/Feature/LockPeriodEnforcementTest.php`, `tests/Unit/TaxTerMathematicalBracketsTest.php`  
  *Aksi:* Tulis rangkaian test suite baru untuk menaikkan cakupan pengujian modul akuntansi dari 0% menjadi minimal 85%.

### 6.4 Matriks Definition of Done (DoD) & Acceptance Criteria

| Target Modul / Komponen | Kriteria Penerimaan (Acceptance Criteria) | Metode Verifikasi |
| :--- | :--- | :--- |
| **Hotfix Route View Dokumen** | Halaman `/core/documents` terbuka dengan status HTTP 200 tanpa error `RouteNotFoundException`. | `vendor/bin/phpunit --filter AdminPanelRoutesTest` |
| **Hotfix Seeder Approval F16** | Permintaan lompat tahap ke Pos 3 & Pos 4 dapat di-approve oleh pengguna bersertifikat `R-DOC-SPV`. | Unit test & Tinker role verification |
| **Kepatuhan UU PDP Absensi** | Foto kamera tersimpan di direktori privat; URL publik `/uploads/attendances/...` mengembalikan 404/403. Clock-in ditolak jika consent biometrik belum tervalidasi. | Audit HTTP request & unit test permission |
| **Asinkron F15 Batch Import** | Upload berkas ZIP berisi 20 dokumen pabean mengembalikan respons HTTP dalam < 3 detik; ekstraksi OCR diproses di background queue. | Monitoring antrean queue `php artisan queue:work` |
| **Email Rilis DO (P09)** | Mengirim email resmi dengan lampiran B/L ke alamat vendor pelayaran; tercatat di log mail Laravel. | `Mail::fake()` test assertion |
| **Rekonsiliasi Pajak 1721-A1** | Payroll Masa Desember menghitung PPh 21 progresif setahun; Form 1721-A1 dapat diunduh dalam format PDF resmi. | Unit test perhitungan PPh 21 vs kalkulator DJP |
| **Integritas Lock Period** | Operasi mutasi jurnal atau penagihan pada periode berstatus `is_closed = true` diblokir 100% oleh model observer. | `assertThrows(DomainException::class)` |
| **Skor Verifikasi Otomatis** | Seluruh 14 pemeriksaan forensik pada script reproduksi lolos dengan skor kelulusan 100%. | `powershell scripts\verify_feature_audit.ps1` |

### 6.5 Daftar Lengkap Item yang Perlu Diperbaiki (Actionable Repair Backlog)

Berdasarkan seluruh temuan audit kode sumber, pengujian otomatis, analisis regulasi, kepatuhan UI/UX, dan manajemen risiko, berikut adalah matriks inventarisasi item tindakan perbaikan terstruktur yang harus diselesaikan oleh tim pengembang:

#### Kategori A: Bug Kritis & Kerusakan Kode Sumber (Code Defect & Crashes)
| ID Item | Modul Terkait | Lokasi Berkas & Baris | Tingkat Keparahan | Deskripsi Masalah | Tindakan Perbaikan Konkret |
| :---: | :--- | :--- | :---: | :--- | :--- |
| **FIX-01** | Core Dokumen | `resources/views/core/document_index.blade.php:39` | **Critical** | Memanggil rute tidak terdefinisi `core.document.sync-all-ceisa`, memicu crash HTTP 500 fatal bagi pengguna berizin `setting.manage`. | Ganti pemanggilan rute menjadi `route('config.sync-all')` sesuai deklarasi rute resmi di `routes/web.php:289`. |
| **FIX-02** | Lompat Tahap F16 | `database/seeders/StagePrerequisiteSeeder.php:39, 48` | **Major** | Seeder mensyaratkan kode peran `'approver_role' => 'R-SPV'`, padahal kode peran resmi di database adalah `R-DOC-SPV` (deadlock persetujuan). | Ubah nilai string `'R-SPV'` menjadi `'R-DOC-SPV'` pada kedua baris seeder agar supervisor dokumen dapat menyetujui tiket lompat tahap. |
| **FIX-03** | Akuntansi / GL | `app/Services/Accounting/AccountingService.php:371, 418` | **Major** | Variabel `$entryDate` digunakan pada `AccountingPeriod::isDateLocked($entryDate)` tanpa diinisialisasi terlebih dahulu, memicu `ErrorException: Undefined variable $entryDate`. | Inisialisasi `$entryDate = $entryDate ?? now()->toDateString();` pada method `recordClientInvoiceApproved` dan `recordVendorBillApproved`. |
| **FIX-04** | CRM / Test Suite | `tests/Feature/CrmTest.php:28` | **Minor** | String nomor aju pabean berisi 29 karakter, melanggar batas kolom skema MySQL `VARCHAR(26)` (`SQLSTATE 22001`). | Sesuaikan format nomor aju menjadi 26 digit numerik standar CEISA (`04030012345620261020000001`). |
| **FIX-05** | Test Suite Pabean | `tests/Feature/Bc20CompleteLifecycleTest.php:121` | **Minor** | Assersi jumlah checklist dokumen pabean masih mengasumsikan 9 berkas lama, padahal pipeline v2.0 telah diperluas ke 14 berkas. | Perbarui assersi pengujian menjadi `$this->assertEquals(14, $shipment->documents()->count());`. |

#### Kategori B: Kepatuhan Hukum, Privasi Data & Regulasi (UU PDP No. 27/2022)
| ID Item | Modul Terkait | Lokasi Berkas & Baris | Tingkat Keparahan | Deskripsi Masalah | Tindakan Perbaikan Konkret |
| :---: | :--- | :--- | :---: | :--- | :--- |
| **FIX-06** | HRM / Privasi | `app/Http/Controllers/StaffPortalController.php:296, 380` | **Critical** | Berkas foto absensi selfie disimpan ke direktori webroot publik `public/uploads/attendances/` tanpa enkripsi (ancaman denda 2% omzet UU PDP). | Pindahkan penyimpanan ke disk privat `storage/app/private/attendances/`. Sajikan foto hanya melalui signed streaming controller (`PrivateMediaController`). |
| **FIX-07** | HRM / Pegawai | `app/Models/Employee.php:37` | **Critical** | Kolom representasi biometrik wajah `face_data` disimpan dalam bentuk teks biasa (plain text) di database MySQL. | Tambahkan cast enkripsi `'face_data' => 'encrypted'` pada properti `$casts` di model `Employee` agar terenkripsi AES-256 secara native. |
| **FIX-08** | HRM / Portal | `app/Http/Controllers/StaffPortalController.php::clockIn, clockOut` | **Critical** | Formulir clock-in harian portal staf tidak memvalidasi status persetujuan pemrosesan data biometrik (`BiometricConsent`). | Tambahkan validasi: jika `$employee->hasValidBiometricConsent()` bernilai false, tolak absensi dan alihkan ke form persetujuan privasi. |
| **FIX-09** | HRM / Data Purge | `app/Console/Kernel.php`, `app/Console/Commands/` | **Major** | Belum ada mekanisme pemusnahan berkas foto dan data biometrik setelah melewati batas masa retensi wajib 2 tahun. | Buat artisan command `biometric:purge-expired` dan jadwalkan berjalan harian pada cron Laravel (`$schedule->command('...')->daily()`). |

#### Kategori C: Penggantian Simulasi (Mock Stub) Menjadi Fungsionalitas Riil
| ID Item | Modul Terkait | Lokasi Berkas & Baris | Tingkat Keparahan | Deskripsi Masalah | Tindakan Perbaikan Konkret |
| :---: | :--- | :--- | :---: | :--- | :--- |
| **FIX-10** | DO Pelayaran (P09)| `app/Services/Shipment/ShipmentWorkflowService.php:737-772` | **Major** | Method `sendVendorEmailForDo` hanya merangkai teks email di memori tanpa memanggil fungsi pengiriman email riil (`Mail::send`). | Hubungkan ke mailer resmi: `Mail::to($shipment->shippingVendor->email)->queue(new VendorDoRequestMail($shipment))` lengkap dengan lampiran B/L. |
| **FIX-11** | HRM / Biometrik | `app/Http/Controllers/StaffPortalController.php:315, 398` | **Critical** | Pencocokan wajah menggunakan skor kecocokan *hardcoded* (`0.95`, `0.965`) tanpa engine verifikasi biometrik di server. | Pasang library pencocokan vektor wajah berbasis WebAssembly (MediaPipe/face-api.js) di sisi klien atau engine biometrik server-side. |
| **FIX-12** | CRM / WhatsApp | `app/Http/Controllers/WhatsAppController.php`, `CrmController.php:162` | **Major** | Integrasi WhatsApp WABA berstatus simulasi lokal (menulis pesan ke DB tanpa memanggil Meta API) dan tidak ada webhook. | Hubungkan ke Meta Cloud Graph API v19 (`POST https://graph.facebook.com/...`) dan buat webhook receiver resmi dengan validasi signature HMAC. |
| **FIX-13** | Media Sosial | `app/Http/Controllers/SocialMediaController.php:152-161` | **Minor** | Metrik analitik postingan (*impressions, reach, likes*) menggunakan generator angka acak PHP `rand(300, 750)`. | Ganti dengan metrik riil dari insight API masing-masing platform atau beri label visual transparan *"Simulasi Estimasi Jangkauan"*. |

#### Kategori D: Pembangunan Fitur yang Belum Tersedia (Missing Features)
| ID Item | Modul Terkait | Target Lokasi Implementasi | Tingkat Keparahan | Deskripsi Masalah | Tindakan Perbaikan Konkret |
| :---: | :--- | :--- | :---: | :--- | :--- |
| **FIX-14** | F15 Batch Import | `app/Jobs/Import/ExtractBatchZipJob.php`, `ProcessDocumentOcrJob.php` | **Critical** | Direktori `app/Jobs` tidak ada; pemrosesan ekstraksi ZIP massal dan OCR berjalan 100% sinkron di thread HTTP web (risiko timeout). | Buat direktori `app/Jobs` dan migrasikan proses batch import ke background queue worker dengan progress polling via AJAX/WebSocket. |
| **FIX-15** | Payroll / Pajak | `app/Services/Hrm/AnnualTaxReconciliationService.php`, `resources/views/pdf/form_1721_a1.blade.php` | **Major** | Tidak ada rekonsiliasi PPh 21 tahunan Masa Desember (Pasal 17 progresif) dan ketiadaan Formulir Bukti Potong 1721-A1 PDF. | Kembangkan service rekonsiliasi pajak tahunan, hitung selisih lebih/kurang potong, dan sediakan template cetak PDF Form 1721-A1 resmi. |
| **FIX-16** | Master Klien | `app/Http/Controllers/CrmController.php`, `routes/web.php:190` | **Major** | Master klien hanya memiliki fitur tambah (`storeClient`); tidak ada rute dan form untuk Edit, Update, Nonaktifkan, dan Upload Legalitas. | Tambahkan resource routes: `crm.clients.edit`, `update`, `toggle-status`, serta modal upload berkas PDF NIB, NPWP, dan Surat Kuasa PPJK. |
| **FIX-17** | Master Vendor | `app/Http/Controllers/VendorController.php`, `resources/views/vendors/` | **Major** | Belum ada halaman khusus Menu Master Vendor (Shipping Lines & Trucking); data vendor hanya diinput lewat seeder database. | Buat Controller `VendorController` dengan fitur CRUD lengkap: nama vendor, kontak operasional DO, nomor rekening bank, dan evaluasi performa. |
| **FIX-18** | Finance / Kas | `app/Http/Controllers/FinanceController.php`, `app/Services/Finance/` | **High** | Tidak ada gerbang verifikasi dana talangan masuk (*Escrow Gate*) sebelum pembayaran billing pabean MPN G3 dieksekusi. | Buat sistem validasi: pembayaran billing MPN diblokir kecuali saldo talangan klien berstatus `FUNDS_CLEARED` atau ada approval khusus Direksi. |
| **FIX-19** | Pabean CEISA 4.0 | `app/Services/Ceisa/CeisaManifestService.php` | **Major** | Belum ada integrasi Host-to-Host untuk menarik data Manifes Kapal (BC 1.1 Inward) dan feed otomatis Gate Out TPS Online. | Bangun integrasi API CEISA Katalog 3 (Manifes) dan Katalog 4 (TPS Online) untuk otomasi pengisian pos manifes dan tanggal gate out. |
| **FIX-20** | DO & Logistik | `app/Console/Commands/CheckDemurrageAlertsCommand.php` | **High** | Tidak ada sistem peringatan dini otomatis (*Predictive Demurrage Ticker*) saat sisa free time kontainer mendekati batas kedaluwarsa. | Buat scheduler harian yang mengecek sisa free time kontainer dan mengirimkan notifikasi eskalasi (H-3, H-2, H-1) ke WhatsApp Manager Ops dan Klien. |

#### Kategori E: Perbaikan Akuntansi & Integritas Buku Besar
| ID Item | Modul Terkait | Lokasi Berkas & Baris | Tingkat Keparahan | Deskripsi Masalah | Tindakan Perbaikan Konkret |
| :---: | :--- | :--- | :---: | :--- | :--- |
| **FIX-21** | Akuntansi / Lock | `app/Observers/JournalEntryObserver.php`, `app/Observers/ClientInvoiceObserver.php` | **Major** | Penguncian buku bulanan belum diproteksi di level database model event, dan `ClientInvoiceController` menelan error jurnal jika periode terkunci. | Pasang model observer pada `JournalEntry` dan `JournalLine` untuk memblokir event `creating`, `updating`, dan `deleting` pada periode `is_closed = true`. |
| **FIX-22** | Akuntansi / QA | `tests/Feature/Accounting/` | **Major** | Modul Buku Besar, Jurnal Umum, Kas & Bank, dan Perpajakan memiliki 0% automated test coverage di `tests/`. | Susun test suite feature komprehensif: `AccountingGeneralLedgerTest`, `CashBankTreasuryTest`, `LockPeriodEnforcementTest`, dan `TaxAccountingTest`. |

#### Kategori F: Pengalaman Pengguna (UX) & Antarmuka Visual (UI)
| ID Item | Modul Terkait | Lokasi Berkas & Baris | Tingkat Keparahan | Deskripsi Masalah | Tindakan Perbaikan Konkret |
| :---: | :--- | :--- | :---: | :--- | :--- |
| **FIX-23** | UX Dokumen | `resources/views/bc20/wizard/`, `resources/views/bc30/` | **Major** | Ketiadaan fitur *Auto-Save Draft* per 15 detik dan ketiadaan *Dirty-Form Guard* pada formulir pabean panjang (risiko data hilang). | Pasang auto-save draf berkala ke database/IndexedDB dan pasang event listener `beforeunload` saat pengguna berpindah halaman tanpa menyimpan. |
| **FIX-24** | UX Performa | `resources/views/core/document_index.blade.php`, `document_show.blade.php` | **Major** | Berkas view monolitik raksasa (150 KB dan 135 KB) merender >2.000 elemen DOM sekaligus, memperlambat rendering browser. | Pecah view monolitik menjadi komponen Blade modular (`<x-core.document-table>`, `<x-core.wizard-modal>`) dengan modal lazy-loading AJAX. |
| **FIX-25** | UI Tipografi | `resources/views/core/document_index.blade.php`, `tracking_index.blade.php` | **Minor** | Kolom angka teknis (Nomor Aju 26-digit, Nomor Kontainer, HS Code, Devisa, PPN) menggunakan font sans non-tabular (angka tidak sejajar). | Tambahkan kelas CSS Tailwind `font-mono tabular-nums tracking-tight` pada seluruh sel tabel yang memuat data numerik pabean dan finansial. |
| **FIX-26** | UI Desain Token | `resources/views/layouts/bottom-tab-bar.blade.php:6, 11`, `topbar.blade.php` | **Minor** | Penggunaan hardcoded hex `#007AFF` (Apple Blue) dan `#1C1C1E` menyimpang dari aturan aksen tunggal brand Sky Blue (`#0284c7`). | Ganti nilai hex dengan token semantik Tailwind `text-sky-600`, `bg-sky-600`, dan `var(--color-surface)` sesuai `DESIGN_RULES_PATS_CEISA.md`. |
| **FIX-27** | UI/UX Mobile | `resources/views/layouts/bottom-tab-bar.blade.php:50-55` | **Minor** | Tombol menu pada navigasi mobile memicu drawer desktop horizontal lebar yang memotong layar ponsel. | Ubah interaksi tombol menu mobile agar memunculkan **iOS Native Bottom Sheet Modal** dengan opsi modul vertikal dan backdrop dismiss. |
| **FIX-28** | Keamanan Siber | `routes/web.php:41-45` (Rute Pelacakan Publik `/portal/track/{token}`) | **Minor** | Endpoint pelacakan publik kargo belum diproteksi middleware `throttle` (rentan serangan brute-force enumerasi token UUID). | Tambahkan middleware rate limiting `throttle:60,1` (maksimal 60 request per menit per alamat IP) pada seluruh grup rute `/portal`. |

---

---

## 7. AUDIT KELENGKAPAN FITUR SECARA USER EXPERIENCE (UX)

Evaluasi pengalaman pengguna (*User Experience*) dilakukan dengan mengukur kemudahan, kecepatan, kejelasan alur, efisiensi navigasi, serta ketahanan sistem terhadap kesalahan manusia (*human-error resistance*) di seluruh modul kerja PT Agung Trans Solusindo.

### 7.1 Evaluasi Ergonomi Alur Kerja (Workflow Ergonomics) P01–P19 & Cockpit 11 Tahap
1. **Perjalanan Pengguna Tim Dokumen (P01 s.d. P07):**
   * *Status:* Berfungsi baik pada tingkat konseptual, namun memiliki friksi operasional yang signifikan.
   * *Friction Analysis:* Pembuatan satu berkas PIB BC 2.0 mengharuskan staf pabean mengisi lebih dari 40 parameter masukan yang tersebar di 5 tab wizard (`_stage_header`, `_stage_entitas`, `_stage_pengangkut`, `_stage_dokumen`, `_stage_barang`). Perpindahan antar-tab belum dilengkapi indikator kelengkapan validasi instan per tab, sehingga staf baru mengetahui adanya field kosong setelah menekan tombol submit akhir.
   * *Cross-Functional Handoff:* Transisi kargo dari Pos Dokumen (P07) menuju Pos DO (P08) dan Pos Traffic (P13) belum memiliki mekanisme *in-app push alert* atau notifikasi antrean tugas realtime. Tim DO dan Traffic harus secara manual me-refresh halaman atau mengandalkan komunikasi eksternal (grup WhatsApp) untuk mengetahui kargo baru yang memerlukan tindakan.
2. **Cockpit Pelacakan Proyek 11 Pos (`tracking_index.blade.php`):**
   * *Kelebihan:* Menampilkan visualisasi matriks progres 11 pos kargo secara komprehensif, dilengkapi fungsi *Quick Update* status dokumen ala spreadsheet.
   * *Kelemahan UX:* Tabel dengan banyak kolom (>15 kolom status) belum memiliki fitur *frozen/sticky columns* untuk kolom identitas kargo (Nomor Aju & Nomor B/L). Saat staf menggulir ke kanan untuk melihat status Pos 9–11, konteks nomor shipment hilang dari pandangan, memaksa staf menggulir bolak-balik.

### 7.2 Zero Data Loss & Mekanisme Pencegahan Kesalahan (Error Prevention)
1. **Ketiadaan Auto-Save Draft pada Formulir Pabean Panjang:**
   * *Temuan:* Pada `bc20/wizard/_stage_barang.blade.php` dan `bc20/create.blade.php`, data yang diinput oleh pengguna hanya tersimpan di memori browser (form DOM) sebelum tombol simpan ditekan.
   * *Risiko UX:* Jika koneksi terputus, sesi PHP habis (*session timeout*), atau tab browser tertutup tanpa sengaja saat staf sedang menginput puluhan baris uraian barang, seluruh data hilang tanpa pemulihan (*Zero Data Loss violated*).
   * *Rekomendasi:* Pasang mekanisme *Debounced Auto-Save* ke tabel draf database atau penyimpanan lokal peramban (*IndexedDB/localStorage*) setiap 15 detik dengan indikator visual *"Draf otomatis tersimpan 14:32"*.
2. **Ketiadaan Peringatan Navigasi Form Belum Tersimpan (Dirty-Form Guard):**
   * *Temuan:* Tidak ada event listener `beforeunload` atau Alpine.js dirty state watcher saat pengguna mengklik menu sidebar saat formulir pengeditan sedang aktif terisi. Staf dapat berpindah halaman secara tidak sengaja dan kehilangan data input.
3. **Penyelarasan Histori Dokumen (Document Versioning):**
   * *Temuan:* Model `ShipmentDocument` telah memiliki atribut `version` dan `is_current`, namun antarmuka pengguna di `document_show.blade.php` belum menyajikan fitur perbandingan draf (*Diff Viewer*) antar versi dokumen atau tombol pemulihan dokumen versi sebelumnya (*Rollback Version*).

### 7.3 Beban Kognitif (Cognitive Load), View Monolitik & Ergonomi Keyboard
1. **Masalah View Monolitik Raksasa:**
   * `resources/views/core/document_index.blade.php` berukuran **149.7 KB** (~3.200 baris) dan `document_show.blade.php` berukuran **135.5 KB** (~2.800 baris).
   * *Dampak Kinerja & UX:* Seluruh modal, tab tersembunyi, dan formulir interaktif dirender sekaligus ke dalam DOM. Hal ini menghasilkan lebih dari 2.000 elemen DOM, menyebabkan perlambatan *First Contentful Paint* (> 1.8 detik pada laptop operasional) dan *scrolling lag* pada perangkat berspesifikasi standar.
   * *Rekomendasi:* Dekomposisi view monolitik menjadi komponen Blade modular (`<x-core.document-table>`, `<x-core.wizard-modal>`) dengan pemuatan data modal berbasis *lazy-loading AJAX*.
2. **Ketiadaan Navigasi Berbasis Keyboard (Keyboard-Centric Ergonomics):**
   * Operator dokumen pabean memasukkan data berulang (HS Code, jumlah kemasan, valuta, CIF) dengan kecepatan tinggi. Saat ini, sistem masih 100% *mouse-dependent* (harus mengklik tombol "+ Tambah Barang", mengklik modal, dan mengklik simpan).
   * Belum tersedia tombol pintas global (*Hotkeys*), seperti `Alt+N` (Shipment Baru), `Ctrl+S` (Simpan Draf), `Ctrl+Enter` (Submit CEISA), dan navigasi panah tabel keyboard.

### 7.4 Evaluasi Pengalaman Pengguna Staf Lapangan (Mobile Port Ops UX)
Staf Lapangan Pelabuhan (`R-FLD`) bertugas di dermaga dan lapangan penumpukan kontainer (TPS) di bawah terik matahari dengan mobilitas tinggi:
1. **Keterbacaan di Luar Ruangan (Outdoor High-Glare Readability):**
   * Tampilan mobile web saat ini menggunakan warna abu-abu tipis (`text-slate-400` dan `border-slate-200`) yang hampir tidak terbaca di bawah sinar matahari langsung pelabuhan. Diperlukan mode *Outdoor High-Contrast* dengan teks hitam pekat (`text-black font-semibold`) dan border kontras tinggi.
2. **Resiliensi Koneksi Buruk (Offline-First / Low-Connectivity Resilience):**
   * Area dermaga seringkali mengalami *blank spot* seluler. Saat staf lapangan mengambil 4 foto fisik behandle dan segel kontainer, kegagalan upload HTTP langsung membatalkan proses tanpa penyimpanan lokal (*local retry queue*).
   * Diperlukan Service Worker PWA yang menyimpan foto ke *CacheStorage / IndexedDB* secara offline dan mengunggahnya otomatis saat sinyal kembali stabil (*Background Sync*).
3. **Ergonomi Area Sentuh Jempol (Thumb-Zone Optimization):**
   * Sebagian tombol aksi konfirmasi gate-out dan upload behandle pada layar ponsel berukuran lebih kecil dari $40\times 40\text{px}$, rentan salah sentuh saat petugas mengenakan sarung tangan lapangan. Standar minimum Apple HIG adalah $48\times 48\text{px}$.

### 7.5 Evaluasi Portal Mandiri Klien (Client Experience & Transparansi)
1. **Visual Timeline & Status Kargo:**
   * Halaman pelacakan publik `/portal/track/{token}` menyajikan linimasa status kontainer yang bersih dan mudah dipahami importir/eksportir.
2. **Kelemahan Interaksi Klien:**
   * Klien tidak dapat memilih opsi notifikasi mandiri (misal: tombol *"Kirim update status ke WhatsApp saya"*).
   * Formulir unggah dokumen mandiri oleh klien (`portal.upload-token`) tidak memiliki fitur validasi format/ukuran file di sisi klien (*client-side preview & compression*), sehingga jika klien mengunggah file scan PDF berukuran 50MB, koneksi akan menggantung tanpa pesan error yang informatif.

### 7.6 Feedback Loops, State Komunikatif & Penanganan Empty State
1. **Indikator Proses & Loading States:**
   * Operasi berdurasi panjang seperti klasifikasi AI Pos Tarif HS Code dan pengecekan skema CEISA 4.0 masih menggunakan penundaan sinkron tanpa *skeleton loaders* atau *progress bar animasi*, menciptakan persepsi bahwa sistem sedang macet (*freezing*).
2. **Notifikasi Banner Statis vs Floating Toast:**
   * Pesan sukses/gagal di banyak modul masih dirender sebagai banner alert statis di pucuk atas halaman. Jika staf sedang berada di bagian bawah tabel yang panjang, notifikasi tersebut berada di luar area pandang (*out of viewport*). Wajib distandarkan menggunakan *Floating Toast Notification* di sudut kanan atas dengan durasi auto-dismiss 4 detik.
3. **Penanganan Status Kosong (Empty States):**
   * Ketika tabel kargo, tagihan vendor, atau pipeline CRM belum memiliki data, sistem hanya menampilkan tabel kosong atau teks *"Tidak ada data"*. Belum terdapat ilustrasi kontekstual, pesan edukatif, dan tombol aksi pemandu (*Empty State Call to Action*, misal: *"Belum ada penawaran aktif. Buat penawaran harga pertama Anda di sini"*).

---

## 8. AUDIT KELENGKAPAN FITUR SECARA USER INTERFACE (UI)

Audit antarmuka visual dievaluasi terhadap kepatuhan **Apple Human Interface Guidelines (HIG)**, **Tailwind CSS Semantic Tokens**, dan aturan desain resmi pada `docs/DESIGN_RULES_PATS_CEISA.md` serta `docs/DESIGN_SYSTEM_PATS_CEISA.md`.

### 8.1 Kepatuhan Sistem Token 3-Layer & Eliminasi Nilai Hardcoded
Sistem desain resmi PATS_CEISA mewajibkan arsitektur token 3 tingkat (`public/css/design-tokens.css`):
1. **Inkonsistensi Nilai Hardcoded Hex:**
   * *Temuan Lapangan:* Pada `resources/views/layouts/bottom-tab-bar.blade.php`, `topbar.blade.php`, dan beberapa tombol wizard, ditemukan penggunaan hardcoded hex `#007AFF` (Apple Blue) dan `#1C1C1E` (Dark Surface).
   * *Pelanggaran Aturan Desain:* `docs/DESIGN_RULES_PATS_CEISA.md` Bagian 2 secara tegas menetapkan **Sky Blue (`#0284c7` / `--primitive-sky-600`)** sebagai warna aksen tunggal brand Genie / PATS. Penggunaan `#007AFF` secara manual merusak konsistensi palet korporat PT Agung Trans Solusindo.
   * *Rekomendasi:* Ganti seluruh kemunculan `#007AFF` dengan kelas semantik Tailwind `text-sky-600`, `bg-sky-600`, atau variabel CSS `var(--color-primary)`.
2. **Pemanfaatan Token Permukaan (Surface Tokens):**
   * Komponen kartu pada `dashboard.blade.php` telah mengadopsi `bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-slate-200/80` secara konsisten, namun beberapa container input pada modul akuntansi masih menggunakan `bg-gray-100` bawaan browser.

### 8.2 Arsitektur Apple HIG Bento Grid & Konsistensi Geometri Squircle
1. **Bento Grid Dashboard:**
   * Penataan Bento Grid pada dashboard utama (`resources/views/dashboard.blade.php`) telah memenuhi standar visual eksekutif yang tinggi: metrik KPI penjaluran pabean, devisa ekspor/impor, dan status radar kontainer tersusun rapi dalam blok-blok modular proporsional.
2. **Konsistensi Sudut (Radius Squircle Tokens):**
   * Standar resmi:
     * Tombol, Input & Item Menu: `rounded-xl` (12px).
     * Kartu Dashboard, Modal Dialog & Panel: `rounded-2xl` (16–20px).
     * Badge status kecil & chip tag: `rounded-lg` (8px).
   * *Temuan:* Sebagian besar komponen telah mematuhi aturan squircle ini. Namun pada beberapa tabel daftar kargo, sudut container tabel masih menggunakan `rounded-none` yang memutus kontinuitas visual macOS/iOS.

### 8.3 Tipografi, Hierarki Visual & Angka Tabular Monospace
1. **Penataan Tipografi:**
   * Menggunakan font modern Sans-Serif (Inter / SF Pro Display) dengan hierarki bobot yang teratur: Title (Bold 24px), Subtitle (Semibold 16px), Body (Regular 14px), dan Caption (Medium 12px).
2. **Kebutuhan Vital Angka Tabular Monospace (`tabular-nums font-mono`):**
   * *Temuan Kritis UI:* Data operasional logistik kepabeanan didominasi oleh angka-angka teknis:
     * Nomor Pengajuan Pabean (26 digit angka).
     * Nomor Peti Kemas ISO 6346 (4 huruf + 7 digit angka, e.g., `MSKU1234567`).
     * Pos Tarif HS Code BTKI 2022 (8 digit angka, e.g., `8471.30.20`).
     * Nilai Finansial Devisa (USD/IDR, tarif bea masuk, nilai CIF/FOB).
   * *Kelemahan:* Banyak kolom tabel di `document_index.blade.php`, `tracking_index.blade.php`, dan `accounting/index.blade.php` masih menggunakan font proporsional standar tanpa class `font-mono tabular-nums`. Karakter angka proporsional memiliki lebar horizontal berbeda (angka '1' lebih sempit dari '8'), sehingga digit-digit desimal dan nilai ribuan tidak sejajar vertikal secara presisi. Hal ini memperlambat verifikasi visual auditor keuangan dan staf pabean.

### 8.4 Disiplin Anti-Slop: Audit Warna Pelangi, Fake Telemetry & Emojis
1. **Larangan Warna Pelangi Navigasi (*Rainbow Accent Ban*):**
   * *Status: 100% Lulus (COMPLIANT).*  
   * Navigasi sidebar pada `resources/views/layouts/sidebar.blade.php` telah sepenuhnya bersih dari warna-warni acak. Seluruh ikon menu menggunakan warna netral seragam (`text-slate-400 hover:text-slate-900`) dan hanya memancarkan warna aksen Sky Blue pada kondisi aktif (`bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300`).
2. **Larangan Fake Telemetry Timestamp:**
   * *Status: Sebagian.*  
   * Dashboard tidak lagi menampilkan jam PHP statis palsu. Namun pada indikator status gateway CEISA 4.0, masih terdapat label *"Online"* statis yang belum terikat ke interval ping websocket/AJAX nyata.
3. **Larangan Emoji Struktural:**
   * *Status: 100% Lulus (COMPLIANT).*  
   * Seluruh ikon pada tombol, header tabel, dan navigasi menggunakan ikon vektor SVG murni (Lucide Icons) dengan ketebalan garis seragam (`stroke-width="1.75"`), menggantikan seluruh penggunaan emoji visual yang tidak profesional.

### 8.5 Aksesibilitas Kontras Warna (WCAG 2.1 AA) & Keselarasan Dark Mode
1. **Rasio Kontras Status Kepabeanan:**
   * **Jalur Hijau & SPPB:** `bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300` $\rightarrow$ Rasio Kontras 6.4:1 pada mode terang dan 5.8:1 pada mode gelap (Memenuhi kriteria **WCAG 2.1 AA Pass**).
   * **Jalur Merah & Reject:** `bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300` $\rightarrow$ Rasio Kontras 5.3:1 (Memenuhi kriteria **WCAG 2.1 AA Pass**).
   * **Jalur Kuning & Hold:** Teks `text-amber-700` pada latar `bg-amber-50` memenuhi rasio 4.9:1. Dilarang keras menurunkan warna menjadi `text-yellow-400` yang gagal kontras (< 2.8:1).
2. **Keselarasan Mode Gelap (Dark Mode Parity):**
   * Mode gelap telah terkonfigurasi pada shell utama (Sidebar, Topbar, Kartu). Namun, beberapa elemen form select dropdown kustom dan pop-up datepicker kepabeanan masih merender latar putih terang yang menimbulkan efek silau mendadak (*flashing*) saat digunakan di ruangan gelap.

### 8.6 Responsivitas Multi-Perangkat (Mobile PWA, Tablet, Desktop)
1. **Mobile (<768px - PWA Experience):**
   * Tab bar bawah aktif pada layar ponsel, namun tombol trigger menu samping masih membuka drawer desktop lebar horizontal yang memotong konten.
   * *Perbaikan Wajib:* Tombol menu pada mobile wajib membuka **iOS Bottom Sheet Modal** dengan opsi menu vertikal dan *backdrop dismiss*.
2. **Tablet (768px - 1024px - iPadOS Layout):**
   * Mode collapsed sidebar (`w-20`) telah terpasang dengan baik. Namun tooltip nama modul yang mengandalkan hover mouse tidak dapat diakses optimal pada layar sentuh iPad tanpa stylus/trackpad.
3. **Desktop (≥1024px - macOS Pro Workspace):**
   * Tata letak sangat matang, memanfaatkan ruang horizontal secara optimal untuk tabel data berdensitas tinggi, filter bar, dan panel aksi cepat.

---

## 9. AUDIT MENYELURUH MANAJEMEN RISIKO (RISK MANAGEMENT AUDIT)

Audit risiko ini mengevaluasi eksposur kerugian finansial, sanksi hukum pemerintah, kegagalan operasional logistik, dan insiden keamanan siber yang dihadapi oleh **PT Agung Trans Solusindo** dalam pengoperasian sistem GENIE.

### 9.1 Matriks Evaluasi Risiko Enterprise (Risk Heatmap 5x5)

Evaluasi memetakan 10 risiko utama berdasarkan Tingkat Probabilitas (*Likelihood* 1–5) dan Tingkat Dampak (*Severity* 1–5):

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

| ID Risiko | Kategori Risiko | Deskripsi Potensi Risiko | Likelihood | Impact | Skor Risiko | Tingkat Risiko | Status Mitigasi Saat Ini |
| :---: | :--- | :--- | :---: | :---: | :---: | :---: | :--- |
| **R01** | **Hukum / Regulasi** | Kebocoran foto biometrik absensi di webroot publik (Sanksi UU PDP No. 27/2022). | 3 | 5 | **15** | **EXTREME** | **Belum Ada** (Foto tersimpan publik tanpa enkripsi). |
| **R02** | **Kepatuhan Pabean** | Kesalahan Pos Tarif HS Code memicu SPTNP / Denda Notul 100%–1000% selisih BM. | 4 | 4 | **16** | **EXTREME** | **Parsial** (Ada rekomendasi AI, tapi verifikasi SPV belum enforce lartas). |
| **R03** | **Stabilitas Sistem** | Crash memori & HTTP 504 Timeout akibat Batch Import F15 tanpa background worker. | 4 | 5 | **20** | **EXTREME** | **Belum Ada** (`app/Jobs` tidak ada; 100% proses sinkron). |
| **R04** | **Finansial / Likuiditas** | Pembayaran billing pabean MPN G3 tanpa jaminan dana talangan dari klien (*Default*). | 5 | 4 | **20** | **EXTREME** | **Belum Ada** (Tidak ada escrow/pre-funding verification gate). |
| **R05** | **Kecurangan (Fraud)** | Penggelembungan kwitansi petty cash operasional trattic/behandle lapangan. | 2 | 4 | **8** | **HIGH** | **Parsial** (Input manual tanpa validasi geotag foto pengeluaran). |
| **R06** | **Operasional** | Deadlock persetujuan lompat tahap F16 akibat salah kode role (`R-SPV`). | 3 | 3 | **9** | **HIGH** | **Belum Ada** (Tiket lompat macet, butuh intervensi SuperAdmin). |
| **R07** | **Operasional Pelayaran** | Denda Demurrage & Detention kontainer akibat keterlambatan rilis DO (P09 mock email). | 3 | 3 | **9** | **HIGH** | **Parsial** (Countdown hari ada, tapi email pelayaran masih mock stub). |
| **R08** | **Integritas Finansial** | Manipulasi atau penghapusan jurnal pada periode akuntansi yang telah ditutup. | 3 | 2 | **6** | **MEDIUM** | **Parsial** (Penutupan buku ada, tapi controller menelan exception). |
| **R09** | **Operasional SDM** | Proyek shipment terbengkalai saat staf dokumen cuti/sakit mendadak (*SPOF*). | 1 | 2 | **2** | **LOW** | **Tersedia** (Tersedia fitur alih tugas otomatis `reassignPic`). |
| **R10** | **Keamanan Siber** | Brute-force token UUID pelacakan kargo publik akibat ketiadaan rate limiter. | 3 | 1 | **3** | **LOW** | **Belum Ada** (Rute `/portal/track/{token}` belum diproteksi throttle). |

### 9.2 Analisis Risiko Operasional, Demurrage & Penumpukan Pelabuhan
1. **Risiko Biaya Demurrage & Detention Pelayaran (ID: R07):**
   * *Mekanisme Kerugian:* Agen pelayaran (Maersk, CMA CGM, ONE, Evergreen) memberikan masa bebas (*free time*) pemakaian kontainer standar 7–14 hari. Keterlambatan penebusan DO atau pengembalian kontainer kosong dikenakan denda demurrage rata-rata **USD 50 s.d. USD 150 per kontainer per hari**.
   * *Kelemahan Sistem Saat Ini:* Model `Shipment` memang memiliki mutator `getDaysLeftFreeTimeAttribute`, namun sistem **belum memiliki mesin peringatan dini otomatis (*Predictive Early-Warning Ticker*)**. Tidak ada pemicu otomatis yang mengirimkan notifikasi eskalasi berjenjang (D-3, D-2, D-1) ke WhatsApp Operations Manager atau Klien saat free time mendekati kritis. Ditambah lagi, pengiriman email permintaan DO (tahap P09) masih berupa mock stub.
2. **Risiko Bottleneck Pemeriksaan Fisik Pabean (Behandle):**
   * Jika penjaluran CEISA menetapkan **Jalur Merah**, kargo wajib dipindahkan ke lapangan behandle TPS dalam kurun waktu 1x24 jam. Ketiadaan penugasan otomatis ke staf lapangan berisiko memicu penumpukan kargo dan denda penumpukan progresif (*storage fee*) TPS pelabuhan.

### 9.3 Analisis Risiko Kepatuhan Pabean (Customs Regulatory Risk & Sanksi Notul/SPTNP)
1. **Risiko Sanksi Denda Administrasi Nota Pembetulan (SPTNP) (ID: R02):**
   * Sesuai **Pasal 82 ayat (5) UU No. 17 Tahun 2006 tentang Kepabeanan**, kesalahan penetapan pos tarif atau nilai pabean yang mengakibatkan kekurangan pembayaran Bea Masuk dikenakan sanksi denda administrasi berupa denda berjenjang **minimal 100% hingga maksimal 1000%** dari total kekurangan Bea Masuk.
   * *Contoh Kasus:* Kekurangan bayar BM Rp 25.000.000 akibat salah 1 digit HS Code dapat berujung pada tagihan SPTNP senilai Rp 250.000.000. Jika importir menolak membayar, PPJK (PT Agung Trans Solusindo) secara hukum ikut bertanggung jawab secara tanggung renteng.
   * *Mitigasi Wajib:* Sistem AI Agent klasifikasi HS Code tidak boleh langsung menetapkan pos tarif secara mandiri; wajib menerapkan protokol *Four-Eyes Principle* di mana supervisor dokumen (`R-DOC-SPV`) wajib memverifikasi uraian spesifikasi barang, katalog teknis, dan catatan pos tarif BTKI sebelum enkripsi draf pabean.
2. **Risiko Pelanggaran Larangan dan Pembatasan (Lartas):**
   * Pengiriman draf PIB tanpa melampirkan izin impor resmi (Laporan Surveyor / LS, Persetujuan Impor Kemenperin, izin BPOM, sertifikat Karantina) mengakibatkan kargo terkena **Nota Pemberitahuan Penolakan (NPP)** atau perintah re-ekspor paksa. Sistem belum memiliki validasi silang mandiri ke portal INSW Lartas sebelum pengiriman data ke CEISA 4.0.

### 9.4 Analisis Risiko Finansial, Fraud Talangan Pabean & Arus Kas
1. **Risiko Gagal Bayar Talangan Pabean / Reimbursable Default (ID: R04):**
   * *Kondisi Bisnis:* Pembayaran billing pabean (Bea Masuk, PPN Impor, PPh 22) melalui portal MPN G3 seringkali bernilai ratusan juta hingga miliaran rupiah per pengapalan. PT Agung Trans Solusindo seringkali diminta memberikan talangan (*reimbursable advance*) terlebih dahulu agar kargo segera keluar.
   * *Celah Sistem:* Modul Finance saat ini mengizinkan penerbitan instruksi pembayaran billing MPN G3 tanpa mengecek status apakah dana talangan telah disetorkan oleh klien ke rekening escrow perusahaan. Jika klien mengalami gagal bayar (*default*), arus kas perusahaan langsung terancam krisis likuiditas parah.
   * *Rekomendasi Kontrol:* Wajib dipasang fitur **Pre-Funding Verification Gate**: Pembayaran billing MPN hanya dapat dieksekusi jika (a) status piutang talangan telah bertanda `FUNDS_CLEARED` di bank, atau (b) ada *Bypass Approval* tertulis dengan tanda tangan digital Direktur Utama (`R-OWN`).
2. **Risiko Kecurangan Pengeluaran Lapangan (Petty Cash Fraud) (ID: R05):**
   * Pengeluaran kas kecil untuk biaya kuli pelabuhan, sewa forklift TPS, dan timbangan kontainer saat ini diinput secara manual pada `TrafficWorkflowController::storeOperationCost` tanpa pembuktian foto nota ber-geotag GPS. Hal ini membuka celah moral hazard dan manipulasi nota fisik.

### 9.5 Analisis Risiko Hukum & Kepatuhan Perlindungan Data Pribadi (UU PDP No. 27/2022)
1. **Ancaman Pidana & Sanksi Denda Maksimal (ID: R01):**
   * **UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi** mengklasifikasikan data biometrik (data wajah dan sidik jari) sebagai **Data Pribadi Spesifik** (Pasal 16 ayat 2).
   * **Pasal 67 ayat (2):** Setiap orang yang dengan sengaja dan melawan hukum mengungkapkan Data Pribadi yang bukan miliknya dipidana dengan pidana penjara paling lama 4 tahun dan/atau pidana denda paling banyak Rp 4.000.000.000.
   * **Pasal 68:** Memperoleh atau menggunakan data pribadi secara melawan hukum dipidana penjara paling lama 5 tahun dan/atau denda Rp 5.000.000.000.
   * Bagi korporasi, sanksi denda administratif dapat mencapai **2% dari total pendapatan tahunan** atau pencabutan izin operasional perusahaan.
2. **Kondisi Pelanggaran Konkret di Repositori:**
   * Berkas foto selfie absensi disimpan pada folder terbuka `public/uploads/attendances/`. Siapa pun yang mengetahui URL atau melakukan *directory traversal* dapat mengunduh dan menyalahgunakan ratusan data biometrik wajah karyawan.
   * Rencana tindakan perbaikan pada Roadmap Fase 2 bersifat **MUTLAK & WAJIB SEGERA DISELESAIKAN**.

### 9.6 Analisis Risiko Keamanan Siber, Penetrasi & Ketersediaan Sistem
1. **Kerentanan Serangan Denial of Service via Zip Bomb (ID: R03):**
   * Modul Batch Import F15 mengekstrak file kompresi ZIP langsung di thread web server PHP. Penyerang atau staf yang mengunggah berkas zip-bomb (file 10MB yang mengembang menjadi 50GB di memori) akan langsung menguras CPU dan RAM server Laragon/Apache, mengakibatkan *denial of service* (server down) bagi seluruh staf operasional lainnya.
2. **Eksploitasi Enumerasi Token Portal Klien (ID: R10):**
   * Rute publik `Route::get('/portal/track/{token}')` belum diproteksi middleware `throttle`. Penyerang dapat melancarkan skrip *dictionary attack* untuk menebak token pelacakan kargo guna mencuri data manifes kargo bernilai tinggi.

---

## 10. KERANGKA OPTIMASI MENUJU LEVEL FITUR MAKSIMAL (FEATURE MAXIMIZATION FRAMEWORK)

Untuk mentransformasikan **GENIE (PATS_CEISA)** dari sistem ERP operasional biasa menjadi **Platform Ekosistem Logistik & Kepabeanan Otonom Terpadu (Autonomous Enterprise Logistics Hub)**, disusun kerangka kerja optimasi fitur berbasis model kematangan 5 tingkat.

### 10.1 Model Kematangan Fitur Enterprise Logistik (Maturity Levels 1 s.d. 5)

```
┌────────────────────────────────────────────────────────────────────────┐
│ LEVEL 5: AUTONOMOUS ECOSYSTEM (Ekosistem Otonom, Prediktif & Terpadu) │
├────────────────────────────────────────────────────────────────────────┤
│ LEVEL 4: INTELLIGENT & PROACTIVE (Asistensi AI, Early-Warning & RAG)   │
├────────────────────────────────────────────────────────────────────────┤
│ LEVEL 3: AUTOMATED & EVENT-DRIVEN (Antrean Queue, Background Sync H2H) │
├────────────────────────────────────────────────────────────────────────┤
│ LEVEL 2: CONNECTED WORKFLOW (Alur Kerja Berantai & Validasi Dasar) ◄───┤ STATUS GENIE SAAT INI
├────────────────────────────────────────────────────────────────────────┤ (Rata-rata berada di Level 2.4)
│ LEVEL 1: MANUAL CRUD / FORM ENTRY (Pencatatan Mandiri Tanpa Integrasi) │
└────────────────────────────────────────────────────────────────────────┘
```

* **Level 1 (Manual CRUD):** Entri data formulir dasar tanpa validasi silang dan tanpa integrasi eksternal.
* **Level 2 (Connected Workflow):** Alur kerja berantai (P01–P19) terhubung antar-tahap, skema pabean tervalidasi, kalkulasi matematis otomatis (Status saat ini: Core pabean & Payroll).
* **Level 3 (Automated & Event-Driven):** Pemrosesan asinkron berbasis background worker, webhook sinkronisasi otomatis CEISA & Bank, eliminasi seluruh proses manual berulang.
* **Level 4 (Intelligent & Proactive):** Asistensi AI prediktif (deteksi anomali bea masuk, auto-clustering dokumen tingkat lanjut, peringatan dini demurrage cerdas, OCR multi-halaman split-screen).
* **Level 5 (Autonomous Logistics Ecosystem):** Platform mandiri bersertifikasi ISO 27001 & UU PDP, integrasi pertukaran data elektronik pelabuhan real-time (TPS Online & INSW), rekonsiliasi perbankan otomatis instan, serta bot operasional WhatsApp dua arah interaktif.

### 10.2 Analisis Kesenjangan (Gap Analysis) Menuju Level Fitur Maksimal per Modul

Tabel komparatif berikut menjabarkan kesenjangan fungsional antara kondisi sistem saat ini menuju target **Level Fitur Maksimal**:

| Modul Bisnis | Status Tingkat Saat Ini | Target Level Maksimal | Fitur Kunci yang Belum Tersedia (Gap Fungsional) | Solusi Arsitektur & Teknologi untuk Mencapai Level Maksimal |
| :--- | :---: | :---: | :--- | :--- |
| **Core Customs CEISA 4.0** | **Level 2.5** | **Level 5.0** | • Integrasi Manifes BC 1.1 Inward.<br>• Feed otomatis status TPS Online.<br>• Pembayaran langsung API Billing MPN G3.<br>• Sistem otomatisasi sanggahan Notul/SPTNP. | Pasang consumer webhook CEISA 4.0 Host-to-Host; integrasikan OpenAPI INSW Single Submission; buat generator dokumen surat sanggahan pabean otomatis berbasis riwayat penetapan pos tarif BTKI. |
| **Delivery Order (DO)** | **Level 2.0** | **Level 4.5** | • Integrasi API pelayaran / PIN DO digital.<br>• Pengiriman email rilis DO riil.<br>• Deteksi otomatis free-time & detention. | Hubungkan dengan platform INSW DO Online v3.0; implementasikan driver SMTP/Mailgun riil pada `ShipmentWorkflowService`; bangun cron engine kalkulasi denda harian kontainer. |
| **Traffic & Dispatch Armada** | **Level 2.0** | **Level 4.5** | • Master data armada & pengemudi (Fleet/Driver).<br>• Pelacakan GPS IoT armada truk realtime.<br>• Tiket digital e-SPPB & QR-code gate-pass. | Buat modul Fleet Management; integrasikan webhook GPS telematics vendor trucking; terbitkan e-Pass ber-QR Code untuk dipindai satpam pos pelabuhan TPS. |
| **Batch Import F15 & F16** | **Level 2.0** | **Level 5.0** | • Pemrosesan asinkron (Background Queue).<br>• Pemisahan berkas PDF multi-halaman otomatis.<br>• Auto-tune threshold akurasi OCR AI. | Bangun `app/Jobs/` dengan driver Redis Queue; gunakan PDF parser engine berbasis AI Vision untuk mendeteksi batas dokumen (*split document boundary*); dashboard akurasi OCR. |
| **HRM & Presensi Biometrik** | **Level 2.0** | **Level 5.0** | • Pencocokan wajah biometrik nyata.<br>• Enkripsi berkas & isolasi direktori privat.<br>• Deteksi keaslian wajah (*Liveness Detection*).<br>• Pemusnahan data otomatis retensi 2 tahun. | Pindahkan media ke storage privat bertanda tangan; pasang engine ekstraksi vektor wajah 128-D berbasis WebAssembly di browser (zero photo exposure); artisan scheduler pemusnahan berkas. |
| **Payroll & Perpajakan** | **Level 2.8** | **Level 5.0** | • Rekonsiliasi tahunan PPh 21 Pasal 17.<br>• Formulir bukti potong 1721-A1 PDF.<br>• Integrasi e-Bupot 21/26 DJP.<br>• Auto-disbursement API bank. | Kembangkan kalkulator Masa Pajak Desember; buat template PDF 1721-A1; buat generator XML/JSON skema e-Bupot DJP; integrasikan API Corporate Disbursement Bank Mandiri/BCA. |
| **Finance, Billing & Kas** | **Level 2.5** | **Level 5.0** | • Pre-funding / Escrow verification gate.<br>• Live corporate bank statement sync.<br>• Revaluasi selisih kurs valas otomatis.<br>• e-Faktur direct API upload. | Buat aturan pemblokiran bayar billing pabean tanpa dana talangan masuk; hubungkan BCA/Mandiri Cash Management API; modul jurnal otomatis selisih kurs KMK vs Bank. |
| **Akuntansi & Buku Besar** | **Level 2.5** | **Level 5.0** | • Penguncian buku di level Model Observer.<br>• Pengujian otomatis (Coverage > 85%).<br>• Konsolidasi multi-cabang (Jakarta, Sby, Smg).<br>• Laporan arus kas otomatis metode langsung. | Pasang observer Eloquent penolak mutasi periode terkunci; tulis test suite lengkap di `tests/Feature/Accounting*`; buat segmentasi kode cabang pada COA (`1-1110-JKT`). |
| **CRM & Omnichannel WABA** | **Level 1.8** | **Level 4.5** | • Koneksi nyata Meta Cloud Graph API.<br>• Webhook penerima pesan masuk 2 arah.<br>• Bot WhatsApp otomatis cek status kontainer.<br>• Push notifikasi SPPB instan ke nomor klien. | Daftarkan Meta Business App resmi; ganti simulasi dengan HTTP client Guzzle ke Graph API v19; bangun chatbot auto-reply status kontainer via nomor B/L. |
| **AI Customs Agent & MCP** | **Level 3.8** | **Level 5.0** | • Basis pengetahuan RAG Catatan Penjelasan BTKI.<br>• Audit trail reasoning keputusan AI.<br>• Autonomous customs risk anomaly scanner. | Tambahkan vector database (pgvector/Pinecone) berisi UU Kepabeanan & Buku Tarif BTKI 2022; implementasikan audit trail usulan tarif sebelum persetujuan supervisor. |

### 10.3 Blueprint Spesifikasi Fitur Generasi Lanjutan (Next-Gen Autonomous Logistics)
Tiga fitur terobosan puncak yang akan membawa efisiensi operasional tertinggi bagi PT Agung Trans Solusindo:

1. **Sistem Deteksi Dini & Pencegah Demurrage Kontainer (Predictive Free-Time Engine):**
   * *Logika Kerja:* Sistem menghitung secara otomatis delta waktu antara tibanya kapal (*Arrival Date*) dengan sisa masa bebas peti kemas (*Free Time Deadline*).
   * *Otomasi:* Pada H-3 dan H-1 sebelum jatuh tempo demurrage, sistem secara otomatis menerbitkan tiket eskalasi darurat pada dashboard Manajer Operasional dan mengirimkan peringatan otomatis ke staf traffic lapangan dan importir melalui WhatsApp.
2. **Pencocokan Biometrik Wajah Tanpa Paparan Foto (Edge Vector Biometrics):**
   * *Logika Kerja:* Menggunakan library WebAssembly (MediaPipe/TensorFlow.js) yang berjalan langsung di peramban ponsel staf.
   * *Keamanan Mutlak:* Foto wajah tidak pernah dikirimkan mentah ke server publik. Ponsel hanya mengirimkan representasi array vektor numerik 128-dimensi yang terenkripsi token AES-256. Sistem di server membandingkan jarak kosinus (*Cosine Similarity Distance*) vektor wajah tanpa menyimpan berkas foto, menjamin **kepatuhan 100% terhadap UU PDP**.
3. **Bot Asisten Pabean WhatsApp Dua Arah (Conversational Customs Tracking Bot):**
   * *Logika Kerja:* Klien importir tidak perlu lagi menelepon staf untuk menanyakan status kargo. Klien cukup mengirim pesan WhatsApp: *"Status BL MSKU9876543"*.
   * *Respons Instan:* Bot mengekstrak nomor B/L, memeriksa database operasional, dan membalas dalam 2 detik: *"Kargo Anda (1x40' HC) telah terbit SPPB Jalur Hijau pukul 11:30 WIB. Saat ini dalam proses pemuatan truk untuk Gate Out pelabuhan"*.

### 10.4 Arsitektur Target Ekosistem Digital 2026/2027 (PATS_CEISA Apex)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                    GENIE / PATS_CEISA APEX ECOSYSTEM 2026                    │
├───────────────────────┬──────────────────────────────┬───────────────────────┤
│ 1. CLIENT & FIELD     │ 2. INTELLIGENT CORE ENGINE   │ 3. REGULATORY & H2H   │
│ • Client Tracking PWA │ • Event-Driven Redis Queue   │ • CEISA 4.0 Open API  │
│ • Outdoor Field PWA   │ • AI Vision OCR & Document   │ • INSW DO Online v3.0 │
│ • Biometric Edge Auth │ • Predictive Demurrage Engine│ • Meta WABA Bot Hub   │
│ • WhatsApp Bot 2-Way  │ • Balanced Auto-Journal GL   │ • Bank Corporate API  │
└───────────────────────┴──────────────────────────────┴───────────────────────┘
```

---

## 11. KESIMPULAN AKHIR & REKOMENDASI TERPADU (UNIFIED FINAL CONCLUSION)

Audit komprehensif multi-dimensi terhadap sistem **GENIE (PATS_CEISA)** membuktikan bahwa perangkat lunak ini memiliki fondasi arsitektur enterprise yang sangat potensial, dengan pencapaian gemilang pada remediasi payload Flat Root CEISA 4.0 DJBC, server MCP 27 tools berstandar internasional, proteksi Controller-Level RBAC 12 peran, dan formula penggajian TER PMK 168/2023.

Namun, untuk mengamankan operasional perusahaan dan mencapai **Level Fitur Maksimal**, pimpinan PT Agung Trans Solusindo dan tim pengembang Cooca.id disarankan membagi fokus eksekusi ke dalam 3 prioritas strategis:

1. **Prioritas Mendesak (Minggu 1):** Selesaikan perbaikan rute view dokumen (`RouteNotFoundException`), perbaiki seeder approval F16 (`R-DOC-SPV`), inisialisasi variabel akuntansi `$entryDate`, dan rapikan data uji CRM.
2. **Prioritas Kepatuhan Hukum & Keamanan (Minggu 2–3):** Relokasikan foto absensi ke direktori privat terlindungi, tegakkan enkripsi biometrik, wajibkan persetujuan UU PDP sebelum absensi, dan amankan periode akuntansi di level database trigger.
3. **Prioritas Skalabilitas & Fitur Maksimal (Minggu 4–6):** Bangun background queue worker F15 untuk eliminasi risiko crash impor massal, hubungkan live Meta WABA WhatsApp API, aktifkan email DO pelayaran riil, dan lengkapi automated test coverage pada modul finansial hingga mencapai di atas 85%.

Dengan menuntaskan seluruh rencana perbaikan pada laporan audit ini, sistem GENIE dipastikan bertransformasi menjadi platform ERP kepabeanan dan logistik ekspor-impor paling andal, patuh regulasi, aman dari risiko hukum, dan berkinerja tinggi di industri logistik nasional.

---
*Laporan Audit Multi-Dimensi Komprehensif ini disusun secara independen, objektif, dan forensik berdasarkan bukti kode sumber aktual.*  
*PT Agung Trans Solusindo & Cooca.id — All Rights Reserved © 2026.*
