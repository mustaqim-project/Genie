# PATS_CEISA ERP — Design System Architecture & Specifications

**Sistem Desain Terpadu Tiga Tingkat (Three-Layer Design Tokens)**  
*Standar Desain Resmi PT Agung Trans Solusindo — Portal Ekspor-Impor & Kepabeanan CEISA 4.0 DJBC*

---

## 1. Filosofi Desain & Arsitektur Token

Sistem Desain **PATS_CEISA** dirancang mengacu pada **Apple Human Interface Guidelines (HIG)** yang disesuaikan untuk sistem enterprise web berkecepatan tinggi:
- **Clean & Anti-Clutter:** Menghilangkan elemen visual berlebih, kontras berlebihan, dan layout yang tumpang tindih.
- **Glassmorphism & Depth:** Menggunakan blur halus (*backdrop-filter: blur*), bayangan lembut (*soft elevation*), dan radius sudut squircle (*rounded-xl/2xl*).
- **Single Source of Truth:** Seluruh styling diturunkan melalui arsitektur 3-Layer Tokens (`Primitive` → `Semantic` → `Component`).
- **PWA & Responsivitas:** Pengalaman konsisten di Desktop (macOS layout), Tablet (iPadOS split view), dan Mobile (iOS native bottom tab bar).

```
┌─────────────────────────────────────────────────────────────┐
│ LAYER 1: PRIMITIVE TOKENS (Nilai Dasar / Mentah)             │
│ Contoh: --primitive-sky-600: #0284c7; --space-4: 1rem;      │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ LAYER 2: SEMANTIC TOKENS (Tujuan & Peran Kontekstual)       │
│ Contoh: --color-primary: var(--primitive-sky-600);           │
│         --color-surface: #ffffff;                            │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ LAYER 3: COMPONENT TOKENS (Spesifikasi Komponen Khusus)     │
│ Contoh: --btn-primary-bg: var(--color-primary);             │
│         --sidebar-item-active-bg: var(--color-primary);     │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Token Dictionary (Daftar Token Lengkap)

### A. Layer 1: Primitive Tokens

#### 1. Warna Dasar (Palette)
| Grup Warna | Variabel CSS | Hex / Value | Penggunaan |
|:---|:---|:---|:---|
| **Slate (Neutral)** | `--primitive-slate-50` | `#f8fafc` | Latar belakang halaman default |
| | `--primitive-slate-100` | `#f1f5f9` | Latar badge/search container |
| | `--primitive-slate-200` | `#e2e8f0` | Border default, pembatas tabel |
| | `--primitive-slate-300` | `#cbd5e1` | Border hover & garis fokus |
| | `--primitive-slate-400` | `#94a3b8` | Ikon pasif & teks placeholder |
| | `--primitive-slate-500` | `#64748b` | Teks sekunder & label kecil |
| | `--primitive-slate-600` | `#475569` | Teks pendukung |
| | `--primitive-slate-700` | `#334155` | Teks isi & judul sedang |
| | `--primitive-slate-800` | `#1e293b` | Header tabel & judul sub-modul |
| | `--primitive-slate-900` | `#0f172a` | Judul utama & teks kontras tinggi |
| | `--primitive-slate-950` | `#020617` | Latar halaman mode gelap |
| **Sky (Brand Primary)** | `--primitive-sky-50` | `#f0f9ff` | Status aktif lembut & highlight baris |
| | `--primitive-sky-100` | `#e0f2fe` | Badge aktif & hover menu |
| | `--primitive-sky-500` | `#0ea5e9` | Indikator fokus & link |
| | `--primitive-sky-600` | `#0284c7` | Warna tombol utama (Primary CTA) |
| | `--primitive-sky-700` | `#0369a1` | Hover state tombol utama |
| | `--primitive-sky-800` | `#075985` | Teks link aktif pada kartu |
| **Emerald (Success)** | `--primitive-emerald-50` | `#ecfdf5` | Background badge sukses/online |
| | `--primitive-emerald-600` | `#059669` | Status Berhasil, CEISA Online, Disetujui |
| | `--primitive-emerald-700` | `#047857` | Teks badge status selesai/SPPB |
| **Amber (Warning)** | `--primitive-amber-50` | `#fffbeb` | Background badge warning/draft |
| | `--primitive-amber-500` | `#f59e0b` | Status Pending, Verifikasi, Hold |
| | `--primitive-amber-700` | `#b45309` | Teks badge warning |
| **Rose (Danger)** | `--primitive-rose-50` | `#fff1f2` | Background badge reject/gagal |
| | `--primitive-rose-600` | `#e11d48` | Status Reject, Error, Hapus (Destructive) |
| | `--primitive-rose-700` | `#be123c` | Teks pesan bahaya/validasi gagal |
| **Indigo (Info / EDI)** | `--primitive-indigo-50` | `#eef2ff` | Background badge EDI / Integrasi DJBC |
| | `--primitive-indigo-600` | `#4f46e5` | Ikon sinkronisasi CEISA & batch job |

#### 2. Skala Spasi (4px Grid Scale)
| Token | Nilai Rem | Piksel | Penggunaan Rekomendasi |
|:---|:---|:---|:---|
| `--space-0-5` | `0.125rem` | 2px | Gap antar badge micro, outline offset |
| `--space-1` | `0.25rem` | 4px | Padding icon kecil, jarak tombol mini |
| `--space-1-5` | `0.375rem` | 6px | Padding vertikal pill status |
| `--space-2` | `0.5rem` | 8px | Padding internal tombol compact, gap list |
| `--space-2-5` | `0.625rem` | 10px | Padding input form horizontal |
| `--space-3` | `0.75rem` | 12px | Padding vertikal card compact, gap item menu |
| `--space-4` | `1rem` | 16px | Padding kontainer umum, margin antar elemen |
| `--space-5` | `1.25rem` | 20px | Padding kartu dashboard |
| `--space-6` | `1.5rem` | 24px | Jarak antar section halaman |
| `--space-8` | `2rem` | 32px | Jarak section utama |
| `--space-10` | `2.5rem` | 40px | Padding hero section / header halaman |

#### 3. Tipografi (Apple SF Pro Font Stack)
- **Keluarga Font (Sans):** `-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", sans-serif`
- **Keluarga Font (Mono):** `"SF Mono", "JetBrains Mono", Menlo, Monaco, monospace` (khusus No Aju, No Kontainer, Nilai Pabean, Tanggal & Jam).
- **Skala Ukuran Font:**
  - `--font-size-2xs` (10px): Label pill micro, badge tab bar mobile.
  - `--font-size-xs` (12px): Teks deskripsi pembantu, timestamp, header kolom tabel.
  - `--font-size-sm` (13px): Teks menu sidebar, tombol sekunder, filter dropdown.
  - `--font-size-base` (14px): Teks standar paragraf, input form, isi data tabel.
  - `--font-size-md` (16px): Subheading, judul modal dialog, penekanan angka.
  - `--font-size-lg` (18px): Judul kartu statistik, judul tabel.
  - `--font-size-xl` (20px): Judul halaman modul (Page Header).
  - `--font-size-2xl` (24px): Angka metrik utama (Total Omzet, Total Kontainer).

#### 4. Sudut Lengkung (Apple Squircle Radii)
- `--radius-xs` (4px): Checkbox, badge status micro.
- `--radius-sm` (6px): Tag status kecil, shortcut kbd (`⌘K`).
- `--radius-md` (8px): Tombol sekunder compact, dropdown menu item.
- `--radius-lg` (12px): Input form standar, tombol primary, item sidebar.
- `--radius-xl` (16px): Kartu sub-modul, modal header, spotlight search box.
- `--radius-2xl` (20px): Kartu dashboard utama, container formulir terpadu.
- `--radius-full` (9999px): Avatar pengguna, pill status bulat, tombol floating.

#### 5. Bayangan & Kedalaman (Elevation & Shadow)
- `--shadow-hairline`: `0 1px 2px 0 rgba(0, 0, 0, 0.03)` (Border elevasi Apple).
- `--shadow-sm`: `0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05)` (Kartu default).
- `--shadow-md`: `0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05)` (Dropdown, popover).
- `--shadow-lg`: `0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04)` (Modal dialog).
- `--shadow-glass`: `0 8px 32px 0 rgba(0, 0, 0, 0.06)` (Floating sheet & mobile drawer).

---

### B. Layer 2: Semantic Tokens

| Variabel CSS | Pemetaan Default (Terang) | Pemetaan Mode Gelap (.dark) | Keterangan Penggunaan |
|:---|:---|:---|:---|
| `--color-page-bg` | `var(--primitive-slate-50)` | `var(--primitive-slate-950)` | Background keseluruhan canvas |
| `--color-surface` | `#ffffff` | `var(--primitive-slate-900)` | Background panel, card, tabel |
| `--color-surface-subtle`| `rgba(255, 255, 255, 0.8)` | `rgba(15, 23, 42, 0.8)` | Permukaan berbayang / translucent |
| `--color-surface-glass` | `rgba(255, 255, 255, 0.85)`| `rgba(15, 23, 42, 0.85)`| Header sticky & bottom bar blur |
| `--color-text-primary` | `var(--primitive-slate-900)` | `#ffffff` | Judul, angka kunci, penekanan |
| `--color-text-secondary` | `var(--primitive-slate-700)` | `var(--primitive-slate-300)` | Teks deskripsi & isi sel data |
| `--color-text-muted` | `var(--primitive-slate-500)` | `var(--primitive-slate-400)` | Label form pendukung & keterangan |
| `--color-border-subtle` | `rgba(226, 232, 240, 0.8)` | `rgba(51, 65, 85, 0.7)` | Garis batas antar komponen kartu |
| `--color-border-default`| `var(--primitive-slate-200)` | `var(--primitive-slate-800)` | Border input dan divider tabel |
| `--color-primary` | `var(--primitive-sky-600)` | `var(--primitive-sky-500)` | Aksi utama, link, indikator terpilih |
| `--color-success` | `var(--primitive-emerald-600)` | `var(--primitive-emerald-500)` | Indikator sukses, SPPB, lolos pabean |
| `--color-warning` | `var(--primitive-amber-500)` | `var(--primitive-amber-400)` | Menunggu verifikasi / billing pending|
| `--color-danger` | `var(--primitive-rose-600)` | `var(--primitive-rose-500)` | Penolakan pabean / error kritis |

---

### C. Layer 3: Component Tokens

#### 1. Sidebar Navigation (`--sidebar-*`)
- `--sidebar-bg`: `rgba(248, 250, 252, 0.9)` (Translucent macOS side sheet).
- `--sidebar-border`: `var(--color-border-subtle)`.
- `--sidebar-item-radius`: `var(--radius-xl)`.
- `--sidebar-item-active-bg`: `var(--color-primary)`.
- `--sidebar-item-active-fg`: `#ffffff`.
- `--sidebar-item-hover-bg`: `rgba(226, 232, 240, 0.5)`.

#### 2. Topbar Header (`--topbar-*`)
- `--topbar-bg`: `rgba(255, 255, 255, 0.82)`.
- `--topbar-border`: `var(--color-border-subtle)`.
- `--topbar-height`: `4rem` (64px).

#### 3. Cards & Bento Grid (`--card-*`)
- `--card-bg`: `var(--color-surface)`.
- `--card-border`: `var(--color-border-subtle)`.
- `--card-radius`: `var(--radius-2xl)`.
- `--card-shadow`: `var(--shadow-sm)`.

#### 4. Buttons (`--btn-*`)
- `--btn-radius`: `var(--radius-xl)`.
- `--btn-primary-bg`: `var(--color-primary)`.
- `--btn-primary-hover-bg`: `var(--color-primary-hover)`.
- `--btn-primary-fg`: `#ffffff`.
- `--btn-secondary-bg`: `#ffffff`.
- `--btn-secondary-border`: `var(--color-border-default)`.
- `--btn-secondary-fg`: `var(--color-text-secondary)`.

#### 5. Form Input Controls (`--input-*`)
- `--input-bg`: `#ffffff`.
- `--input-border`: `var(--color-border-default)`.
- `--input-radius`: `var(--radius-xl)`.
- `--input-focus-ring`: `rgba(2, 132, 199, 0.25)`.

#### 6. iOS Bottom Tab Bar (`--tabbar-*`)
- `--tabbar-bg`: `rgba(255, 255, 255, 0.88)` (Mobile Blur Dock).
- `--tabbar-border`: `var(--color-border-subtle)`.

---

## 3. Matriks Status Komponen (Component State Matrix)

### A. Tombol Utama (Primary Action Button)
| Properti | Default | Hover | Active (Pressed) | Disabled | Focus-Visible |
|:---|:---|:---|:---|:---|:---|
| **Background** | `var(--color-primary)` | `var(--color-primary-hover)` | `var(--primitive-sky-800)` | `var(--primitive-slate-200)` | `var(--color-primary)` |
| **Teks** | `#ffffff` | `#ffffff` | `#ffffff` | `var(--primitive-slate-400)` | `#ffffff` |
| **Shadow** | `var(--shadow-sm)` | `var(--shadow-md)` | `none` | `none` | `0 0 0 3px var(--input-focus-ring)` |
| **Transform** | `scale(1)` | `scale(1)` | `scale(0.98)` | `none` | `none` |

### B. Input Teks / Selektor (Form Controls)
| Properti | Default | Hover | Focused | Error / Invalid | Disabled |
|:---|:---|:---|:---|:---|:---|
| **Background** | `#ffffff` | `#ffffff` | `#ffffff` | `var(--primitive-rose-50)` | `var(--primitive-slate-100)` |
| **Border** | `var(--color-border-default)` | `var(--color-border-hover)` | `var(--color-primary)` | `var(--color-danger)` | `var(--primitive-slate-200)` |
| **Ring** | `none` | `none` | `3px var(--input-focus-ring)` | `3px rgba(225, 29, 72, 0.2)` | `none` |
| **Teks** | `var(--color-text-primary)` | `var(--color-text-primary)` | `var(--color-text-primary)` | `var(--color-danger-text)` | `var(--primitive-slate-400)` |

### C. Tabel Baris Data (Data Grid Row)
| Properti | Default | Hover | Selected | Striped (Ganjil) |
|:---|:---|:---|:---|:---|
| **Background** | `transparent` | `rgba(241, 245, 249, 0.6)` | `var(--primitive-sky-50)` | `rgba(248, 250, 252, 0.5)` |
| **Border Bottom**| `var(--color-border-subtle)` | `var(--color-border-subtle)` | `var(--primitive-sky-200)` | `var(--color-border-subtle)` |
| **Teks Utama** | `var(--color-text-primary)` | `var(--color-text-primary)` | `var(--color-primary-text)` | `var(--color-text-primary)` |

---

## 4. Pola Implementasi & Anti-Pattern

### ❌ Anti-Pattern (DILARANG KERAS)
```html
<!-- DILARANG: Menggunakan hardcoded random hex code atau inline style tanpa token -->
<button style="background: #2563EB; border-radius: 25px; padding: 13px 22px;">
    Kirim Data
</button>

<div class="bg-[#123456] text-[#abcdef] p-[17px] rounded-[13px]">
    Kotak Info
</div>
```

### ✅ Best Practice (WAJIB DIIKUTI)
```html
<!-- BENAR: Menggunakan Token CSS atau Kelas Semantik Tailwind yang terhubung ke Token -->
<button class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-98 text-white font-medium shadow-sm transition-all focus:ring-4 focus:ring-sky-500/20">
    Kirim Data
</button>

<!-- Atau menggunakan CSS Variable murni -->
<div class="card p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
    <h3 class="text-slate-900 font-bold text-base">Kotak Info</h3>
    <p class="text-slate-500 text-xs">Menggunakan token semantic</p>
</div>
```

---

## 5. Integrasi File Sistem Proyek

1. **Definisi CSS Variables:** [`public/css/design-tokens.css`](file:///c:/laragon/www/pats_ceisa/public/css/design-tokens.css)
2. **Definisi JSON Schema:** [`public/css/design-tokens.json`](file:///c:/laragon/www/pats_ceisa/public/css/design-tokens.json)
3. **Master Layout Blade:** [`resources/views/layouts/app.blade.php`](file:///c:/laragon/www/pats_ceisa/resources/views/layouts/app.blade.php)
4. **Navigasi macOS Sidebar:** [`resources/views/layouts/sidebar.blade.php`](file:///c:/laragon/www/pats_ceisa/resources/views/layouts/sidebar.blade.php)
5. **Apple Toolbar Topbar:** [`resources/views/layouts/topbar.blade.php`](file:///c:/laragon/www/pats_ceisa/resources/views/layouts/topbar.blade.php)
6. **Administrative Footer:** [`resources/views/layouts/footer.blade.php`](file:///c:/laragon/www/pats_ceisa/resources/views/layouts/footer.blade.php)
7. **Mobile iOS Bottom Dock:** [`resources/views/layouts/bottom-tab-bar.blade.php`](file:///c:/laragon/www/pats_ceisa/resources/views/layouts/bottom-tab-bar.blade.php)

---
*Dokumen ini merupakan referensi tunggal standar visual dan arsitektur token di lingkungan aplikasi PATS_CEISA ERP.*
