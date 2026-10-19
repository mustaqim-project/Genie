# PATS_CEISA ERP — ATURAN KHUSUS DESAIN & STANDAR UI/UX
**PT Agung Trans Solusindo — Portal Kepabeanan CEISA 4.0 & Ekosistem Logistik**
*Standar Resmi Berdasarkan Integrasi Skill: /web-ios-pwa-redesign, /ui-ux-pro-max, dan /design-taste-frontend*

---

## 1. DESIGN READ & TIGA DIAL (CORE CONFIGURATION)

### A. One-Line Design Read
> **"Reading this as: High-Reliability Enterprise Customs & Logistics ERP (PATS_CEISA) for customs brokers (PPJK) & enterprise logistics staff, with an Apple macOS/iOS clean utility language, leaning toward Tailwind tokens + SF/Inter typography + single sky-blue accent + dense functional ergonomics."**

### B. Konfigurasi Tiga Dial
```yaml
DESIGN_VARIANCE: 3   # Enterprise, simetris, tata letak predictable & berstruktur tinggi
MOTION_INTENSITY: 3  # Halus, fungsional (150-250ms), tanpa animasi sinematik yang memperlambat operasi
VISUAL_DENSITY: 8    # Kepadatan data tinggi (cockpit logistik, B/L, kontainer, pabean, invoice)
```

---

## 2. ARSITEKTUR TIGA TINGKAT TOKEN (3-LAYER TOKEN SYSTEM)

Semua komponen visual WAJIB mengacu pada hierarki token di `public/css/design-tokens.css`:

1. **Layer 1: Primitive Tokens (Fondasi Murni)**
   - Neutral: `--primitive-slate-50` s/d `--primitive-slate-950`
   - Brand Primary: `--primitive-sky-50` s/d `--primitive-sky-900` (`#0284c7` sebagai basis)
   - Status: `--primitive-emerald-*` (Success/Online), `--primitive-amber-*` (Warning/Hold), `--primitive-rose-*` (Danger/Reject), `--primitive-indigo-*` (EDI/Sync)
   - Spacing: Skala 4px (`--space-0-5` (2px) s/d `--space-12` (48px))

2. **Layer 2: Semantic Tokens (Peran & Konteks)**
   - Surface: `--color-page-bg`, `--color-surface`, `--color-surface-glass` (`rgba(255,255,255,0.85)`)
   - Text: `--color-text-primary` (`slate-900`), `--color-text-secondary` (`slate-700`), `--color-text-muted` (`slate-500`)
   - Border: `--color-border-subtle` (`rgba(226,232,240,0.8)`), `--color-border-default` (`slate-200`)
   - State: `--color-primary`, `--color-success`, `--color-warning`, `--color-danger`

3. **Layer 3: Component Tokens (Spesifik Komponen)**
   - Sidebar: `--sidebar-bg`, `--sidebar-item-active-bg`, `--sidebar-item-radius`
   - Topbar: `--topbar-bg`, `--topbar-height: 4rem`
   - Card: `--card-radius: var(--radius-2xl)`, `--card-shadow: var(--shadow-sm)`
   - Tabbar: `--tabbar-bg`, `--tabbar-border`

---

## 3. ATURAN ANTI-SLOP & DISIPLIN VISUAL (ANTI-DEFAULT DISCIPLINE)

1. **Banned: Rainbow Accent pada Navigasi**
   - DILARANG memberikan warna hover/active warna-warni sembarangan pada ikon menu sidebar (misal: amber untuk DO, pink untuk HRM, purple untuk AI, rose untuk Gateway).
   - Seluruh ikon menu sidebar menggunakan warna netral (`text-slate-500`), hover seragam (`hover:text-slate-900 hover:bg-slate-200/50`), dan state aktif tunggal Sky Blue (`bg-sky-600 text-white` atau `text-sky-600 font-bold`).
   - Warna status fungsional (hijau/kuning/merah) HANYA diizinkan untuk pill badge real-time status dokumen (SPPB, Hold, Reject), bukan untuk menu statis.

2. **Banned: Fake Telemetry & Fake Timestamp**
   - DILARANG menampilkan timestamp statis hasil render PHP (misal `now()->format('H:i') WIB`) sebagai label live status online.
   - Status CEISA 4.0 harus mencerminkan hasil ping/heartbeat nyata atau label statis netral tanpa jam palsu yang menipu pengguna.

3. **Banned: Emojis Sebagai Ikon Struktural**
   - DILARANG menggunakan emoji (🎨, 🚀, ⚙️, ⚠️) pada menu navigasi, header modal, tombol, atau tabel.
   - Wajib menggunakan SVG murni (Heroicons / Phosphor) dengan `stroke-width="1.5"` atau `"2.0"` seragam.

4. **Brand Identity: Genie (Sesuai Permintaan Klien)**
   - Nama produk/aplikasi resmi sesuai permintaan Klien adalah **Genie** (atau **Genie ERP** — Powered by PATS / PT Agung Trans Solusindo).
   - Branding **Genie** wajib konsisten di seluruh layout:
     - Sidebar Header: **Genie**
     - Topbar Breadcrumb & Tab Title: **Genie** / `Genie — PATS_CEISA ERP`
     - PWA Title: **Genie**
     - Footer: **Genie** &bull; &copy; PT Agung Trans Solusindo

5. **Lock: Bentuk Sudut (Squircle Consistency)**
   - Tombol dan Item Navigasi: `rounded-xl` (12px).
   - Kartu Dashboard, Modal Dialog & Spotlight: `rounded-2xl` (16-20px).
   - Tag status kecil & kbd badge: `rounded-lg` (8px).

---

## 4. TIGA PRESENTASI PERANGKAT TERPADU (RESPONSIVE MATRIX)

### A. Mobile (< 768px — Apple iOS Native PWA Experience)
1. **Bottom Tab Bar (Dock):**
   - Maksimal 4–5 tab utama: `Dashboard`, `Dokumen`, `DO & Port`, `Finance`, `Lainnya`.
   - Tinggi tap target vertikal minimum **48px–52px**, ditambah `padding-bottom: max(0.5rem, env(safe-area-inset-bottom))`.
   - Menggunakan `active:scale-95` tactile spring feedback.
   - Tombol "Lainnya" WAJIB memunculkan **iOS Bottom Sheet Modal** (bukan membuka drawer desktop sidebar).
2. **Top Bar Ringkas (Compact Navigation):**
   - Dilengkapi tombol Back (`<`) pada halaman detail/sub-modul.
   - Tombol pencarian Spotlight ringkas yang dapat diakses dari mobile.
3. **Viewport & Safe Area:**
   - Wajib menggunakan `min-h-[100dvh]` (bukan `h-screen` yang melompat karena address bar Safari).
   - `<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">`.

### B. Tablet (768px – 1024px — iPadOS Adaptive Layout)
1. **Sidebar Split-View:**
   - Dukungan mode collapsed (`w-20`) otomatis atau floating drawer saat orientasi portrait.
   - Tooltip interaktif saat hover di mode collapsed, sehingga pengguna tablet tetap memahami fungsi ikon.
2. **Tata Letak Adaptif:**
   - Formulir input terbagi side-by-side (2 kolom proporsional).
   - Grid bento 2 kolom untuk KPI metrik pabean.
   - Tabel data pabean memanfaatkan scroll horizontal berbayang halus (*fade edge*).

### C. Desktop (≥ 1024px — macOS Toolbar & High-Density Workspace)
1. **Persistent macOS Sidebar (`w-72` atau `w-20`):**
   - Translucent `bg-white/90 backdrop-blur-2xl border-r border-slate-200/80`.
   - Navigasi dikelompokkan tegas: `Utama`, `Core Bisnis`, `Manajemen`, `Sistem & AI`.
2. **Spotlight Command Palette (`⌘K` / `Ctrl+K`):**
   - Modal pencarian cepat terintegrasi untuk No Aju, No Kontainer, B/L, Klien, dan Navigasi.
   - Navigasi keyboard nyata (`ArrowUp`, `ArrowDown`, `Enter`, `Escape`).
3. **High Data Density Ergonomics:**
   - Baris tabel compact dengan padding 8px-12px (`py-2.5 px-3.5`).
   - Angka dan kode menggunakan font monospace (`JetBrains Mono` / `SF Mono`).

---

## 5. ERGONOMI AKSESIBILITAS & WCAG AA (UI/UX PRO MAX)

1. **Ukuran Tap Target:**
   - Semua elemen interaktif (tombol, link, toggle) memiliki area sentuh minimum **44 x 44 pt** (Apple HIG) atau didukung padding tak kasat mata (`hit slop`).
2. **Rasio Kontras Warna:**
   - Teks biasa terhadap background wajib memenuhi rasio kontras minimum **4.5:1** (WCAG AA).
   - Teks besar (18px+ / bold) dan elemen non-teks penting (border tombol, ikon fungsional) minimum **3.0:1**.
   - Dilarang teks placeholder terlalu abu-abu terang (wajib minimal `slate-400`).
3. **Fokus Keyboard (Focus-Visible):**
   - Seluruh input dan tombol wajib memiliki outline fokus tegas: `focus-visible:ring-4 focus-visible:ring-sky-500/20 focus-visible:border-sky-600 focus:outline-none`.
4. **Semantik & Aksesibilitas Ikon:**
   - Ikon dekoratif yang mendampingi teks WAJIB memiliki atribut `aria-hidden="true"`.
   - Ikon kontrol mandiri (seperti tombol close `X` atau tombol lonceng notifikasi) WAJIB memiliki label aksesibel `aria-label="Tutup"` atau `aria-label="Lihat Notifikasi"`.

---

## 6. STANDAR TEKNOLOGI & ASSET STACK (LARAVEL + VITE + PWA)

1. **Eliminasi Tailwind Play CDN di Produksi:**
   - Hapus `<script src="https://cdn.tailwindcss.com"></script>` pada `app.blade.php`.
   - Gunakan bundler lokal terkompilasi: `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
   - Ini mencegah FOUC (Flash of Unstyled Content), menghemat 3MB download per user, dan memungkinkan aplikasi bekerja 100% offline via PWA Service Worker.
2. **PWA Standalone Checklist:**
   - Hubungkan file manifest resmi: `<link rel="manifest" href="{{ asset('manifest.json') }}">`.
   - Sertakan Apple touch icon: `<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">`.
   - Pasang PWA registration script yang mengelola pembaruan service worker secara elegan.
   - Sediakan `offline.html` untuk skenario jaringan pelabuhan/lapangan putus.
3. **Isolasi Fitur Debug di Lingkungan Produksi:**
   - Dropdown Quick Role Switcher (`/quick-login/{role}`) WAJIB dilindungi oleh `@if(app()->environment('local'))` agar tidak terekspos ke publik atau lingkungan produksi.
