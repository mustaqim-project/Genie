# LAPORAN AUDIT SISTEM END-TO-END ENTERPRISE KOMPREHENSIF
## SISTEM ERP KEPABEANAN, FREIGHT FORWARDING & LOGISTIK: PATS_CEISA (GENIE ERP v2.0)
**PT AGUNG TRANS SOLUSINDO & COOCA.ID**  
*Klasifikasi: Laporan Audit Forensik Sistem, Dekomposisi Arsitektur, Verifikasi CRUD & Runtime, Matriks Kesenjangan Regulasi, dan Analisis Risiko Menyeluruh*  
*Status Dokumen: Final Assessment Report (Strictly Read-Only Audit)*  
*Tanggal: 15 November 2026*  
*Versi Sistem: 2.0-Enterprise (Laravel 13.20.0 / PHP 8.3 / MySQL 8.4)*  

---

## DAFTAR ISI

1. [Ringkasan Eksekutif (Executive Summary)](#1-ringkasan-eksekutif-executive-summary)
   - 1.1 Latar Belakang & Tujuan Audit
   - 1.2 Ruang Lingkup Sistem & Metodologi Audit
   - 1.3 Evaluasi Kesehatan Multi-Dimensi (System Health Score)
   - 1.4 Ringkasan Temuan Audit Berdasarkan Tingkat Keparahan (Severity Summary)
   - 1.5 Prestasi Kunci Arsitektur & Kepatuhan Regulasi
2. [Cakupan Sistem (System Coverage)](#2-cakupan-sistem-system-coverage)
   - 2.1 Inventarisasi 12 Modul Fungsional & 40 Fitur Enterprise
   - 2.2 Arsitektur Pengendali Presentasi (39 Controllers)
   - 2.3 Arsitektur Lapisan Layanan Bisnis (23 Domain Services)
   - 2.4 Protokol Agen Kecerdasan Buatan & Server MCP (27 Tools Aktif)
   - 2.5 Struktur Basis Data: 33 Migrasi, 135 Tabel, dan 81 Model Eloquent
   - 2.6 Pemrosesan Latar Belakang (Queue Jobs) & Penjadwal Tugas (Console Scheduler)
3. [Cakupan Operasional CRUD & Siklus Hidup Fungsional (CRUD & Functional Coverage)](#3-cakupan-operasional-crud--siklus-hidup-fungsional-crud--functional-coverage)
   - 3.1 Matriks Verifikasi CRUD 14 Model Transaksional Inti
   - 3.2 Penanganan Validasi Sisi Server & Sisi Klien
   - 3.3 Verifikasi Transisi Status Alur Berantai P01 s.d. P19
   - 3.4 Penegakan Aturan Bisnis Inti (Business Rules BR-01 s.d. BR-20)
   - 3.5 Pengujian Kasus Batas (Edge Cases) & Ketahanan Sistem
4. [Temuan Audit Berdasarkan Tingkat Keparahan (Findings by Severity)](#4-temuan-audit-berdasarkan-tingkat-keparahan-findings-by-severity)
   - 4.1 Temuan Tingkat Kritis (CRITICAL Severity)
   - 4.2 Temuan Tingkat Tinggi (HIGH Severity)
   - 4.3 Temuan Tingkat Sedang (MEDIUM Severity)
   - 4.4 Temuan Tingkat Rendah (LOW Severity)
5. [Fitur Rusak atau Malfungsi (Malfunctioning / Broken Features)](#5-fitur-rusak-atau-malfungsi-malfunctioning--broken-features)
   - 5.1 Mesin Peringatan Demurrage Otomatis (`CheckDemurrageAlertsCommand`)
   - 5.2 Alur Impor Asinkron Dokumen F15 (Rute Hilang & CPU Spike PDF Parser)
   - 5.3 Suite Pengujian Otomatis Fitur Akuntansi & Pajak (`AccountingAndTaxFeatureTest`)
   - 5.4 Transmisi Email Vendor DO Otomatis (Pergeseran Argumen & Vendor Unset)
   - 5.5 Penanganan Akses Media Privat Tanpa Otentikasi (`PrivateMediaController`)
6. [Ketidakkonsistenan Sistem (Inconsistencies Analysis)](#6-ketidakkonsistenan-sistem-inconsistencies-analysis)
   - 6.1 Ketidakkonsistenan Skema & Integritas Data (Data & Schema Inconsistencies)
   - 6.2 Ketidakkonsistenan Informasi & Status Pelacakan (Information Inconsistencies)
   - 6.3 Ketidakkonsistenan Antarmuka Pengguna & Struktur Direktori View (UI Inconsistencies)
   - 6.4 Ketidakkonsistenan Logika Bisnis & Penanganan Respon HTTP (Business Logic Inconsistencies)
7. [Tumpang Tindih Fitur & Rekomendasi Konsolidasi (Overlapping Features & Consolidation)](#7-tumpang-tindih-fitur--rekomendasi-konsolidasi-overlapping-features--consolidation)
   - 7.1 Duplikasi Pengendali Warisan: `DoController` & `TrafficController` vs Workflow Modern
   - 7.2 Metode Monolitik Warisan WhatsApp & Media Sosial di `CrmController`
   - 7.3 Duplikasi Inisialisasi Data Fallback Hardcoded `WhatsappSetting` di Tiga Controller
   - 7.4 Cetak Biru Rekomendasi Konsolidasi & Refactoring Terpadu
8. [Analisis Akar Masalah Sistemik (Root Cause Analysis)](#8-analisis-akar-masalah-sistemik-root-cause-analysis)
   - 8.1 Evolusi Cepat Prototipe v1.0 ke Domain-Driven Enterprise v2.0 Tanpa Gerbang Deprekasi
   - 8.2 Konfigurasi Lingkungan Pengujian yang Membahayakan Basis Data Aktif (`RefreshDatabase`)
   - 8.3 Kesenjangan Sinkronisasi Skema Antara Migrasi, Model, dan Automated Test
   - 8.4 Ketiadaan Validasi Integritas Berkas Sebelum Pemrosesan Regex Pihak Ketiga (Smalot PDF)
   - 8.5 Defisit Infrastruktur Model Factory dalam Ekosistem Pengujian Otomatis
9. [Dampak Lintas Modul & Analisis Risiko Sistem (Cross-Module Impact & System Risk Analysis)](#9-dampak-lintas-modul--analisis-risiko-sistem-cross-module-impact--system-risk-analysis)
   - 9.1 Matriks Evaluasi Risiko Enterprise (Risk Heatmap 5x5 ISO 31000:2018)
   - 9.2 Propagasi Kegagalan Lintas Alur Kerja: Operasional $\rightarrow$ Keuangan $\rightarrow$ Akuntansi
   - 9.3 Analisis Risiko Finansial: Bahaya Denda Demurrage & Pelanggaran Escrow Talangan Pabean
   - 9.4 Analisis Risiko Hukum & Regulasi: Sanksi UU PDP No. 27/2022 & Notul Bea Cukai (SPTNP)
   - 9.5 Analisis Risiko Ketersediaan & Keamanan Siber: DoS Regex & Brute Force Portal Publik
10. [Peta Jalan Remediasi Terprioritisasi (Remediation Roadmap)](#10-peta-jalan-remediasi-terprioritisasi-remediation-roadmap)
    - 10.1 Fase 1: Hotfix Kritis & Pengamanan Basis Data Pengujian (Hari 1 s.d. 2)
    - 10.2 Fase 2: Konsolidasi Arsitektur, Eliminasi Dead Code & Hardcoded Fallbacks (Hari 3 s.d. 5)
    - 10.3 Fase 3: Modernisasi Test Suite, Perbaikan Skema & Pembuatan Model Factories (Minggu 2)
    - 10.4 Fase 4: Pengerasan Produksi Jangka Panjang & Integrasi Gateway Eksternal Live (Minggu 3 s.d. 4)
    - 10.5 Matriks Kriteria Penerimaan (Acceptance Criteria) & Definition of Done (DoD)

---

## 1. RINGKASAN EKSEKUTIF (EXECUTIVE SUMMARY)

### 1.1 Latar Belakang & Tujuan Audit
Audit forensik komprehensif ini dilakukan secara independen terhadap sistem aplikasi **PATS_CEISA (dikenal sebagai GENIE ERP v2.0)** yang beroperasi di direktori `c:\laragon\www\GENIE`. Sistem ini dirancang untuk mendigitalisasi dan mengotomasi seluruh rantai bisnis ekspor-impor, Pengurusan Jasa Kepabeanan (PPJK), pengurusan Delivery Order (DO) pelayaran, manajemen armada kontainer pelabuhan, tata kelola sumber daya manusia (HRM) dan absensi biometrik, penggajian (payroll) patuh pajak, penagihan klien (invoicing), kas/bank multi-rekening, serta akuntansi pembukuan berpasangan (*double-entry general ledger*) untuk **PT Agung Trans Solusindo**.

Tujuan audit ini adalah melakukan evaluasi menyeluruh tanpa kompromi (zero assumption) terhadap:
1. Kesesuaian implementasi aktual kode sumber terhadap 5 dokumen spesifikasi acuan otoritatif:
   - `docs/BRD_GENIE_ERP_v2.0.md` (Business Requirements Document)
   - `docs/PRD_GENIE_ERP_v2.0.md` (Product Requirements Document)
   - `docs/FSD_GENIE_ERP_v2.0.md` (Functional Specifications Document)
   - `docs/RISK_MANAGEMENT_GENIE_ERP.md` (ISO 31000 Risk Management Matrix)
   - `docs/FEATURE_AUDIT_REPORT.md` (28 Prior Remediation Points Catalog)
2. Kesiapan fungsional operasional CRUD dan transisi siklus hidup data (*lifecycle state transitions*).
3. Integritas konsistensi data lintas modul dan penjurnalan buku besar akuntansi otomatis.
4. Kepatuhan hukum dan regulasi nasional (UU Pelindungan Data Pribadi No. 27/2022, PPh 21 TER PMK 168/2023, PMK 71/2022 PPN 1.1%, dan Kepabeanan CEISA 4.0 DJBC).
5. Kualitas perangkat lunak, kebersihan arsitektur (*code hygiene*), eliminasi kode mati (*dead code*), serta ketahanan suite pengujian otomatis (*test harness integrity*).

Audit ini dijalankan dengan mematuhi mandat **Strictly Read-Only**: tidak ada file kode sumber aplikasi (`app/`, `routes/`, `resources/`, `database/`) yang dimodifikasi selama audit berlangsung.

---

### 1.2 Ruang Lingkup Sistem & Metodologi Audit
Ruang lingkup audit mencakup 12 modul fungsional utama, 40 spesifikasi fitur enterprise, 135 tabel basis data MySQL 8.4, 81 model Eloquent, 39 controller, 23 domain services, 3 background queue jobs, 29 berkas MCP domain tools, serta 32 berkas automated test suites.

Metodologi audit forensik yang digunakan:
1. **Pemeriksaan Statis & Analisis Semantik Kode:** Menelusuri seluruh deklarasi rute HTTP, middleware otorisasi, dependensi model, dan integrasi pustaka eksternal.
2. **Uji Eksekusi Runtime Terisolasi:** Menguji eksekusi migrasi, seeding, dan unit/feature tests menggunakan runtime terisolasi (SQLite persistent harness) untuk membuktikan perilaku runtime tanpa membahayakan data produksi lokal.
3. **Pencocokan Bukti Forensik Konkret:** Setiap temuan diverifikasi langsung ke file target, baris kode, jejak stack trace error, atau struktur tabel basis data.
4. **Analisis Akar Masalah Lapis Dalam (Deep Root Cause Analysis):** Mengidentifikasi sumber struktural masalah arsitektur, bukan sekadar mencatat gejala permukaan.

---

### 1.3 Evaluasi Kesehatan Multi-Dimensi (System Health Score)

Berdasarkan evaluasi audit independen lintas 5 dimensi kritis:

| No | Dimensi Evaluasi | Bobot | Skor | Kategori | Ringkasan Evaluasi Forensik |
|:---:|:---|:---:|:---:|:---:|:---|
| 1 | **Arsitektur & Integritas Basis Data** | 25% | **92 / 100** | **EXCELLENT** | 100% 33 migrasi berstatus `Ran` (135 tabel). 81 Model konsisten menerapkan `HasUuids` dan `SoftDeletes`. Observer akuntansi mengunci mutasi di level database. Terdapat sedikit redundansi controller warisan v1.0. |
| 2 | **Fungsionalitas & Cakupan CRUD** | 25% | **90 / 100** | **EXCELLENT** | Rantai proses bisnis P01 s.d. P19, Flat Root CEISA 4.0, TER PMK 168/2023, dan Double-Entry GL berfungsi autentik. Ditemukan 1 rute import apply hilang dan parameter email DO tergeser. |
| 3 | **Kepatuhan Regulasi & Keamanan** | 20% | **94 / 100** | **OUTSTANDING** | Kepatuhan UU PDP No. 27/2022 ditegakkan via disk privat dan enkripsi AES-256 (`face_data`, `salary_base`). RBAC 12 Roles native `HasMiddleware` aktif. Pre-Funding Escrow Gate pabean aktif. |
| 4 | **Kualitas Pengujian & Runtime** | 15% | **74 / 100** | **GOOD / NEEDS HARDENING** | 22 dari 32 suite pengujian (68.75%) lulus 100% secara solid. Namun, konfigurasi default `phpunit.xml` membahayakan database live via `RefreshDatabase`, 1 skema test akuntansi usang, dan regex PDF parser rentan DoS. |
| 5 | **UI/UX & Ergonomi Bento Grid** | 15% | **88 / 100** | **EXCELLENT** | Desain Apple HIG Bento Grid elegan, tabular nums monospace pada data angka, rate limiter portal publik aktif. Ditemukan direktori view terpecah antara `setting` dan `settings`. |
| **TOTAL** | **KESEHATAN SISTEM MENYELURUH** | **100%** | **88.6 / 100** | **TIER-1 ENTERPRISE READY** | **Sistem sangat matang dan siap produksi setelah 4 hotfix kritis diterapkan.** |

---

### 1.4 Ringkasan Temuan Audit Berdasarkan Tingkat Keparahan (Severity Summary)

Dari investigasi forensik menyeluruh, teridentifikasi total **16 temuan audit** yang diklasifikasikan ke dalam 4 tingkatan keparahan:

```
┌────────────────────────────────────────────────────────────────────────┐
│               DISTRIBUSI TEMUAN AUDIT SISTEM GENIE ERP                 │
├───────────────────┬──────────────┬─────────────────────────────────────┤
│ Tingkat Keparahan │ Jumlah Item  │ Klasifikasi Dampak Operasional      │
├───────────────────┼──────────────┼─────────────────────────────────────┤
│ 1. CRITICAL       │   4 Temuan   │ Menghambat runtime / potensi loss   │
│ 2. HIGH           │   4 Temuan   │ Kegagalan alur / misdirection data  │
│ 3. MEDIUM         │   5 Temuan   │ Redundansi kode / dead code / smell │
│ 4. LOW            │   3 Temuan   │ Inkonsistensi minor UI & test gaps  │
├───────────────────┼──────────────┼─────────────────────────────────────┤
│ TOTAL TEMUAN      │  16 Temuan   │ 100% Dilengkapi 15-Field Remediasi  │
└───────────────────┴──────────────┴─────────────────────────────────────┘
```

---

### 1.5 Prestasi Kunci Arsitektur & Kepatuhan Regulasi
Sistem PATS_CEISA v2.0 telah mencapai kemajuan teknis enterprise yang luar biasa:
1. **Penguncian Buku Akuntansi di Level Basis Data:** Melalui `JournalEntryObserver` dan `JournalLineObserver`, tidak ada satu pun transaksi yang dapat disisipkan, diubah, atau dihapus pada periode akuntansi tertutup (`is_closed = true`). Koreksi masa lalu diwajibkan menggunakan *Reverse Entry* transparan.
2. **Kepatuhan Privasi Biometrik UU PDP No. 27/2022:** Foto kehadiran pegawai dipindahkan sepenuhnya dari folder webroot publik ke direktori terisolasi `storage/app/private/attendances/` dan hanya dapat diakses melalui signed URL terenkripsi via `PrivateMediaController`. Data biometrik (`face_data`) dan gaji pokok pegawai dienkripsi menggunakan AES-256 di basis data MySQL.
3. **Escrow Gate Talangan Pabean:** Pemisahan penagihan reimbursable outlay vs pendapatan jasa PPN 1.1% PMK 71/2022, disertai gerbang penguncian pembayaran billing MPN G3 yang melarang pencairan kas sebelum dana importir klir di bank atau disetujui Direktur Utama (`R-OWN`).
4. **Arsitektur Model Context Protocol (MCP) Server:** Implementasi standar MCP protocol (transport SSE dan JSON-RPC 2.0) dengan 27 tools bisnis logistik yang memungkinkan agen kecerdasan buatan mengeksekusi operasi kepabeanan, keuangan, dan tracking secara terstruktur dan aman.

---

## 2. CAKUPAN SISTEM (SYSTEM COVERAGE)

### 2.1 Inventarisasi 12 Modul Fungsional & 40 Fitur Enterprise
Sistem terbagi ke dalam 12 modul bisnis yang mencakup 40 kapabilitas enterprise tingkat tinggi:

```
┌────────────────────────────────────────────────────────────────────────────────────────────────┐
│                   INVENTARISASI 12 MODUL FUNGSIONAL & FITUR ENTERPRISE                         │
├────────────────────────────┬───────────────────────────────────────────────────────────────────┤
│ Modul Fungsional           │ Fitur-Fitur Kunci yang Terverifikasi di Kode Sumber               │
├────────────────────────────┼───────────────────────────────────────────────────────────────────┤
│ 1. Pabean & CEISA 4.0      │ Draf & Transmisi Flat Root PIB BC 2.0 & PEB BC 3.0, H2H Manifes   │
│                            │ BC 1.1, TPS Online Gate Out Feed, Auto-Save Form 15s.             │
│ 2. Core Import & Validasi  │ F15 Asynchronous Batch ZIP Ingestion, OCR Teks, 10 Aturan         │
│                            │ Validasi Silang Pabean (V01-V10), Zip Bomb Security Defense.       │
│ 3. Core Workflow P01–P19   │ Cockpit Tracking 11 Pos Kargo, F16 Stage Skipping Engine,         │
│                            │ Approval Deadlock Resolution via R-DOC-SPV, Auto-Assign PIC.      │
│ 4. DO & Pelayaran          │ Transmisi Mailable Vendor DO (P09), Pemantauan Free Time Kontainer,│
│                            │ Predictive Demurrage Radar (H-3..H-1), Penyelesaian Biaya Lokal.  │
│ 5. Traffic & Lapangan      │ Web PWA Mobile, Unggah Foto 4-Tahap Behandle TPS, Gate Out        │
│                            │ Pelabuhan, Bukti Serah Terima Gudang (POD) Stempel Basah.         │
│ 6. HRM & Biometrik         │ Enkripsi Kolom AES-256 (`face_data`, `salary_base`), Disk Privat, │
│                            │ UU PDP Biometric Consent, 2-Year Purge Cron, Cosine 128-D Match.  │
│ 7. Payroll & Pajak Staf    │ TER PMK 168/2023 Kategori A, B, C, Plafon BPJS JP 2026,           │
│                            │ Rekonsiliasi Tahunan Desember Pasal 17, Form 1721-A1 PDF Resmi.   │
│ 8. Invoicing & Billing     │ Tagihan Klien P19 (Outlay Non-PPN vs Jasa PPN 1.1% PMK 71/2022),   │
│                            │ Pre-Funding Escrow Gate, Pembayaran SSPCP MPN G3 15-Digit.        │
│ 9. Finance & Kas/Bank      │ Multi-Rekening (BCA Operasional, Mandiri Talangan, Kasir Kas Kecil│
│                            │ Pelabuhan), Persetujuan Vendor Bill > 10 Juta via R-OWN.          │
│ 10. Akuntansi & GL         │ Bagan Akun Standar Forwarding (COA 1-5), Balanced Double-Entry    │
│                            │ Engine (Toleransi 0.05), Observer Lock Period, 1-Click Reversing. │
│ 11. CRM, WABA & Portal     │ Master Klien & Validasi Surat Kuasa 30 Hari, Master Vendor,       │
│                            │ Forwarder Quotation (Diskon >10% Gate), Meta Cloud WABA v19.0,    │
│                            │ Portal Publik Mandiri Token 64-Karakter (Rate Limit 60 req/min).  │
│ 12. AI Agent & MCP Server  │ OpenAI Customs Intelligence HS Code 8-Digit BTKI 2022, Server MCP │
│                            │ SSE + JSON-RPC 27 Tools, Multi-Template Official DomPDF Engine.   │
└────────────────────────────┴───────────────────────────────────────────────────────────────────┘
```

---

### 2.2 Arsitektur Pengendali Presentasi (39 Controllers)
Sebanyak 39 Controller terdistribusi dalam 3 namespace:
- **`App\Http\Controllers` (35 Controller):**
  Mengendalikan seluruh antarmuka web pengguna dan alur kerja operasional. Seluruh controller menerapkan `HasMiddleware` dari Laravel 11/13 dan memeriksa hak akses via `Gate::authorize`.
  *Daftar:* `AccountingController`, `AiAgentController`, `BatchImportController`, `BC20Controller`, `BC30Controller`, `CashBankController`, `CeisaConfigController`, `ClientInvoiceController`, `ClientPortalController`, `Controller`, `CrmController`, `DashboardController`, `DoController` (*Dead Code*), `DoWorkflowController`, `FinanceController`, `HrmController`, `InboxController`, `McpIntegrationController`, `PdfController`, `PrivateMediaController`, `ProfileController`, `ProjectTrackingController`, `QuotationController`, `RoleController`, `SalesVisitController`, `SettingController`, `ShipmentProjectController`, `SocialMediaController`, `StaffPortalController`, `TaxController`, `TodoController`, `TrafficController` (*Dead Code*), `TrafficWorkflowController`, `VendorBillController`, `VendorController`, `WhatsAppController`.
- **`App\Http\Controllers\Api` (2 Controller):**
  `CeisaApiController` (OpenAPI CEISA 4.0 DJBC) dan `McpCustomsAgentController` (Server Model Context Protocol).
- **`App\Http\Controllers\Auth` (1 Controller):**
  `AuthController` (Otentikasi, proteksi brute-force, dan 2FA OTP email untuk `R-OWN` dan `R-FIN`).

---

### 2.3 Arsitektur Lapisan Layanan Bisnis (23 Domain Services)
Logika bisnis enterprise diisolasi secara ketat dalam 23 Domain Services di `app/Services/`:
- **Accounting (1):** `AccountingService` (Mesin jurnal ganda seimbang, penerbitan invoice P19, pembayaran vendor bill, escrow talangan pabean, penguncian periode buku).
- **Ai (1):** `OpenAiCustomsService` (Klasifikasi pos tarif HS Code 8-digit BTKI 2022, ekstraksi packing list).
- **Ceisa (11):** `CeisaAuthService`, `CeisaBtkiTariffSyncService`, `CeisaClientService`, `CeisaDocumentService`, `CeisaDocumentSyncService`, `CeisaExportImportService`, `CeisaHsCodeService`, `CeisaKursService`, `CeisaManifestService`, `DocumentBC20Service`, `DocumentBC30Service`.
- **Crm (1):** `MetaWabaService` (Klien resmi Meta Cloud Graph API v19.0, validasi signature HMAC webhook, auto-reply pelacakan B/L).
- **Hrm (4):** `AnnualTaxReconciliationService` (Rekonsiliasi PPh 21 Pasal 17 Desember & Form 1721-A1 PDF), `BiometricVerificationService` (Pencocokan Cosine Similarity vektor wajah 128-D), `FaceVerificationService`, `TaxTerCalculatorService` (Tarif Efektif Rata-Rata PMK 168/2023 Kategori A, B, C).
- **Shipment (5):** `BatchImportService` (Ekstraksi ZIP, anti zip bomb), `CrossDocumentValidationService` (10 Aturan validasi silang V01-V10), `DocumentExtractionService` (Ekstraksi teks PDF invoice/packing list), `ShipmentWorkflowService` (Transisi rantai operasional P01-P19), `StageJumpService` (Mesin lompat tahap F16).

---

### 2.4 Protokol Agen Kecerdasan Buatan & Server MCP (27 Tools Aktif)
Direktori `app/Domain/Mcp/Tools/` mengimplementasikan server Model Context Protocol (MCP) dengan 27 tools aman:
1. Akuntansi & GL: `AccountingGetProfitLossTool`.
2. CRM & Komunikasi: `CrmSearchClientsTool`, `CrmSendWhatsappTool`.
3. Pabean & CEISA: `CustomsCheckCeisaStatusTool`, `CustomsCheckHsCodeTool`, `CustomsDetectShipmentTool`, `CustomsGenerateDeclarationTool`, `CustomsGetKursPajakTool`, `CustomsParsePackingListTool`.
4. Operasional DO: `DoManageWorkflowTool`.
5. Keuangan & Kas: `FinanceGetCashBankBalancesTool`, `FinanceGetSummaryTool`, `FinanceManageInvoiceTool`, `FinanceManageVendorBillTool`, `FinanceRecordCashMutationTool`.
6. SDM & Absensi: `HrmGetAttendanceSummaryTool`, `HrmGetPayrollSummaryTool`.
7. Otorisasi & Governance: `McpApproveActionTool`, `McpDeleteRecordTool`, `McpListPendingApprovalsTool`.
8. Proyek & Pelacakan: `ShipmentCreateProjectTool`, `ShipmentGetDetailTool`, `ShipmentListProjectsTool`, `ShipmentUpdateStatusTool`.
9. Perpajakan: `TaxGetSummaryTool`.
10. Lapangan & Traffic: `TrafficAssignFleetTool`, `TrafficTrackContainersTool`.

---

### 2.5 Struktur Basis Data: 33 Migrasi, 135 Tabel, dan 81 Model Eloquent
- **Status Migrasi:** Seluruh 33 migrasi berstatus `Ran` pada database `PATS_CEISA`.
- **Standar Integritas Data:**
  - 100% tabel transaksional menggunakan UUID v4 sebagai Primary Key.
  - 100% model transaksional menerapkan trait `SoftDeletes` dan `Auditable`.
  - Pola relasi Foreign Key ditegakkan secara ketat pada tabel relasional.
- **Model Eloquent (81 Model):**
  Seluruh entitas domain terdefinisi di `app/Models/` dan mewakili siklus bisnis forwarder dari hulu ke hilir.

---

### 2.6 Pemrosesan Latar Belakang (Queue Jobs) & Penjadwal Tugas (Console Scheduler)
- **Background Queue Workers (`app/Jobs/Import/`):**
  1. `ExtractBatchZipJob`: Memproses unzipping berkas batch asinkron dan memeriksa rasio kompresi (Zip Bomb).
  2. `ProcessDocumentOcrJob`: Mengekstrak teks dokumen PDF dan mencocokkan bounding box entitas pabean.
  3. `RunCrossValidationJob`: Mengeksekusi 10 aturan validasi silang (V01 s.d. V10).
- **Penjadwal Tugas Cron (`routes/console.php`):**
  - `shipment:check-demurrage`: Dijalankan setiap hari pukul `07:30` untuk mendeteksi sisa free time kontainer.
  - `biometric:purge-expired`: Dijalankan setiap hari pukul `02:00` untuk memusnahkan data biometrik yang telah melewati retensi 2 tahun sesuai UU PDP.
  - `ceisa:sync-all`: Dijalankan setiap hari pukul `04:00` untuk sinkronisasi dokumen aktif dengan CEISA.
  - `ceisa:sync-hs-codes`: Dijalankan setiap hari Minggu pukul `03:00` untuk memperbarui pos tarif BTKI 2022.

---

## 3. CAKUPAN OPERASIONAL CRUD & SIKLUS HIDUP FUNGSIONAL (CRUD & FUNCTIONAL COVERAGE)

### 3.1 Matriks Verifikasi CRUD 14 Model Transaksional Inti

Berdasarkan pemeriksaan kode sumber, skema basis data, dan eksekusi pengujian:

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                               MATRIKS VERIFIKASI CRUD 14 ENTITAS UTAMA SISTEM                                    │
├─────────────────────┬──────────────┬──────────────┬──────────────┬──────────────┬────────────────────────────────┤
│ Model Entitas       │ Create (C)   │ Read (R)     │ Update (U)   │ Delete (D)   │ Integritas Siklus Hidup Data   │
├─────────────────────┼──────────────┼──────────────┼──────────────┼──────────────┼────────────────────────────────┤
│ 1. `Shipment`       │ PASS (UUID)  │ PASS (Index) │ PASS (Stages)│ PASS (Soft)  │ Status P01 s.d. P19 terkunci   │
│ 2. `ShipmentDocument│ PASS (Upload)│ PASS (Stream)│ PASS (Verify)│ PASS (Soft)  │ Validasi ekstensi & ukuran     │
│ 3. `Client`         │ PASS (NIB)   │ PASS (Index) │ PASS (Modal) │ PASS (Guard) │ Blokir hapus jika ada order    │
│ 4. `ShippingVendor` │ PASS (Code)  │ PASS (Index) │ PASS (Edit)  │ PASS (Guard) │ Blokir hapus jika ada shipment │
│ 5. `CustomsBilling` │ PASS (MPN G3)│ PASS (Detail)│ PASS (NTPN)  │ PASS (Guard) │ Terikat Pre-Funding Escrow     │
│ 6. `ClientInvoice`  │ PASS (Auto)  │ PASS (PDF/UI)│ PASS (Status)│ PASS (Soft)  │ Pisahkan talangan vs PPN 1.1%  │
│ 7. `VendorBill`     │ PASS (AP)    │ PASS (Aging) │ PASS (Apprv) │ PASS (Soft)  │ Wajib approval R-OWN > 10jt    │
│ 8. `CashBankAccount`│ PASS (Bank)  │ PASS (Saldo) │ PASS (Mutasi)│ PASS (Guard) │ Blokir saldo minus (Overdrawn) │
│ 9. `ChartOfAccount` │ PASS (COA)   │ PASS (Pohon) │ PASS (Nama)  │ PASS (Guard) │ Dilarang hapus jika ada jurnal │
│ 10. `JournalEntry`  │ PASS (Bal)   │ PASS (Buku)  │ PASS (Lock)  │ PASS (Lock)  │ Observer tolak aksi di locked  │
│ 11. `Employee`      │ PASS (Encr)  │ PASS (Profil)│ PASS (Update)│ PASS (Soft)  │ Enkripsi AES-256 face & salary │
│ 12. `AttendanceLog` │ PASS (GPS)   │ PASS (Rekap) │ PASS (Koreksi│ PASS (Purge) │ Validasi Geofence & UU PDP     │
│ 13. `Quotation`     │ PASS (Tarif) │ PASS (PDF)   │ PASS (Diskon)│ PASS (Soft)  │ Diskon > 10% wajib acc R-OWN   │
│ 14. `Todo`          │ PASS (Kanban)│ PASS (Index) │ PASS (Status)│ PASS (Soft)  │ Check constraint status enum   │
└─────────────────────┴──────────────┴──────────────┴──────────────┴──────────────┴────────────────────────────────┘
```

---

### 3.2 Penanganan Validasi Sisi Server & Sisi Klien
1. **Server-Side Request Validation:**
   Form Requests dan inline validator diterapkan pada 100% titik masukan formulir. Input angka keuangan divalidasi `numeric|min:0`, kode HS divalidasi format numerik 8-digit BTKI, dan berkas diunggah dengan pembatasan MIME type `application/pdf,image/jpeg,image/png` dengan batas ukuran maksimum 10MB.
2. **Client-Side Dirty-Form Guard & Auto-Save:**
   Pada formulir wizard PIB BC 2.0 (`resources/views/bc20/create.blade.php`), terpasang Alpine.js auto-save ke local storage setiap 15 detik dan listener `window.addEventListener('beforeunload', ...)` yang memunculkan peringatan konfirmasi jika pengguna mencoba menutup tab saat terdapat isian yang belum tersimpan.

---

### 3.3 Verifikasi Transisi Status Alur Berantai P01 s.d. P19

Alur operasional logistik diatur oleh mesin status terpadu yang memastikan transisi berurutan dan mengunci ketergantungan dokumen:

```
[P01: Order Masuk] ──► [P02: Checklist 14 Berkas] ──► [P03: Upload B/L & Invoice]
                                                               │
┌──────────────────────────────────────────────────────────────┘
▼
[P04: AI Ekstraksi HS & CIF] ──► [P05: Verifikasi Supervisor (Four-Eyes)]
                                               │
┌──────────────────────────────────────────────┘
▼
[P06: Submit Flat Root CEISA 4.0] ──► [P07: Billing MPN G3 15-Digit Terbit]
                                               │ (Handoff ke Tim DO)
┌──────────────────────────────────────────────┘
▼
[P08: Pilih Vendor Pelayaran] ──► [P09: Email Vendor DO Riil] ──► [P10: Radar Free Time H-3..H-1]
                                                                             │
┌────────────────────────────────────────────────────────────────────────────┘
▼
[P11: Verifikasi Bayar Biaya Lokal] ──► [P12: Rilis DO Resmi (DO_ISSUED)]
                                                       │ (Handoff ke Tim Lapangan)
┌──────────────────────────────────────────────────────┘
▼
[P13: Kapal Tiba ARRIVED] ──► [P14: Penjaluran Pabean (Hijau/Kuning/Merah)]
                                               │
┌──────────────────────────────────────────────┘
▼
[P15: Foto 4-Tahap Behandle TPS] ──► [P16: Gate Out Pelabuhan] ──► [P17: Serah Terima Gudang POD]
                                                                             │
┌────────────────────────────────────────────────────────────────────────────┘
▼
[P18: Otomasi Terbit Invoice Klien P19] ──► [P19: Setor Billing NTPN & Jurnal Laba Rugi]
```

---

### 3.4 Penegakan Aturan Bisnis Inti (Business Rules BR-01 s.d. BR-20)
Sistem memverifikasi kepatuhan terhadap 20 aturan bisnis spesifikasi:
- **BR-01 (Ambang Persetujuan Vendor Bill > 10 Juta):** Terverifikasi aktif di `VendorBillController::store`. Nominal > Rp 10.000.000 otomatis berstatus `PENDING_APPROVAL` dan memerlukan persetujuan `R-OWN`.
- **BR-02 (Ambang Diskon Quotation > 10%):** Terverifikasi aktif di `QuotationController::store`. Diskon di atas 10% mengunci pengiriman penawaran hingga disetujui `R-OWN`.
- **BR-03 (Masa Berlaku Surat Kuasa PPJK >= 30 Hari):** Terverifikasi aktif di `ShipmentProjectController::store`. Proyek baru ditolak jika masa berlaku Surat Kuasa klien < 30 hari kalender.
- **BR-04 (Prinsip Four-Eyes Dokumen Pabean):** Terverifikasi aktif di `ShipmentProjectController::verifyDocuments`. Pengajuan draf ke CEISA mewajibkan approval akun bersertifikasi `R-DOC-SPV`.
- **BR-05 (Pre-Funding Escrow Gate Talangan Pabean):** Terverifikasi aktif di `AccountingService::recordCustomsBillingPayment`. Kas kasir dilarang membayarkan MPN G3 pabean jika saldo dana talangan klien belum berstatus `FUNDS_CLEARED`, kecuali terdapat bypass Direktur Utama.
- **BR-06 (Alih Tugas Otomatis PIC Cuti):** Terverifikasi aktif di `LeaveRequest`. Ketika permohonan cuti disetujui, PIC pada seluruh shipment aktif otomatis dialihkan ke staf pengganti yang ditunjuk.
- **BR-07 (Pemisahan Penagihan P19 Reimbursable vs Jasa PPN 1.1%):** Terverifikasi aktif di `ClientInvoiceController`. Tagihan memisahkan reimbursable pabean non-PPN dari imbalan jasa yang dipungut PPN 1.1% PMK 71/2022.
- **BR-08 (PPh 21 TER PMK 168/2023 & Rekonsiliasi Desember 1721-A1):** Terverifikasi aktif di `TaxTerCalculatorService` dan `AnnualTaxReconciliationService`.
- **BR-09 (Plafon Iuran BPJS JP Rp 11.086.300):** Terverifikasi aktif di `SystemSettingSeeder` dan parameter penggajian.
- **BR-10 & BR-11 (Enkripsi Biometrik & Purge 2 Tahun UU PDP):** Terverifikasi aktif via casting `'encrypted'` pada model `Employee` dan perintah konsol `biometric:purge-expired`.
- **BR-12 & BR-13 (Observer Lock Period & Keseimbangan Jurnal GL):** Terverifikasi aktif via `JournalEntryObserver` dan toleransi selisih 0.05 pada `AccountingService`.
- **BR-14 (Peringatan Demurrage Berjenjang H-3/H-2/H-1):** Dirancang pada `CheckDemurrageAlertsCommand` (mengalami kendala runtime yang dirinci pada Bab 4).
- **BR-15 (Antrean Asinkron F15):** Terverifikasi aktif via `ExtractBatchZipJob` dan `ProcessDocumentOcrJob`.
- **BR-16 (Isolasi Finansial Lompat Tahap F16):** Terverifikasi aktif via opsi `OPENING_BALANCE` di `StageJumpService`.
- **BR-17 (Rate Limiting Portal Publik 60 req/min):** Terverifikasi aktif via middleware `throttle:60,1` pada rute `/portal/track/{token}`.
- **BR-18 (Controller-Level RBAC Native):** Terverifikasi aktif via implementasi `HasMiddleware` pada 100% controller administratif.
- **BR-19 (Append-Only Audit Trail):** Terverifikasi aktif via trait `Auditable` pada 16 model transaksional.
- **BR-20 (Apple HIG Bento Grid Design):** Terverifikasi aktif pada seluruh template Blade utama.

---

### 3.5 Pengujian Kasus Batas (Edge Cases) & Ketahanan Sistem
1. **Zip Bomb Attack Defense:** Terverifikasi pada `BatchImportQueueAndBombTest`. Pengunggahan arsip dengan rasio kompresi 150:1 atau uncompressed size > 2GB digagalkan oleh `ExtractBatchZipJob` dengan melempar `SecurityException`.
2. **Brute Force Rate Limiting:** Terverifikasi pada `PublicPortalRateLimitTest`. Penembakan 120 request berturut-turut ke endpoint portal publik menghasilkan 60 respon sukses (HTTP 200) diikuti oleh pemblokiran `HTTP 429 Too Many Requests` pada request ke-61 dan seterusnya.
3. **GPS Geofencing Palsu:** Terverifikasi pada `HrmModelTest`. Pengiriman koordinat presensi dengan selisih jarak 1.500 meter dari koordinat kantor Mega Kuningan ditolak sistem dengan pesan di luar radius lokasi kerja.
4. **Rekonsiliasi Pajak Desember Lebih Potong:** Terverifikasi pada `AnnualTaxReconciliationService`. Karyawan dengan kredit TER Masa Jan–Nov yang melebihi PPh 21 Pasal 17 setahun penuh menghasilkan nilai negatif yang secara otomatis dijadikan penambah gaji bersih (restitusi internal) pada slip Desember.

---

## 4. TEMUAN AUDIT BERDASARKAN TINGKAT KEPARAHAN (FINDINGS BY SEVERITY)

Seluruh temuan berikut mematuhi secara ketat standar **15-Field Finding Structure**:

### 4.1 Temuan Tingkat Kritis (CRITICAL Severity)

---

#### AUDIT-CRIT-01
- **1. ID Temuan:** AUDIT-CRIT-01
- **2. Judul:** Konfigurasi Default `phpunit.xml` Mengarah ke Basis Data Aktif `PATS_CEISA` Disertai Trait `RefreshDatabase` yang Menghapus Seluruh Tabel
- **3. Modul / Fitur Terdampak:** DevSecOps / Testing Infrastructure / Database Storage
- **4. Severity:** CRITICAL
- **5. Kategori Masalah:** Data Integrity / DevSecOps Risk / Accidental Data Loss
- **6. Langkah Reproduksi:**
  1. Buka terminal proyek di `c:\laragon\www\GENIE`.
  2. Jalankan perintah `vendor\bin\phpunit tests/Feature/Bc20CompleteLifecycleTest.php` atau `php artisan test` tanpa menyertakan konfigurasi lingkungan khusus.
  3. Periksa basis data MySQL `PATS_CEISA` via HeidiSQL atau CLI.
- **7. Kondisi Aktual:**
  `phpunit.xml` baris 27-28 mendefinisikan:
  ```xml
  <env name="DB_CONNECTION" value="mysql"/>
  <env name="DB_DATABASE" value="PATS_CEISA"/>
  ```
  Sebanyak 18 test suite fitur mendeklarasikan `use RefreshDatabase;`. Saat dieksekusi pada MySQL, Laravel menjalankan perintah DDL `DROP TABLE` pada seluruh 135 tabel basis data pengembangan dan menjalankan ulang migrasi, menghapus seketika seluruh data operasional, draf pabean, konfigurasi setting, dan akun pengguna riil.
- **8. Expected Behavior:**
  Pengujian otomatis harus terisolasi penuh pada basis data terpisah (e.g. `pats_ceisa_test` atau basis data file SQLite tersendiri) sehingga eksekusi test suite apa pun tidak pernah menyentuh atau menghapus basis data pengembangan/operasional `PATS_CEISA`.
- **9. Bukti Hasil Pemeriksaan:**
  - `phpunit.xml:27-28`: Nilai `DB_DATABASE` bernilai `PATS_CEISA`.
  - `.env:26`: `DB_DATABASE=PATS_CEISA`.
  - Berkas test dengan `RefreshDatabase`: `AccountingAndTaxFeatureTest.php:25`, `Bc20CompleteLifecycleTest.php:19`, `DocumentEditAndRollbackPermissionsTest.php:16`, `RbacAuthenticationTest.php:17`.
- **10. Akar Masalah:**
  Template konfigurasi `phpunit.xml` bawaan tidak dialihkan ke basis data pengujian khusus (`pats_ceisa_testing`) atau SQLite file terisolasi sebelum sprint pengembangan dimulai.
- **11. Dampak Bisnis dan Teknis:**
  Risiko kehancuran data pengembangan total setiap kali developer atau pipeline CI menjalankan perintah tes standar. Developer kehilangan riwayat draf pabean, catatan transaksi, dan harus melakukan re-seed manual yang memakan waktu.
- **12. Data atau Modul Lain yang Terdampak:**
  Seluruh 135 tabel di basis data `PATS_CEISA`.
- **13. Rekomendasi Perbaikan:**
  Ubah konfigurasi `phpunit.xml` agar menggunakan basis data terisolasi:
  ```xml
  <env name="DB_CONNECTION" value="sqlite"/>
  <env name="DB_DATABASE" value=":memory:"/>
  ```
  Atau jika menggunakan MySQL, arahkan ke database test terpisah:
  ```xml
  <env name="DB_DATABASE" value="pats_ceisa_test"/>
  ```
- **14. Prioritas Implementasi:** P0 (Bloker Segera)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Jalankan `vendor\bin\phpunit --testsuite=Unit` dan pastikan basis data MySQL `PATS_CEISA` tidak mengalami perubahan tabel atau penurunan jumlah record.

---

#### AUDIT-CRIT-02
- **1. ID Temuan:** AUDIT-CRIT-02
- **2. Judul:** Lonjakan Konsumsi CPU Ekstrem (>400s CPU) dan Pembekuan Server Akibat Regular Expression Backtracking pada `DocumentExtractionService` Saat Memproses Berkas PDF Non-Standar
- **3. Modul / Fitur Terdampak:** Core Import F15 / `DocumentExtractionService` / Unggah Dokumen Pabean
- **4. Severity:** CRITICAL
- **5. Kategori Masalah:** Runtime Crash / Resource Exhaustion / Denial of Service (DoS)
- **6. Langkah Reproduksi:**
  1. Jalankan test suite `tests/Feature/TimDocumentWorkflowTest.php`.
  2. Test membuat berkas PDF tiruan biner acak via `UploadedFile::fake()->create('dokumen.pdf', 300, 'application/pdf')`.
  3. Dokumen diunggah ke rute `core.document.upload`.
  4. Controller memanggil `DocumentExtractionService::extractTextFromFile`.
- **7. Kondisi Aktual:**
  Proses PHP membeku (*hang*) mengonsumsi 100% dari satu inti CPU selama lebih dari 400 detik dan menggunakan memori lebih dari 467 MB RAM. Web thread terkunci dan tidak pernah mengembalikan respon HTTP.
- **8. Expected Behavior:**
  `DocumentExtractionService` harus memeriksa validitas struktur berkas PDF (keberadaan tabel cross-reference / xref atau header `%PDF-`) sebelum melakukan ekstraksi mendalam. Jika pustaka parser gagal, proses fallback harus memiliki batas waktu (timeout) ketat atau menolak berkas korup tanpa memasuki rekursi regex tak terbatas.
- **9. Bukti Hasil Pemeriksaan:**
  - `app/Services/Shipment/DocumentExtractionService.php:84`:
    ```php
    preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $matches);
    ```
  - Saat parser Smalot gagal membaca PDF tanpa struktur xref, baris 84 mengeksekusi ekspresi reguler unanchored `.*` pada string biner acak ratusan kilobyte, memicu catastrophic backtracking pada mesin PCRE PHP.
- **10. Akar Masalah:**
  Penggunaan quantifiers ungreedy/greedy `(.*?)` dengan modifier `/s` pada payload biner acak tanpa pembatasan panjang substring masukan dan tanpa validasi header PDF yang sah.
- **11. Dampak Bisnis dan Teknis:**
  Potensi serangan Denial of Service (DoS) nyata. Jika importir atau pengguna mengunggah berkas PDF rusak atau korup, worker web server HTTP atau queue job akan membeku, menguras sumber daya komputasi server, dan menyebabkan status HTTP 504 Gateway Timeout bagi seluruh pengguna ERP lainnya.
- **12. Data atau Modul Lain yang Terdampak:**
  Modul F15 Batch Import, Modul Core Dokumen Pabean, dan antrean `ProcessDocumentOcrJob`.
- **13. Rekomendasi Perbaikan:**
  1. Tambahkan validasi awal sebelum membuka stream: pastikan berkas diawali `%PDF-` dan memiliki penutup `%%EOF`.
  2. Batasi pembacaan regex stream hanya pada berkas berukuran wajar dengan batas traversal karakter maksimum.
  3. Pasang mekanisme `try-catch` dengan batasan execution time atau gunakan ekstensi `pdfdetox` / `pdftotext` native.
- **14. Prioritas Implementasi:** P0 (Bloker Segera)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Jalankan `vendor\bin\phpunit tests/Feature/TimDocumentWorkflowTest.php` dan pastikan seluruh 13 tes selesai dalam waktu kurang dari 10 detik tanpa lonjakan CPU tak terkendali.

---

#### AUDIT-CRIT-03
- **1. ID Temuan:** AUDIT-CRIT-03
- **2. Judul:** Kegagalan Eksekusi Scheduled Command `CheckDemurrageAlertsCommand` Akibat Kolom Tidak Ditemukan (`user_id`) dan Pelanggaran Check Constraint Status Enum
- **3. Modul / Fitur Terdampak:** DO & Pelayaran / Pemantauan Demurrage / Console Scheduler
- **4. Severity:** CRITICAL
- **5. Kategori Masalah:** Runtime Crash / Data Integrity / Demurrage Financial Loss Risk
- **6. Langkah Reproduksi:**
  1. Pastikan terdapat shipment yang mendekati sisa free time (H-3 s.d. H-1).
  2. Jalankan perintah artisan secara manual via CLI:
     ```powershell
     php artisan shipment:check-demurrage
     ```
  3. Perhatikan output jejak eksepsi pada terminal.
- **7. Kondisi Aktual:**
  Perintah konsol melempar eksepsi fatal dan berhenti mendadak:
  `SQLSTATE[23000]: Integrity constraint violation: 19 CHECK constraint failed: status` dan `Column not found: user_id`.
  Tidak ada satu pun notifikasi atau tiket peringatan demurrage yang berhasil dibuat di sistem.
- **8. Expected Behavior:**
  Perintah konsol berjalan mulus setiap hari pukul `07:30`, membaca sisa free time kontainer, menghitung selisih hari, dan menerbitkan tiket tugas `Todo` berkategori `DO_OPERATIONS` kepada Operations Manager serta mengirimkan peringatan eskalasi jika free time kritis.
- **9. Bukti Hasil Pemeriksaan:**
  - `app/Console/Commands/CheckDemurrageAlertsCommand.php:70`:
    `'user_id' => $opsManager->id,` (Kolom yang benar pada tabel `todos` adalah `assigned_to_user_id`).
  - `app/Console/Commands/CheckDemurrageAlertsCommand.php:76`:
    `'status' => 'PENDING',`
  - `database/migrations/2026_11_12_000003_sprint_4_todos_sales_and_visits.php:21`:
    Kolom `status` pada tabel `todos` diproteksi check constraint:
    `$table->enum('status', ['TODO', 'IN_PROGRESS', 'WAITING_EXTERNAL', 'DONE', 'CANCELLED'])->default('TODO');`
    Nilai string `'PENDING'` tidak ada dalam daftar enum yang sah!
- **10. Akar Masalah:**
  Pengembang command menuliskan atribut model `Todo` menggunakan asumsi kolom generik tanpa menyelaraskan dengan migrasi sprint 4 basis data.
- **11. Dampak Bisnis dan Teknis:**
  Fungsi radar peringatan dini demurrage (BR-14) lumpuh total secara diam-diam (*silent failure*) di cron harian. Kontainer kargo impor importir di pelabuhan dapat melewati batas free time tanpa diketahui tim DO, memicu tagihan denda demurrage & detention dari maskapai pelayaran senilai USD 50–150 per hari per kontainer (risiko finansial jutaan hingga ratusan juta rupiah).
- **12. Data atau Modul Lain yang Terdampak:**
  Tabel `todos`, modul Delivery Order, dashboard operasional DO, dan notifikasi WhatsApp eskalasi.
- **13. Rekomendasi Perbaikan:**
  Ubah kode di `CheckDemurrageAlertsCommand.php`:
  Ganti `'user_id'` menjadi `'assigned_to_user_id'` dan ganti `'status' => 'PENDING'` menjadi `'status' => 'TODO'`.
- **14. Prioritas Implementasi:** P0 (Bloker Segera)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Eksekusi `php artisan shipment:check-demurrage` dan jalankan `vendor\bin\phpunit tests/Feature/Logistics/DoReleaseAndDemurrageTest.php`. Pastikan tes nomor 4 lulus tanpa error constraint violation.

---

#### AUDIT-CRIT-04
- **1. ID Temuan:** AUDIT-CRIT-04
- **2. Judul:** Ketiadaan Deklarasi Rute `core.import.doc.apply` di `routes/web.php` Memicu `RouteNotFoundException` Fatal pada Alur Impor Berkas F15
- **3. Modul / Fitur Terdampak:** Core Import F15 / Asynchronous Document Processing
- **4. Severity:** CRITICAL
- **5. Kategori Masalah:** Runtime Crash / Specification Gap / Broken Navigation
- **6. Langkah Reproduksi:**
  1. Akses halaman tinjauan ekstraksi dokumen batch impor di `/core/import/{batchId}/review`.
  2. Klik tombol konfirmasi untuk menerapkan hasil ekstraksi ke draf shipment, atau jalankan test suite:
     ```powershell
     vendor\bin\phpunit --filter test_batch_import_and_deletion tests/Feature/BulkDraftAndDeleteAssetsTest.php
     ```
- **7. Kondisi Aktual:**
  Aplikasi melempar eksepsi runtime `Symfony\Component\Routing\Exception\RouteNotFoundException`:
  `Route [core.import.doc.apply] not defined` pada baris pengujian 358 dan memicu HTTP 500 pada peramban pengguna.
- **8. Expected Behavior:**
  Rute `core.import.doc.apply` harus terdaftar di `routes/web.php` dan terhubung ke metode controller yang memetakan data hasil ekstraksi dokumen ke kolom-kolom draf PIB BC 2.0 atau tabel shipment terkait.
- **9. Bukti Hasil Pemeriksaan:**
  - `tests/Feature/BulkDraftAndDeleteAssetsTest.php:358`: Memanggil `route('core.import.doc.apply', $batch->id)`.
  - `routes/web.php:300-315`: Grup rute `core.import` hanya mendaftarkan:
    - `index` (`GET /core/import`)
    - `store` (`POST /core/import`)
    - `show` (`GET /core/import/{id}`)
    - `destroy` (`DELETE /core/import/{id}`)
    - `review` (`GET /core/import/{id}/review`)
    - `updateField` (`POST /core/import/field/{id}`)
    - `jump` (`POST /core/import/jump`)
    Rute `core.import.doc.apply` sama sekali tidak dideklarasikan.
- **10. Akar Masalah:**
  Pengembang mengimplementasikan logika ekstraksi dokumen tetapi lupa menambahkan deklarasi rute POST untuk penerapan dokumen ke berkas rute web.
- **11. Dampak Bisnis dan Teknis:**
  Pengguna yang telah mengunggah berkas ZIP massal dan meninjau hasil OCR tidak dapat menerapkan hasil parsing dokumen menjadi draf kepabeanan, menyebabkan alur kerja F15 terputus di tengah jalan.
- **12. Data atau Modul Lain yang Terdampak:**
  Modul Batch Import F15, tabel `import_batches`, tabel `import_documents`, dan pembentukan draf PIB BC 2.0.
- **13. Rekomendasi Perbaikan:**
  Tambahkan definisi rute pada `routes/web.php` di dalam grup `core/import`:
  ```php
  Route::post('/{id}/apply', [BatchImportController::class, 'applyDocuments'])->name('core.import.doc.apply');
  ```
  Dan pastikan metode `applyDocuments` di `BatchImportController` menangani pemindahan data dengan benar.
- **14. Prioritas Implementasi:** P0 (Bloker Segera)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Jalankan `vendor\bin\phpunit tests/Feature/BulkDraftAndDeleteAssetsTest.php` dan pastikan seluruh 8 tes lulus 100%.

---

### 4.2 Temuan Tingkat Tinggi (HIGH Severity)

---

#### AUDIT-HIGH-01
- **1. ID Temuan:** AUDIT-HIGH-01
- **2. Judul:** Ketidakcocokan Kolom Skema Basis Data pada Suite Pengujian `AccountingAndTaxFeatureTest.php` Menyebabkan Kegagalan Otomatis Pengujian Akuntansi
- **3. Modul / Fitur Terdampak:** Test Suite / Modul Akuntansi & GL / Penguncian Periode Buku
- **4. Severity:** HIGH
- **5. Kategori Masalah:** Data Integrity / Schema Inconsistency / Test Suite Failure
- **6. Langkah Reproduksi:**
  1. Jalankan pengujian fitur akuntansi dan pajak:
     ```powershell
     vendor\bin\phpunit tests/Feature/AccountingAndTaxFeatureTest.php
     ```
  2. Amati kegagalan pada baris 48-51.
- **7. Kondisi Aktual:**
  Pengujian gagal dengan error:
  `SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: accounting_periods.name`.
- **8. Expected Behavior:**
  Model `AccountingPeriod` harus dibuat menggunakan kolom-kolom yang sesuai dengan skema migrasi resmi sehingga pengujian penguncian periode buku berjalan sukses.
- **9. Bukti Hasil Pemeriksaan:**
  - `tests/Feature/AccountingAndTaxFeatureTest.php:48-55`:
    ```php
    AccountingPeriod::create([
        'year' => 2026,
        'month' => 1,
        'status' => 'CLOSED',
        'closed_at' => now(),
        'notes' => 'Periode audit Januari dikunci',
    ]);
    ```
  - Skema tabel `accounting_periods` pada migrasi `database/migrations/2026_11_12_000001_sprint_1_financial_integrity_and_audit.php:29-39`:
    - `name` (VARCHAR 50 NOT NULL)
    - `start_date` (DATE NOT NULL)
    - `end_date` (DATE NOT NULL)
    - `is_closed` (BOOLEAN DEFAULT FALSE)
    - `closed_by` (UUID NULLABLE)
    Kolom `year`, `month`, `status`, dan `notes` sama sekali tidak ada pada tabel!
- **10. Akar Masalah:**
  Penulis tes menulis kode pengujian menggunakan asumsi rancangan awal tabel sebelum migrasi Sprint 1 distandarisasi ke format nama periode dan rentang tanggal.
- **11. Dampak Bisnis dan Teknis:**
  Suite pengujian akuntansi gagal memverifikasi mekanisme penting penguncian periode buku secara berulang, memberikan persepsi palsu bahwa fitur akuntansi rusak, atau sebaliknya jika diabaikan dapat meloloskan regresi di masa depan.
- **12. Data atau Modul Lain yang Terdampak:**
  `AccountingPeriod`, `JournalEntryObserver`, dan pipeline CI/CD.
- **13. Rekomendasi Perbaikan:**
  Perbaiki pemanggilan model pada `tests/Feature/AccountingAndTaxFeatureTest.php:48-55`:
  ```php
  AccountingPeriod::create([
      'name' => 'Januari 2026',
      'start_date' => '2026-01-01',
      'end_date' => '2026-01-31',
      'is_closed' => true,
      'closed_at' => now(),
  ]);
  ```
- **14. Prioritas Implementasi:** P1 (Tinggi)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Jalankan `vendor\bin\phpunit tests/Feature/AccountingAndTaxFeatureTest.php` dan pastikan tes nomor 2 lulus tanpa kegagalan integritas constraint.

---

#### AUDIT-HIGH-02
- **1. ID Temuan:** AUDIT-HIGH-02
- **2. Judul:** Pergeseran Parameter Pemanggilan dan Relasi Vendor Kosong pada `ShipmentWorkflowService::sendVendorEmailForDo` Mengakibatkan Pengiriman Email ke Alamat Salah
- **3. Modul / Fitur Terdampak:** DO & Pelayaran / Notifikasi Permohonan DO Riil (P09)
- **4. Severity:** HIGH
- **5. Kategori Masalah:** Business Logic / Email Transmission Failure
- **6. Langkah Reproduksi:**
  1. Panggil metode pengiriman email permohonan DO ke pelayaran melalui controller atau unit test:
     ```php
     $service->sendVendorEmailForDo($shipment, $shippingVendor->pic_email, 'Catatan permohonan');
     ```
  2. Amati data penerima email pada log surat (*Mail fake*).
- **7. Kondisi Aktual:**
  Email tidak terkirim ke alamat email vendor yang dimasukkan (`$shippingVendor->pic_email`), melainkan terkirim ke alamat default fallback `'import.documentation@maersk.com'` dengan teks catatan permohonan yang terisi nilai alamat email.
- **8. Expected Behavior:**
  Email permohonan DO harus dikirimkan tepat ke alamat loket DO pelayaran yang menangani shipment terkait (`$shipment->shippingVendor->pic_email` atau argumen email eksplisit) dengan lampiran berkas B/L.
- **9. Bukti Hasil Pemeriksaan:**
  - `app/Services/Shipment/ShipmentWorkflowService.php:218`:
    Deklarasi metode:
    ```php
    public function sendVendorEmailForDo(Shipment $shipment, ?string $customNotes = null)
    ```
    Metode hanya menerima dua parameter: `$shipment` dan `$customNotes`.
  - Di dalam metode:
    ```php
    $recipientEmail = $shipment->shippingVendor?->pic_email ?? 'import.documentation@maersk.com';
    ```
  - Pemanggilan di `tests/Feature/Logistics/DoReleaseAndDemurrageTest.php:89`:
    ```php
    $service->sendVendorEmailForDo($this->shipment, $this->shippingLine->pic_email, 'Mohon rilis DO urgent');
    ```
    Alamat email terkirim sebagai argumen kedua (`$customNotes`), sementara `$this->shipment->shipping_vendor_id` belum di-set, sehingga email selalu jatuh ke fallback Maersk.
- **10. Akar Masalah:**
  Inkonsistensi kontrak antarmuka metode antara implementasi service dengan pemanggilnya, serta tidak adanya validasi keharusan pengisian `shipping_vendor_id` sebelum pemanggilan transmisi DO.
- **11. Dampak Bisnis dan Teknis:**
  Jika staf DO mengajukan permohonan DO untuk pelayaran ONE, Evergreen, atau Samudera Indonesia, surat permohonan DO dan berkas rahasia B/L klien dapat terkirim secara keliru ke loket Maersk atau gagal terkirim ke loket pelayaran yang sebenarnya, mengakibatkan keterlambatan penerbitan DO dan timbulnya denda demurrage.
- **12. Data atau Modul Lain yang Terdampak:**
  `VendorDoRequestMail`, tabel `shipments`, tabel `shipping_vendors`.
- **13. Rekomendasi Perbaikan:**
  Ubah tanda tangan metode di `ShipmentWorkflowService.php`:
  ```php
  public function sendVendorEmailForDo(Shipment $shipment, ?string $customEmail = null, ?string $customNotes = null)
  ```
  Prioritaskan `$customEmail ?? $shipment->shippingVendor?->pic_email`.
- **14. Prioritas Implementasi:** P1 (Tinggi)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Jalankan `vendor\bin\phpunit tests/Feature/Logistics/DoReleaseAndDemurrageTest.php` dan pastikan tes nomor 2 lulus assersi `Mail::assertQueued(...)`.

---

#### AUDIT-HIGH-03
- **1. ID Temuan:** AUDIT-HIGH-03
- **2. Judul:** Inkonsistensi Penanganan Permintaan Tanpa Otentikasi pada Rute Media Streaming Privat (`PrivateMediaController`) Mengembalikan 403 Forbidden Bukan 401/302 Redirect
- **3. Modul / Fitur Terdampak:** Keamanan / HRM Privasi UU PDP / `PrivateMediaController`
- **4. Severity:** HIGH
- **5. Kategori Masalah:** Security / Specification Gap / HTTP Status Code Inconsistency
- **6. Langkah Reproduksi:**
  1. Akses URL foto absensi privat di peramban tanpa melakukan login:
     `GET /portal/attendances/photo/{attendanceLogId}`
  2. Periksa kode status respon HTTP yang diterima.
- **7. Kondisi Aktual:**
  Server mengembalikan `HTTP 403 Forbidden` langsung dari controller.
- **8. Expected Behavior:**
  Pengguna publik yang belum terotentikasi dan tidak memiliki signed token seharusnya diarahkan (`HTTP 302 Redirect`) ke halaman login `/login`, atau mengembalikan `HTTP 401 Unauthorized` pada konteks API/JSON.
- **9. Bukti Hasil Pemeriksaan:**
  - `routes/web.php:52`: Rute `portal/attendances/photo/{attendanceId}` dideklarasikan di luar grup middleware `auth`.
  - `app/Http/Controllers/PrivateMediaController.php:44`:
    ```php
    if (!$hasValidSignature && !$isAuthorizedUser) {
        abort(403, 'Akses media privat ditolak: Diperlukan URL bertanda tangan sah atau sesi berwenang.');
    }
    ```
  - `tests/Feature/Security/BiometricPrivacyE2ETest.php:87`:
    Pengujian mengharapkan penolakan unauthenticated dialihkan ke login (`assertRedirect(route('login'))`), sehingga tes gagal karena menerima 403.
- **10. Akar Masalah:**
  Penggunaan langsung `abort(403)` pada rute publik tanpa memeriksa apakah pengguna tamu (`auth()->guest()`) yang sebaiknya diarahkan login terlebih dahulu.
- **11. Dampak Bisnis dan Teknis:**
  Pengguna resmi yang mengklik tautan foto absensi saat sesinya telah kedaluwarsa akan disajikan halaman error 403 yang membingungkan alih-alih dialihkan ke formulir login untuk masuk kembali.
- **12. Data atau Modul Lain yang Terdampak:**
  `PrivateMediaController`, portal absensi staf, dan suite `BiometricPrivacyE2ETest`.
- **13. Rekomendasi Perbaikan:**
  Ubah penanganan di `PrivateMediaController.php`:
  ```php
  if (!$hasValidSignature && !auth()->check()) {
      return redirect()->guest(route('login'));
  }
  if (!$hasValidSignature && !$isAuthorizedUser) {
      abort(403, 'Akses media privat ditolak: Anda tidak memiliki wewenang untuk melihat berkas ini.');
  }
  ```
- **14. Prioritas Implementasi:** P1 (Tinggi)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Jalankan `vendor\bin\phpunit tests/Feature/Security/BiometricPrivacyE2ETest.php` dan pastikan seluruh 5 tes lulus 100%.

---

#### AUDIT-HIGH-04
- **1. ID Temuan:** AUDIT-HIGH-04
- **2. Judul:** Skrip Verifikasi Forensik `verify_remediation_success.ps1` Hanya Memeriksa Keberadaan Berkas Fisik Bukan Eksekusi Fungsional yang Sebenarnya
- **3. Modul / Fitur Terdampak:** Quality Assurance / DevSecOps Tooling
- **4. Severity:** HIGH
- **5. Kategori Masalah:** Quality Assurance / False Sense of Security / Verification Flaw
- **6. Langkah Reproduksi:**
  1. Jalankan skrip audit remediasi di terminal PowerShell:
     ```powershell
     powershell -ExecutionPolicy Bypass -File scripts/verify_remediation_success.ps1
     ```
  2. Perhatikan hasil cek ke-14 yang menyatakan `[PASS] 100% SUCCESS`.
- **7. Kondisi Aktual:**
  Skrip melaporkan bahwa seluruh 14 pengujian lulus 100% hijau. Namun, pemeriksaan nomor 14 hanya menjalankan `Test-Path "tests/Feature/AccountingAndTaxFeatureTest.php"`. Ketika berkas pengujian tersebut dieksekusi secara nyata dengan PHPUnit, tes tersebut langsung gagal akibat schema constraint error (AUDIT-HIGH-01).
- **8. Expected Behavior:**
  Skrip verifikasi kualitas harus menjalankan eksekusi pengujian secara fungsional (*functional run*) dan memeriksa exit code dari perintah pengujian, bukan sekadar memverifikasi keberadaan berkas di hard disk.
- **9. Bukti Hasil Pemeriksaan:**
  - `scripts/verify_remediation_success.ps1:78-83`:
    ```powershell
    # Check 14: Automated Test Exists
    if (Test-Path "tests/Feature/AccountingAndTaxFeatureTest.php") {
        Write-Host "Check 14: PASS (AccountingAndTaxFeatureTest exists)" -ForegroundColor Green
    }
    ```
- **10. Akar Masalah:**
  Penyusun skrip pengujian membuat shortcut verifikasi berbasis eksistensi path guna mengejar metrik kelulusan tanpa menjalankan tes secara penuh.
- **11. Dampak Bisnis dan Teknis:**
  Memberikan laporan palsu (*false green*) kepada manajemen dan auditor bahwa perbaikan akuntansi telah tervalidasi 100%, padahal di baliknya masih tersimpan kegagalan skema data.
- **12. Data atau Modul Lain yang Terdampak:**
  Pipeline CI/CD, laporan kepatuhan sistem, dan kepercayaan tata kelola perangkat lunak.
- **13. Rekomendasi Perbaikan:**
  Perbarui Check 14 pada `scripts/verify_remediation_success.ps1` agar mengeksekusi PHPUnit dengan runner SQLite terisolasi dan mengevaluasi `$LASTEXITCODE -eq 0`.
- **14. Prioritas Implementasi:** P1 (Tinggi)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Jalankan skrip yang telah diperbaiki dan buktikan bahwa status PASS hanya muncul jika PHPUnit benar-benar mengembalikan hasil 0 failure.

---

### 4.3 Temuan Tingkat Sedang (MEDIUM Severity)

---

#### AUDIT-MED-01
- **1. ID Temuan:** AUDIT-MED-01
- **2. Judul:** Keberadaan Controller Warisan Tanpa Rute Aktif (`DoController.php` & `TrafficController.php`) yang Mengakses Model Usang `CeisaDocument`
- **3. Modul / Fitur Terdampak:** Arsitektur Perangkat Lunak / Pemeliharaan Kode / Dead Code
- **4. Severity:** MEDIUM
- **5. Kategori Masalah:** Architecture / Code Cleanliness / Technical Debt
- **6. Langkah Reproduksi:**
  1. Periksa berkas `app/Http/Controllers/DoController.php` dan `app/Http/Controllers/TrafficController.php`.
  2. Periksa pemetaan rute aktif dengan `php artisan route:list | grep -E "DoController|TrafficController"`.
- **7. Kondisi Aktual:**
  Kedua controller tersebut sama sekali tidak terhubung ke rute mana pun di `routes/web.php`. Rute `/do` dan `/traffic` di `routes/web.php:106-112` telah dialihkan menggunakan redirect closure ke `core.do.index` (`DoWorkflowController`) dan `core.traffic.index` (`TrafficWorkflowController`). Kode di kedua controller tersebut mengakses tabel lama `ceisa_documents` secara langsung dan tidak mendukung siklus hidup `Shipment` v2.0.
- **8. Expected Behavior:**
  Seluruh kode sumber yang telah usang dan tidak memiliki rute aktif harus dihapus atau diarsipkan secara rapi agar tidak membingungkan pemelihara sistem di masa depan.
- **9. Bukti Hasil Pemeriksaan:**
  - `app/Http/Controllers/DoController.php`: 32 baris kode mati.
  - `app/Http/Controllers/TrafficController.php`: 32 baris kode mati.
  - `routes/web.php:106-112`:
    ```php
    Route::get('/do', fn() => redirect()->route('core.do.index'))->name('do.index');
    Route::get('/traffic', fn() => redirect()->route('core.traffic.index'))->name('traffic.index');
    ```
- **10. Akar Masalah:**
  Saat refactoring v1.0 ke alur kerja berbasis event `ShipmentWorkflowService` v2.0 dilakukan, pengembang membuat controller baru (`DoWorkflowController`, `TrafficWorkflowController`) tetapi membiarkan controller lama tertinggal di repositori.
- **11. Dampak Bisnis dan Teknis:**
  Meningkatkan beban kognitif developer baru yang membaca repositori; risiko developer lain salah mengimpor atau memanggil controller lama saat membuat fitur turunan.
- **12. Data atau Modul Lain yang Terdampak:**
  Struktur controller aplikasi.
- **13. Rekomendasi Perbaikan:**
  Hapus kedua berkas controller tersebut (`DoController.php` dan `TrafficController.php`) dari direktori `app/Http/Controllers/`.
- **14. Prioritas Implementasi:** P2 (Sedang)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Pastikan `php artisan route:list` tetap berjalan normal dan rute redirect `/do` serta `/traffic` tetap mengarah ke `DoWorkflowController` dan `TrafficWorkflowController`.

---

#### AUDIT-MED-02
- **1. ID Temuan:** AUDIT-MED-02
- **2. Judul:** Direktori Sub-View Tunggal Berisi Berkas Shim 1-Baris (`resources/views/core/do/index.blade.php` & `traffic/index.blade.php`)
- **3. Modul / Fitur Terdampak:** UI Layer / Blade Templates Layout
- **4. Severity:** MEDIUM
- **5. Kategori Masalah:** UI Inconsistency / Architecture / Redundant Views
- **6. Langkah Reproduksi:**
  1. Buka berkas `resources/views/core/do/index.blade.php` dan `resources/views/core/traffic/index.blade.php`.
- **7. Kondisi Aktual:**
  Kedua berkas hanya berisi satu baris instruksi blade:
  `@include('core.do_index')` dan `@include('core.traffic_index')`.
  Sementara itu, controller aktif `DoWorkflowController` dan `TrafficWorkflowController` langsung merender view utama `core.do_index` dan `core.traffic_index` tanpa melalui berkas shim tersebut.
- **8. Expected Behavior:**
  Struktur direktori view harus rapi dan konsisten tanpa menyisakan berkas perantara 1-baris yang tidak pernah dirender oleh controller aktif.
- **9. Bukti Hasil Pemeriksaan:**
  - `resources/views/core/do/index.blade.php:1`: `@include('core.do_index')`.
  - `resources/views/core/traffic/index.blade.php:1`: `@include('core.traffic_index')`.
  - `DoWorkflowController.php:32`: `return view('core.do_index', ...);`.
  - `TrafficWorkflowController.php:35`: `return view('core.traffic_index', ...);`.
- **10. Akar Masalah:**
  Sisa-sisa shim view dari migrasi konvensi penamaan view lama.
- **11. Dampak Bisnis dan Teknis:**
  Redundansi berkas view dan ketidakkonsistenan penataan folder UI.
- **12. Data atau Modul Lain yang Terdampak:**
  Folder `resources/views/core/`.
- **13. Rekomendasi Perbaikan:**
  Hapus direktori `resources/views/core/do/` dan `resources/views/core/traffic/` beserta berkas shim di dalamnya.
- **14. Prioritas Implementasi:** P2 (Sedang)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Akses `/core/do` dan `/core/traffic` pada peramban web dan pastikan halaman terender sempurna.

---

#### AUDIT-MED-03
- **1. ID Temuan:** AUDIT-MED-03
- **2. Judul:** Terdapat 9 Metode Monolitik Warisan WhatsApp dan Media Sosial di `CrmController.php` yang Tidak Lagi Memiliki Rute
- **3. Modul / Fitur Terdampak:** Modul CRM / Arsitektur Controller
- **4. Severity:** MEDIUM
- **5. Kategori Masalah:** Architecture / Code Redundancy / Dead Code
- **6. Langkah Reproduksi:**
  1. Buka berkas `app/Http/Controllers/CrmController.php`.
  2. Periksa baris 118 hingga 370.
  3. Cocokkan nama-nama metode tersebut dengan pemetaan di `routes/web.php`.
- **7. Kondisi Aktual:**
  `CrmController.php` memuat 9 metode: `updateWaSettings`, `sendWaTest`, `storeBroadcast`, `sendChatMessage`, `storeTemplate`, `storeSocialPost`, `replySocialInteraction`, `connectSocialAccount`, dan `disconnectSocialAccount`. Seluruh rute terkait telah dipindahkan ke controller spesialis (`WhatsAppController`, `SocialMediaController`, `InboxController`, dan `SettingController`). Metode-metode ini di `CrmController` tidak memiliki rute dan tidak pernah dapat dijangkau.
- **8. Expected Behavior:**
  Controller yang bertanggung jawab atas data klien (`CrmController`) harus fokus pada manajemen prospek dan klien tanpa menyimpan duplikasi logika modul lain yang telah dipisahkan.
- **9. Bukti Hasil Pemeriksaan:**
  - `app/Http/Controllers/CrmController.php:118-370`: 250+ baris kode duplikat tak terjangkau.
  - `routes/web.php:217-238, 293-295`: Rute-rute terkait mengarah secara eksklusif ke controller mandiri.
- **10. Akar Masalah:**
  Modularisasi modul CRM menjadi sub-sistem khusus (WhatsApp, Inbox, Social Media) tidak diikuti dengan pembersihan metode lama pada file asalnya.
- **11. Dampak Bisnis dan Teknis:**
  Duplikasi kode logika bisnis yang berpotensi memicu perbedaan implementasi jika ada pengembang yang keliru memanggil metode di controller lama.
- **12. Data atau Modul Lain yang Terdampak:**
  `CrmController.php`.
- **13. Rekomendasi Perbaikan:**
  Pangkas ke-9 metode warisan tersebut dari `CrmController.php` dan biarkan controller tersebut fokus murni pada pengelolaan klien dan pipeline penjualan.
- **14. Prioritas Implementasi:** P2 (Sedang)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Jalankan `php artisan route:list` dan `vendor\bin\phpunit tests/Feature/CrmTest.php` untuk memastikan tidak ada fungsi CRM yang terganggu.

---

#### AUDIT-MED-04
- **1. ID Temuan:** AUDIT-MED-04
- **2. Judul:** Duplikasi Pembuatan Data Fallback Hardcoded `WhatsappSetting` yang Diulang Persis Sama pada Tiga Controller Berbeda
- **3. Modul / Fitur Terdampak:** Konfigurasi Sistem / WABA Gateway / Kebersihan Kode
- **4. Severity:** MEDIUM
- **5. Kategori Masalah:** Code Duplication / Configuration Smell / Hardcoded Values
- **6. Langkah Reproduksi:**
  1. Lakukan pencarian string `"104928374829103"` di seluruh controller aplikasi:
     ```powershell
     Select-String -Path app/Http/Controllers/*.php -Pattern "104928374829103"
     ```
- **7. Kondisi Aktual:**
  Blok kode pembuatan record `WhatsappSetting` tiruan (`waba_id: 104928374829103`, `phone: +62 812-9000-8888`, nama: `PT AGUNG TRANS LOGISTICS`) disalin secara verbatim pada tiga file controller:
  1. `app/Http/Controllers/CrmController.php:53-66`
  2. `app/Http/Controllers/WhatsAppController.php:35-48`
  3. `app/Http/Controllers/SettingController.php:35-47`
- **8. Expected Behavior:**
  Inisialisasi data pengaturan sistem harus dipusatkan melalui Seeder basis data (`SystemSettingSeeder` / `DatabaseSeeder`) atau ditangani oleh sebuah Service Provider / Setting Repository tunggal. Controller tidak boleh menyematkan blok pembuatan data statis di dalam metode `index()`.
- **9. Bukti Hasil Pemeriksaan:**
  Ketiga controller mengeksekusi `WhatsappSetting::first()` dan jika null langsung memanggil `WhatsappSetting::create([...])` dengan array nilai string yang identik.
- **10. Akar Masalah:**
  Pola penyalinan kode (*copy-paste programming*) untuk memastikan halaman pengaturan tidak error saat dijalankan pada instalasi baru tanpa seeding.
- **11. Dampak Bisnis dan Teknis:**
  Jika ada perubahan parameter default WABA, pengembang harus memperbarui tiga controller yang berbeda. Melanggar prinsip DRY (*Don't Repeat Yourself*).
- **12. Data atau Modul Lain yang Terdampak:**
  Tabel `whatsapp_settings`.
- **13. Rekomendasi Perbaikan:**
  Pindahkan inisialisasi default ke `SystemSettingSeeder` dan gunakan model accessor atau helper tunggal:
  `WhatsappSetting::current()` yang mengembalikan record atau mengarahkan pengguna ke konfigurasi awal jika kosong.
- **14. Prioritas Implementasi:** P2 (Sedang)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Pastikan pengujian controller WhatsApp dan Settings tetap berjalan tanpa adanya snippet duplikat di controller.

---

#### AUDIT-MED-05
- **1. ID Temuan:** AUDIT-MED-05
- **2. Judul:** Konfigurasi Default Mengandung Endpoint Terowongan Pihak Ketiga Hardcoded dan API Key Mock di `config/ai.php`
- **3. Modul / Fitur Terdampak:** AI Agent / Customs Intelligence / Konfigurasi Sistem
- **4. Severity:** MEDIUM
- **5. Kategori Masalah:** Security / Configuration Smell / Hardcoded Credentials
- **6. Langkah Reproduksi:**
  1. Buka berkas `config/ai.php`.
  2. Periksa baris 16 dan 17.
- **7. Kondisi Aktual:**
  Terdapat URL terowongan sementara (`https://r4g77gv.abc-tunnel.us/v1`) dan string kunci tiruan (`sk-59807b51bc76063d-ge0agt-97ac1590`) yang disematkan sebagai nilai fallback bawaan:
  ```php
  'endpoint' => env('OPENAI_CUSTOMS_ENDPOINT', 'https://r4g77gv.abc-tunnel.us/v1'),
  'api_key' => env('OPENAI_CUSTOMS_API_KEY', 'sk-59807b51bc76063d-ge0agt-97ac1590'),
  ```
- **8. Expected Behavior:**
  Konfigurasi aplikasi enterprise tidak boleh menyimpan URL tunnel sementara atau key tiruan di dalam berkas konfigurasi repositori. Nilai fallback harus bernilai `null` atau string kosong, dengan kewajiban konfigurasi melalui file `.env`.
- **9. Bukti Hasil Pemeriksaan:**
  `config/ai.php:16-17`.
- **10. Akar Masalah:**
  URL debugging dari sesi pengujian pengembang sebelumnya terbawa dan ter-commit ke dalam file konfigurasi framework.
- **11. Dampak Bisnis dan Teknis:**
  Jika variabel `.env` tidak terpasang di server staging/produksi, sistem akan berupaya menghubungi domain terowongan pihak ketiga yang berpotensi mati atau bahkan dieksploitasi oleh pihak luar jika domain tunnel kedaluwarsa.
- **12. Data atau Modul Lain yang Terdampak:**
  `OpenAiCustomsService.php`.
- **13. Rekomendasi Perbaikan:**
  Ubah nilai fallback menjadi string kosong atau null:
  ```php
  'endpoint' => env('OPENAI_CUSTOMS_ENDPOINT', 'https://api.openai.com/v1'),
  'api_key' => env('OPENAI_CUSTOMS_API_KEY', ''),
  ```
- **14. Prioritas Implementasi:** P2 (Sedang)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Jalankan `vendor\bin\phpunit tests/Unit/OpenAiCustomsServiceTest.php` dan pastikan tes tetap lulus menggunakan mock client.

---

### 4.4 Temuan Tingkat Rendah (LOW Severity)

---

#### AUDIT-LOW-01
- **1. ID Temuan:** AUDIT-LOW-01
- **2. Judul:** Fragmentasi Penamaan Direktori View Pengaturan Antara Singular `setting` dan Plural `settings` Serta Pemisahan `/config`
- **3. Modul / Fitur Terdampak:** UI Layout / Konvensi Penamaan Blade Templates
- **4. Severity:** LOW
- **5. Kategori Masalah:** UI Inconsistency / Directory Structure / Convention Violation
- **6. Langkah Reproduksi:**
  1. Periksa daftar folder pada `resources/views/`.
  2. Perhatikan folder `resources/views/setting/`, `resources/views/settings/`, dan `resources/views/config/`.
- **7. Kondisi Aktual:**
  - `resources/views/setting/index.blade.php`: Pengaturan umum sistem (`/setting`).
  - `resources/views/settings/roles.blade.php`: Manajemen peran RBAC (`/settings/roles`).
  - `resources/views/settings/integrations/mcp.blade.php`: Pengaturan MCP Server (`/settings/integrations/mcp`).
  - `resources/views/config/index.blade.php`: Konfigurasi CEISA 4.0 (`/config`).
  Terjadi pemecahan antara folder singular (`setting`) dan plural (`settings`) serta folder terpisah (`config`).
- **8. Expected Behavior:**
  Seluruh tampilan administratif pengaturan sebaiknya disatukan dalam satu hierarki direktori yang konsisten, misalnya `resources/views/settings/` dengan sub-folder `system/`, `roles/`, `mcp/`, dan `ceisa/`.
- **9. Bukti Hasil Pemeriksaan:**
  Keberadaan tiga direktori terpisah di `resources/views/`.
- **10. Akar Masalah:**
  Pengembangan fitur pengaturan yang dilakukan secara bertahap pada sprint yang berbeda tanpa standarisasi konvensi nama folder.
- **11. Dampak Bisnis dan Teknis:**
  Menyulitkan navigasi berkas bagi pengembang antarmuka front-end.
- **12. Data atau Modul Lain yang Terdampak:**
  Controller yang merender view tersebut (`SettingController`, `RoleController`, `McpIntegrationController`, `CeisaConfigController`).
- **13. Rekomendasi Perbaikan:**
  Satukan seluruh view pengaturan ke dalam satu folder induk `resources/views/settings/`.
- **14. Prioritas Implementasi:** P3 (Rendah)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Akses keempat rute pengaturan di peramban dan pastikan seluruh view terender dengan benar.

---

#### AUDIT-LOW-02
- **1. ID Temuan:** AUDIT-LOW-02
- **2. Judul:** Assersi String HTML Stepper dan Navigasi Usang pada Suite Pengujian Lama Akibat Pembaruan Tampilan Apple HIG Bento Grid
- **3. Modul / Fitur Terdampak:** Test Suite / Tracking Cockpit Views
- **4. Severity:** LOW
- **5. Kategori Masalah:** Test Suite Maintenance / Stale View Assertions
- **6. Langkah Reproduksi:**
  1. Jalankan pengujian `tests/Feature/CustomsTrackingIntegrationTest.php` dan `tests/Feature/ProjectTrackingTest.php`.
  2. Perhatikan kegagalan pada assersi `assertSee`.
- **7. Kondisi Aktual:**
  Pengujian gagal karena mencari fragmen string markup HTML versi 1.0 (misalnya tag class stepper lama), sedangkan tampilan v2.0 telah diperbarui menjadi komponen Apple HIG Bento Grid dengan struktur DOM baru.
- **8. Expected Behavior:**
  Pengujian antarmuka harus memverifikasi keberadaan data teks esensial (misalnya nomor kontainer, status pabean, atau nomor aju) bukan struktur tag CSS atau kelas styling yang rentan berubah saat redesign UI.
- **9. Bukti Hasil Pemeriksaan:**
  - `CustomsTrackingIntegrationTest.php:42`: Mencari elemen markup navigasi lama.
  - `ProjectTrackingTest.php:58`: Mencari struktur elemen stepper lama.
- **10. Akar Masalah:**
  Redesign tampilan ke Apple HIG Bento Grid tidak disertai pembaruan assersi pengujian yang bersifat *over-specified* terhadap markup HTML.
- **11. Dampak Bisnis dan Teknis:**
  Pengujian tampak gagal padahal halaman web di browser pengguna bekerja dan tampil jauh lebih baik dari sebelumnya.
- **12. Data atau Modul Lain yang Terdampak:**
  Suite pengujian tracking kargo.
- **13. Rekomendasi Perbaikan:**
  Ubah assersi pada test file agar menguji data semantik (misalnya `$response->assertSee($shipment->nomor_kontainer)`) alih-alih fragmen tag HTML.
- **14. Prioritas Implementasi:** P3 (Rendah)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Jalankan `vendor\bin\phpunit tests/Feature/ProjectTrackingTest.php` dan pastikan seluruh tes lulus.

---

#### AUDIT-LOW-03
- **1. ID Temuan:** AUDIT-LOW-03
- **2. Judul:** Ketiadaan Model Factories untuk Entitas Transaksional Inti Mengharuskan Pembuatan Objek Uji Manual yang Rentan Kesalahan
- **3. Modul / Fitur Terdampak:** Test Infrastructure / Developer Experience (DX)
- **4. Severity:** LOW
- **5. Kategori Masalah:** Test Infrastructure / Code Quality / Maintainability
- **6. Langkah Reproduksi:**
  1. Periksa isi direktori `database/factories/`.
- **7. Kondisi Aktual:**
  Hanya ada 1 file factory: `UserFactory.php`. Tidak ada factory untuk model `Client`, `ShippingVendor`, `Shipment`, `ClientInvoice`, `VendorBill`, `AccountingPeriod`, `Employee`, `Quotation`, maupun `Todo`. Setiap pengembang yang menulis tes harus menuliskan puluhan baris array manual menggunakan `create([...])`.
- **8. Expected Behavior:**
  Setiap model Eloquent utama memiliki factory bawaan yang menyediakan atribut default valid sesuai skema migrasi terkini.
- **9. Bukti Hasil Pemeriksaan:**
  Direktori `database/factories/` hanya memiliki 1 file untuk 81 model yang ada.
- **10. Akar Masalah:**
  Fokus percepatan delivery fitur operasional pada sprint-sprint sebelumnya mengabaikan pembuatan infrastruktur test factories.
- **11. Dampak Bisnis dan Teknis:**
  Tingginya risiko kesalahan pengisian kolom pada tes baru (seperti yang terjadi pada `AccountingAndTaxFeatureTest` di AUDIT-HIGH-01) dan redundansi kode penyiapan data di seluruh file pengujian.
- **12. Data atau Modul Lain yang Terdampak:**
  Seluruh suite pengujian otomatis di `tests/Feature/`.
- **13. Rekomendasi Perbaikan:**
  Bangun Laravel Model Factories untuk 10 model utama: `ClientFactory`, `ShipmentFactory`, `InvoiceFactory`, `VendorBillFactory`, `AccountingPeriodFactory`, `EmployeeFactory`, dan `TodoFactory`.
- **14. Prioritas Implementasi:** P3 (Rendah)
- **15. Metode Verifikasi Setelah Perbaikan:**
  Gunakan factory baru pada pengujian fitur dan pastikan pembuatan model uji dapat dipersingkat menjadi satu baris: `Shipment::factory()->create()`.

---

## 5. FITUR RUSAK ATAU MALFUNGSI (MALFUNCTIONING / BROKEN FEATURES)

Pada bab ini dirangkum secara mendalam fitur-fitur yang mengalami kegagalan eksekusi nyata (*runtime breakdown*) beserta lokasinya di kode sumber:

### 5.1 Mesin Peringatan Demurrage Otomatis (`CheckDemurrageAlertsCommand`)
- **Status:** **RUSAK / RUNTIME CRASH**
- **Lokasi Kode:** `app/Console/Commands/CheckDemurrageAlertsCommand.php:70,76`
- **Deskripsi Kerusakan:**
  Perintah konsol harian yang bertugas memindai kontainer mendekati jatuh tempo masa bebas (*free time*) mengalami crash fatal seketika karena mencoba memasukkan kolom `user_id` yang tidak ada pada tabel `todos` dan nilai status `'PENDING'` yang melanggar check constraint basis data (`SQLSTATE[23000]`).
- **Dampak Nyata:** Peringatan H-3, H-2, dan H-1 tidak pernah terbit; risiko denda penumpukan kontainer USD 50–150/hari per boks tidak termitigasi secara otomatis.

### 5.2 Alur Impor Asinkron Dokumen F15 (Rute Hilang & CPU Spike PDF Parser)
- **Status:** **RUSAK PADA DUA TITIK ALUR KERJA**
- **Lokasi Kode:**
  1. `routes/web.php:300-315` (Rute `core.import.doc.apply` tidak terdaftar).
  2. `app/Services/Shipment/DocumentExtractionService.php:84` (Regex backtracking stream freeze).
- **Deskripsi Kerusakan:**
  - Pengguna tidak dapat menyelesaikan proses batch import karena tombol konfirmasi akhir memicu `RouteNotFoundException` (HTTP 500).
  - Jika berkas PDF yang diunggah tidak memiliki struktur cross-reference standar, fungsi ekstraksi membekukan server web dengan konsumsi 100% CPU selama lebih dari 6 menit.

### 5.3 Suite Pengujian Otomatis Fitur Akuntansi & Pajak (`AccountingAndTaxFeatureTest`)
- **Status:** **GAGAL EKSEKUSI DI TINGKAT FITUR PENGUJIAN**
- **Lokasi Kode:** `tests/Feature/AccountingAndTaxFeatureTest.php:48-55`
- **Deskripsi Kerusakan:**
  Pengujian penguncian periode buku akuntansi gagal dijalankan karena memasukkan kolom buatan (`year`, `month`, `status`, `notes`) ke dalam model `AccountingPeriod` yang tidak memiliki kolom tersebut dan mengabaikan kolom wajib `name`, `start_date`, `end_date`.

### 5.4 Transmisi Email Vendor DO Otomatis (Pergeseran Argumen & Vendor Unset)
- **Status:** **MISDIRECTION / LOGICAL FAILURE**
- **Lokasi Kode:** `app/Services/Shipment/ShipmentWorkflowService.php:218`
- **Deskripsi Kerusakan:**
  Fungsi transmisi email permohonan DO tidak menerima argumen alamat email tujuan pada posisinya dan jatuh ke fallback alamat email Maersk default, berpotensi mengirimkan dokumen ke perusahaan pelayaran yang keliru.

### 5.5 Penanganan Akses Media Privat Tanpa Otentikasi (`PrivateMediaController`)
- **Status:** **PENANGANAN STATUS HTTP KELIRU**
- **Lokasi Kode:** `app/Http/Controllers/PrivateMediaController.php:44`
- **Deskripsi Kerusakan:**
  Akses tamu tanpa otentikasi langsung dijatuhkan ke error 403 Forbidden tanpa memberikan kesempatan login (302 Redirect).

---

## 6. KETIDAKKONSISTENAN SISTEM (INCONSISTENCIES ANALYSIS)

### 6.1 Ketidakkonsistenan Skema & Integritas Data (Data & Schema Inconsistencies)
1. **Model `AccountingPeriod` vs Test Harness:**
   Migrasi Sprint 1 mendefinisikan periode berbasis rentang tanggal (`start_date`, `end_date`, `name`), sedangkan test suite lama mengasumsikan kolom angka terpisah (`year`, `month`).
2. **Model `Todo` Status Enum:**
   Migrasi Sprint 4 menetapkan status sah: `TODO`, `IN_PROGRESS`, `WAITING_EXTERNAL`, `DONE`, `CANCELLED`. Namun kode command demurrage masih menggunakan string konvensional `PENDING`.
3. **Format Nomor Aju Pabean:**
   Kolom basis data `ceisa_documents.nomor_aju` memiliki panjang `VARCHAR(26)`. Beberapa skenario pengujian lama membuat string acak 29 karakter yang menyebabkan `SQLSTATE 22001 Data too long for column`.

### 6.2 Ketidakkonsistenan Informasi & Status Pelacakan (Information Inconsistencies)
1. **Pemetaan Status `shipments.status` vs Cockpit Stepper:**
   Status tahap shipment `DOC_COMPLETED` di beberapa view tracking diidentifikasi sebagai `BILLING_ISSUED`. Keduanya merujuk pada tahap yang sama (Pos 7 pabean) namun ditampilkan dengan label teks berbeda antara halaman detail pabean dan halaman ringkasan proyek.

### 6.3 Ketidakkonsistenan Antarmuka Pengguna & Struktur Direktori View (UI Inconsistencies)
1. **Pemisahan Folder View Pengaturan:**
   Pengaturan sistem terpecah antara `resources/views/setting/`, `resources/views/settings/`, dan `resources/views/config/`.
2. **Berkas Perantara Shim View:**
   Keberadaan berkas shim satu baris di `resources/views/core/do/index.blade.php` dan `resources/views/core/traffic/index.blade.php` yang tidak mencerminkan hirarki render controller aktif.

### 6.4 Ketidakkonsistenan Logika Bisnis & Penanganan Respon HTTP (Business Logic Inconsistencies)
1. Penolakan akses foto privat absensi tamu yang langsung mengembalikan HTTP 403 alih-alih mengarahkan ke halaman otentikasi resmi.

---

## 7. TUMPANG TINDIH FITUR & REKOMENDASI KONSOLIDASI (OVERLAPPING FEATURES & CONSOLIDATION)

### 7.1 Duplikasi Pengendali Warisan: `DoController` & `TrafficController` vs Workflow Modern
- **Kondisi:**
  Terdapat dua pasang pengendali yang menangani domain yang sama:
  - DO: `DoController.php` (Warisan v1.0, kueri `CeisaDocument`) vs `DoWorkflowController.php` (Modern v2.0, kueri `Shipment`).
  - Traffic: `TrafficController.php` (Warisan v1.0, kueri `CeisaDocument`) vs `TrafficWorkflowController.php` (Modern v2.0, kueri `Shipment`).
- **Rekomendasi Konsolidasi:**
  Hapus secara permanen `DoController.php` dan `TrafficController.php`. Semua interaksi diarahkan penuh ke `DoWorkflowController` dan `TrafficWorkflowController`.

### 7.2 Metode Monolitik Warisan WhatsApp & Media Sosial di `CrmController`
- **Kondisi:**
  `CrmController.php` masih mempertahankan 9 metode lama pengiriman WhatsApp, siaran broadcast, dan penerbitan postingan sosial media yang sudah memiliki controller khususnya masing-masing (`WhatsAppController`, `SocialMediaController`, `InboxController`).
- **Rekomendasi Konsolidasi:**
  Pangkas ke-9 metode tersebut dari `CrmController.php`. `CrmController` hanya melayani:
  - Indeks CRM (`index`)
  - Manajemen Klien & Legalitas (`storeClient`, `updateClient`, `toggleClientStatus`, `uploadClientLegal`)
  - Pipeline Penjualan & Deals.

### 7.3 Duplikasi Inisialisasi Data Fallback Hardcoded `WhatsappSetting` di Tiga Controller
- **Kondisi:**
  Blok kode `WhatsappSetting::create([...])` dengan nilai hardcoded WABA diulang pada `CrmController`, `WhatsAppController`, dan `SettingController`.
- **Rekomendasi Konsolidasi:**
  Pindahkan pembuatan data default ini ke `SystemSettingSeeder.php` dan `CrmOmnichannelSeeder.php`. Di level controller, gunakan pemanggilan tunggal:
  ```php
  $waSetting = WhatsappSetting::first() ?? new WhatsappSetting();
  ```

---

## 8. ANALISIS AKAR MASALAH SISTEMIK (ROOT CAUSE ANALYSIS)

Melalui investigasi mendalam terhadap riwayat komit, struktur arsitektur, dan pola kegagalan, ditemukan 5 akar masalah struktural:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        DIAGRAM POHON AKAR MASALAH SISTEMIK                             │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 1. Evolusi Cepat Prototipe v1.0 Menuju Domain-Driven Enterprise v2.0                   │
│    └── Controller baru dibangun tanpa menghapus controller lama (menimbulkan dead code)│
│                                                                                        │
│ 2. Konfigurasi Lingkungan Pengujian yang Mengabaikan Isolasi Basis Data                │
│    └── `phpunit.xml` default mewarisi DB dev live; trait `RefreshDatabase` menghapus   │
│        tabel tanpa peringatan saat tes dijalankan.                                     │
│                                                                                        │
│ 3. Schema Drift Antara Migrasi Sprint dan Skrip Pengujian Otomatis                     │
│    └── Kolom tabel disempurnakan di migrasi, tetapi file pengujian tidak dimutakhirkan │
│        sehingga memicu kegagalan constraint kolom.                                     │
│                                                                                        │
│ 4. Ketiadaan Sanitasi Awal Struktur PDF Sebelum Pemrosesan Regex Pihak Ketiga          │
│    └── Ekstraksi fallback memproses string biner acak dengan regex unanchored,         │
│        mengakibatkan catastrophic backtracking PCRE pada berkas non-standar.           │
│                                                                                        │
│ 5. Defisit Infrastruktur Model Factories di Lingkungan Pengujian                       │
│    └── Ketiadaan factory memaksa pengujian menulis array model manual, memicu kerapuhan│
│        dan kesalahan penulisan nama kolom di berbagai suite pengujian.                 │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 9. DAMPAK LINTAS MODUL & ANALISIS RISIKO SISTEM (CROSS-MODULE IMPACT & SYSTEM RISK ANALYSIS)

### 9.1 Matriks Evaluasi Risiko Enterprise (Risk Heatmap 5x5 ISO 31000:2018)

Berdasarkan framework manajemen risiko ISO 31000 yang dipetakan pada `docs/RISK_MANAGEMENT_GENIE_ERP.md`:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                     HEATMAP RISIKO ENTERPRISE GENIE ERP (ISO 31000)                    │
├───────┬────────────────────────────────────────────────────────────────────────────────┤
│ DAMPAK│ 1 (Insignificant)  2 (Minor)      3 (Moderate)    4 (Major)       5 (Catastrophic)│
├───────┼────────────────────────────────────────────────────────────────────────────────┤
│ 5     │                                   R06 (Lompat)    R03 (DoS Import) R04 (Escrow)│
│ 4     │                                   R07 (Demurrage) R01 (Privasi)   R02 (Pabean) │
│ 3     │                                                   R08 (Lock Period)            │
│ 2     │                    R10 (Portal)   R05 (Fraud Petty)                            │
│ 1     │ R09 (SPOF Staf)                                                                │
└───────┴────────────────────────────────────────────────────────────────────────────────┘
```
- **R01 (Skor 16 - EXTREME):** Kebocoran Foto Biometrik Absensi (UU PDP No. 27/2022) — *Sudah Termitigasi*.
- **R02 (Skor 20 - EXTREME):** Sanksi Denda Notul Pabean (SPTNP) — *Sudah Termitigasi via Flat Root & 10 Cross-Checks*.
- **R03 (Skor 20 - EXTREME):** Crash Server Memori & 504 Timeout Batch Import F15 — *Rentan Terbuka via AUDIT-CRIT-02*.
- **R04 (Skor 20 - EXTREME):** Gagal Bayar Dana Talangan Pabean Miliaran Rupiah — *Sudah Termitigasi via Pre-Funding Escrow*.
- **R07 (Skor 12 - HIGH):** Denda Demurrage Akibat Peringatan Lumpuh — *Rentan Terbuka via AUDIT-CRIT-03*.

---

### 9.2 Propagasi Kegagalan Lintas Alur Kerja: Operasional $\rightarrow$ Keuangan $\rightarrow$ Akuntansi
Keterikatan antar modul dalam GENIE ERP sangat erat. Jika terjadi kegagalan pada satu modul, dampaknya merambat secara berantai:

```
[Malfungsi CheckDemurrageAlertsCommand (AUDIT-CRIT-03)]
                    │
                    ▼
[Free time kontainer habis tanpa peringatan]
                    │
                    ▼
[Shipping Line menerbitkan Vendor Bill denda Demurrage bernilai puluhan juta]
                    │
                    ▼
[Vendor Bill > 10jt tertahan di antrean persetujuan Direktur Utama (BR-01)]
                    │
                    ▼
[DO Release terhambat ──► Truk tidak bisa Gate Out ──► Delivery POD macet]
                    │
                    ▼
[Invoice Klien P19 tidak dapat terbit ──► Arus Kas Forwarder Terganggu]
```

---

### 9.3 Analisis Risiko Finansial, Regulasi, dan Hukum
1. **Risiko Denda Demurrage (Operasional/Finansial):** Dengan lumpuhnya cron peringatan demurrage, setiap peti kemas yang tertahan di depo atau dermaga dikenakan tarif detention/demurrage rata-rata USD 80 per hari. Untuk 20 boks kontainer impor, keterlambatan 5 hari menimbulkan kerugian sebesar USD 8.000 (~Rp 128.000.000).
2. **Risiko Penolakan Akses Pabean CEISA 4.0 (Regulasi):** Kesalahan format dokumen atau skema payload pabean dapat memicu pemblokiran akses NIB PPJK perusahaan oleh Ditjen Bea Cukai.
3. **Risiko Hukum UU PDP No. 27/2022:** Pelanggaran kebocoran data biometrik diancam pidana penjara hingga 5 tahun dan/atau denda administratif hingga Rp 50 Miliar (Pasal 67 UU PDP). Perlindungan foto absensi di disk privat merupakan benteng hukum mutlak.

---

## 10. PETA JALAN REMEDIASI TERPRIORITISASI (REMEDIATION ROADMAP)

Rencana perbaikan terstruktur disusun dalam 4 fase eksekusi:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        PETA JALAN REMEDIASI BERTAHAP (ROADMAP)                         │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [FASE 1: HARI 1–2] HOTFIX KRITIS & PENGAMANAN BASIS DATA                               │
│ ├── 1. Perbaiki `phpunit.xml` agar terisolasi ke database pengujian khusus.             │
│ ├── 2. Perbaiki kolom `assigned_to_user_id` & status `TODO` pada Demurrage Command.    │
│ ├── 3. Daftarkan rute hilang `core.import.doc.apply` di `routes/web.php`.              │
│ └── 4. Tambahkan validasi header PDF & batas panjang regex pada `DocumentExtraction`.   │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [FASE 2: HARI 3–5] KONSOLIDASI ARSITEKTUR & PEMBERSIHAN KODE                           │
│ ├── 1. Hapus controller usang `DoController.php` dan `TrafficController.php`.          │
│ ├── 2. Hapus direktori dan berkas shim `resources/views/core/do/` dan `traffic/`.      │
│ ├── 3. Pangkas 9 metode monolitik warisan dari `CrmController.php`.                    │
│ ├── 4. Hapus duplikasi hardcoded `WhatsappSetting` dari 3 controller; satukan seeder.  │
│ └── 5. Bersihkan nilai endpoint terowongan dan key tiruan di `config/ai.php`.          │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [FASE 3: MINGGU 2] MODERNISASI TEST SUITE & SINKRONISASI SKEMA                         │
│ ├── 1. Perbaiki pemanggilan kolom `AccountingPeriod` pada `AccountingAndTaxFeatureTest`│
│ ├── 2. Sesuaikan tanda tangan metode `sendVendorEmailForDo` di `ShipmentWorkflowService│
│ ├── 3. Ubah penanganan tamu unauthenticated di `PrivateMediaController` ke 302 Redirect│
│ ├── 4. Mutakhirkan skrip `verify_remediation_success.ps1` agar mengeksekusi test nyata │
│ ├── 5. Perbarui assersi teks semantik pada pengujian tracking kargo.                   │
│ └── 6. Buat Laravel Model Factories untuk 10 model utama di `database/factories/`.     │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [FASE 4: MINGGU 3–4] PENGURASAN SISTEM & KESIAPAN INTEGRASI LIVE                       │
│ ├── 1. Satukan hierarki folder view ke dalam `resources/views/settings/`.              │
│ ├── 2. Konfigurasi sertifikat SSL produksi & IP whitelisting Open API CEISA 4.0 DJBC.  │
│ └── 3. Uji beban (Load Testing) antrean batch import dan webhook Meta WABA live.       │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

### 10.5 Matriks Kriteria Penerimaan (Acceptance Criteria) & Definition of Done (DoD)

Setiap tahapan remediasi dinyatakan selesai jika memenuhi kriteria berikut:

| No | Target Remediasi | Kriteria Penerimaan (Definition of Done) | Metode Verifikasi |
|:---:|:---|:---|:---|
| 1 | **Pengamanan Lingkungan Test** | Eksekusi `phpunit` tidak pernah menghapus atau memodifikasi tabel di basis data `PATS_CEISA`. | `phpunit` dijalankan, hitung baris tabel `users` tetap 12. |
| 2 | **Command Demurrage** | `php artisan shipment:check-demurrage` selesai dengan exit code 0 dan membuat record di `todos` berstatus `TODO`. | Query `SELECT * FROM todos WHERE category='DO_OPERATIONS'`. |
| 3 | **Rute Batch Import** | Permintaan POST ke rute `core.import.doc.apply` berhasil mengembalikan HTTP 200/302 tanpa error not found. | `BulkDraftAndDeleteAssetsTest` lulus 100%. |
| 4 | **Keamanan Regex PDF** | Berkas PDF tanpa xref ditolak secara aman dalam waktu < 1 detik tanpa memicu CPU freeze. | `TimDocumentWorkflowTest` selesai < 10 detik. |
| 5 | **Test Suite Akuntansi** | Seluruh 5 pengujian di `AccountingAndTaxFeatureTest` lulus 100% hijau. | `vendor\bin\phpunit tests/Feature/AccountingAndTaxFeatureTest.php`. |
| 6 | **Pembersihan Dead Code** | Tidak ada lagi berkas `DoController.php` dan `TrafficController.php` di repositori. | `Test-Path app/Http/Controllers/DoController.php` bernilai `False`. |
| 7 | **Pengalihan Media Privat** | Akses tanpa otentikasi ke media privat mengarahkan peramban ke halaman login (HTTP 302). | `BiometricPrivacyE2ETest` lulus 100%. |

---

## 11. KESIMPULAN AKHIR AUDIT INDEPENDEN

Sistem ERP **PATS_CEISA (GENIE ERP v2.0)** adalah platform enterprise logistik yang dirancang dengan arsitektur modern, tangguh, dan matang. Fondasi kepatuhan hukum terhadap **UU Pelindungan Data Pribadi (UU PDP No. 27/2022)**, aturan perpajakan **TER PMK 168/2023**, pemisahan PPN 1.1% **PMK 71/2022**, proteksi transaksi via **Pre-Funding Escrow Gate**, serta penguncian pembukuan di level pemicu basis data (**Lock Period Observers**) telah terpasang secara autentik dan bekerja dengan luar biasa.

Ditemukannya 4 kerentanan kritis pada audit ini (konfigurasi basis data pengujian yang tidak terisolasi, regex DoS pada ekstraksi PDF, bug atribut pada command demurrage, dan rute impor yang belum terdaftar) bukan merupakan cacat arsitektur mendasar, melainkan *implementation glitches* yang dapat diselesaikan secara tuntas dalam kurun waktu 1 hingga 2 hari kerja mengikuti **Peta Jalan Remediasi** di atas.

Dengan mengimplementasikan 16 poin perbaikan yang telah didokumentasikan secara rinci, sistem GENIE ERP v2.0 akan mencapai standar keandalan perangkat lunak kelas dunia (*Tier-1 Enterprise Grade*) yang siap melayani operasional logistik nasional dan internasional dengan integritas data dan keamanan maksimal.

---
*Laporan Audit ini disusun secara independen, objektif, dan faktual berdasarkan analisis forensik kode sumber aktual di c:\laragon\www\GENIE.*  
**Lead Audit Synthesizer & Enterprise System Auditor — 15 November 2026**
