# PATS_CEISA — PT Agung Trans Solusindo
### Sistem ERP Kepabeanan & Logistik Standalone (Custom Architecture)

Sistem perangkat lunak **Enterprise Resource Planning (ERP)** vertikal yang dibangun secara *standalone* (independen, tanpa ketergantungan pada `cooca_core`) khusus untuk operasional **PT Agung Trans Solusindo** (Bapak Soehartono) berdasarkan **Surat Penawaran No. 001/QT/COOCA/X/2026** dan **Perjanjian Kerja Sama (PKS)**.

---

## 🔐 Sistem Otentikasi & RBAC (Role-Based Access Control) Lengkap

Sistem menerapkan arsitektur keamanan berlapis berstandar Enterprise:

```
┌────────────────────────────────────────────────────────────────────────┐
│                   ARSITEKTUR OTENTIKASI & RBAC LENGKAP                 │
├────────────────────┬────────────────────┬──────────────────────────────┤
│ 1. AUTENTIKASI     │ 2. CONTROLLER RBAC │ 3. DYNAMIC PERMISSION MATRIX │
│ • Login/Logout/Sesi│ • HasMiddleware    │ • 12 Peran Resmi             │
│ • Cek Akun Aktif   │ • Enkapsulasi Aksi │ • 20 Izin Modular            │
│ • Profil Mandiri   │ • Gate Auto-Bypass │ • Checklist Setting Dinamis  │
│ • Ganti Password   │ • 403 Page Friendly│ • Quick-Role Switcher Demo   │
└────────────────────┴────────────────────┴──────────────────────────────┘
```

### 1. Daftar 12 Peran Pengguna Resmi (PRD Lampiran 1)
| Kode Peran | Nama Peran | Departemen | Wewenang Utama | Akun Demo (Password: `password`) |
| :--- | :--- | :--- | :--- | :--- |
| **`R-ADM`** | Admin Sistem | IT Lead | Master data, manajemen akun & hak akses, konfigurasi integrasi | `admin@agungtrans.co.id` |
| **`R-OWN`** | Pimpinan (Owner) | Executive | Dashboard eksekutif, KPI, approval strategis | `soehartono@agungtrans.co.id` |
| **`R-DOC-SPV`** | Supervisor Dokumen | Customs | Verifikasi HS Code, approval pabean sebelum kirim CEISA 4.0 | `spv.doc@agungtrans.co.id` |
| **`R-DOC`** | Staff Dokumen | Customs | Drafting PIB BC 2.0 & PEB BC 3.0, ekstraksi PL/BL | `doc.staff@agungtrans.co.id` |
| **`R-DO`** | Staff Delivery Order | Shipping | Tracking kapal, monitoring free time, tagihan pelayaran | `do.staff@agungtrans.co.id` |
| **`R-TRF`** | Staff Traffic | Logistics | Penjaluran CEISA, hold/notul, gate out TPS, vendor armada | `traffic.staff@agungtrans.co.id` |
| **`R-FLD`** | Staff Lapangan | Port Ops | Pemeriksaan fisik (behandle) TPS pelabuhan via mobile web | `field.staff@agungtrans.co.id` |
| **`R-MGR`** | Operations Manager | Operations | Eskalasi SLA, approval operasional tim, KPI shipment | `manager.ops@agungtrans.co.id` |
| **`R-FIN`** | Finance | Finance | Pembayaran billing pabean MPN G3, invoice pelayaran, invoice klien | `finance@agungtrans.co.id` |
| **`R-HR`** | HRD | HR & GA | Pegawai, kontrak PKWT, absensi GPS & wajah, payroll | `hr@agungtrans.co.id` |
| **`R-CS`** | Customer Service | CRM | Inbox percakapan WhatsApp Omnichannel (WABA Meta) | `cs@agungtrans.co.id` |
| **`R-AM`** | Account Manager | Commercial | Komunikasi klien, monitoring shipment klien binaan | `am@agungtrans.co.id` |

---

### 2. Fitur-Fitur Keamanan & Autentikasi yang Diimplementasikan
1. **Pemeriksaan Status Akun Aktif (`is_active`):**
   - Pengguna yang dinonaktifkan oleh administrator secara otomatis ditolak saat login maupun quick-login dengan pesan error yang jelas.
2. **Controller-Level RBAC Encapsulation (`HasMiddleware`):**
   - Seluruh Controller (`BC20Controller`, `BC30Controller`, `DoController`, `TrafficController`, `HrmController`, `CrmController`, `FinanceController`, `AccountingController`, `AiAgentController`, `SettingController`, `CeisaConfigController`) menerapkan interface `Illuminate\Routing\Controllers\HasMiddleware` dengan proteksi permission granular per-method (`only: [...]` / `except: [...]`).
3. **Laravel Gate Auto-Resolution (`Gate::before`):**
   - SuperAdmin (`R-ADM` & `R-OWN`) secara otomatis memperoleh *full-bypass* tanpa batas.
   - Pengecekan standar Laravel `@can('permission.name')` dan `$this->authorize('permission.name')` langsung terhubung ke metode `hasPermission()` pengguna.
4. **Halaman Error 403 (Forbidden) Kustom & Ramah Pengguna:**
   - Halaman `resources/views/errors/403.blade.php` menampilkan identitas pengguna, peran aktif saat ini, daftar izin yang dimiliki, tombol kembali ke Dashboard, serta tombol pengujian peran alternatif.
5. **Manajemen Profil Mandiri & Ganti Password (`/profile`):**
   - Pengguna dapat melihat identitas akun, ringkasan izin yang dimiliki, memperbarui nomor telepon/jabatan, serta mengganti kata sandi dengan validasi kata sandi lama.
6. **Pusat Kendali Pengguna & Matriks Hak Akses di Setting (`/setting`):**
   - **Tab Pengguna:** Tambah user baru, ganti peran pengguna secara langsung, reset kata sandi, aktif/nonaktifkan akun, dan hapus user (dengan proteksi akun utama).
   - **Tab Matriks Hak Akses:** Checklist matriks interaktif 12 peran vs 20 izin modular yang dapat disesuaikan dan disimpan secara dinamis ke database `role_permissions`.
   - **Tab Gateway CEISA:** Parameter pabean, pengujian token OAuth, dan log audit transaksi.

---

### 3. Matriks 20 Permissions Bawaan Sistem
* **Dashboard:** `dashboard.view`, `dashboard.financial`
* **Core Bisnis - Dokumen:** `document.view`, `document.create`, `document.approve`, `ceisa.submit`
* **Core Bisnis - DO:** `do.view`, `do.manage`
* **Core Bisnis - Traffic:** `traffic.view`, `traffic.manage`, `traffic.behandle`
* **HRM:** `hrm.view`, `hrm.manage`
* **CRM:** `crm.view`, `crm.manage`
* **Finance:** `finance.view`, `finance.manage`
* **Accounting:** `accounting.view`, `accounting.manage`
* **AI Agent:** `ai.view`, `ai.approve`
* **Setting:** `setting.view`, `setting.manage`

---

## 🧪 Hasil Pengujian Otomatis (Feature Test Suite)

Telah dibuat automated test suite komprehensif di [`tests/Feature/RbacAuthenticationTest.php`](file:///c:/laragon/www/pats_ceisa/tests/Feature/RbacAuthenticationTest.php):

```bash
php artisan test
```

Hasil eksekusi:
```
Tests:    12 passed (42 assertions)
Duration: 2.35s
Status:   100% OK
```

Skenario yang divalidasi:
1. `test_guest_is_redirected_to_login`: Tamu otomatis dialihkan ke `/login`.
2. `test_user_can_login_with_valid_credentials`: Login sukses dengan data akun valid.
3. `test_inactive_user_cannot_login`: Akun nonaktif ditolak login.
4. `test_admin_has_full_access_to_dashboard_and_setting`: SuperAdmin bypass penuh ke semua modul.
5. `test_staff_do_restricted_from_accessing_setting_and_hrm`: Staff DO sukses membuka DO, namun menerima respons `403 Forbidden` saat mencoba mengakses Setting atau HRM.
6. `test_user_can_view_and_update_profile`: Pengguna dapat mengubah nama & nomor kontak pribadi.
7. `test_user_can_change_password`: Validasi kata sandi lama sebelum mengganti kata sandi baru.
8. `test_admin_can_create_user_and_toggle_status`: Admin dapat mendaftarkan staf baru dan mematikan status akun.
9. `test_admin_can_update_role_permissions_matrix`: Admin dapat menambahkan/mencabut hak akses peran secara dinamis.
10. `test_document_submission_requires_ceisa_submit_permission`: Staff `R-DOC` ditolak 403 saat submit dokumen ke CEISA, sementara Supervisor `R-DOC-SPV` berhasil submit.

---

## 📋 8 Pengelompokan Fitur pada Admin Panel

1. **Dashboard:** Bento Grid metrik operasional, radar penjaluran pabean (Jalur Hijau/Kuning/Merah/SPPB/NPE), devisa FOB/CIF.
2. **Core Bisnis:**
   - **Tim Document:** Ekstraksi PL/BL OCR/LLM, penyusunan PIB BC 2.0 & PEB BC 3.0, kalkulasi CIF, BM, PPN 11%, PPh 22, Devisa FOB, integrasi Host-to-Host CEISA 4.0 DJBC.
   - **Tim DO:** Tracing kapal pelayaran, monitoring free time demurrage/detention, penebusan DO pelayaran.
   - **Tim Traffic:** Monitoring kedatangan, gate out pelabuhan TPS, penanganan hold/notul (NPD, SPTNP), pemeriksaan behandle lapangan.
3. **HRM:** Karyawan, kontrak kerja PKWT/PKWTT, absensi radius GPS & verifikasi wajah, cuti auto-reassign PIC, slip gaji.
4. **CRM:** WhatsApp Business API (WABA Meta), sidebar info shipment aktif, pipeline leads calon klien.
5. **Finance:** Penagihan invoice klien (P19), pembayaran billing pabean MPN G3, AP/AR aging report, kas & bank.
6. **Accounting:** Chart of Accounts (COA) logistik, auto-journal engine, laporan laba rugi (P&L), neraca.
7. **AI Agent:** Server MCP mandiri, briefing harian risiko, antrian usulan aksi persetujuan manusia (*Human-in-the-Loop*).
8. **Setting:** Manajemen akun, matriks hak akses dinamis 12 peran, kredensial CEISA 4.0, audit trail append-only.

---

## 🚀 Cara Menjalankan Aplikasi di Laragon

1. Pastikan web server Apache/Nginx & PHP aktif di Laragon.
2. Akses melalui browser:
   * **`http://pats_ceisa.test`** atau **`http://localhost:8000`** via `php artisan serve`
3. Masuk menggunakan akun demo:
   * **Email:** `admin@agungtrans.co.id`
   * **Password:** `password`
   * Atau gunakan menu **Quick Switch Role** pada halaman login atau menu profil pojok kanan atas.
