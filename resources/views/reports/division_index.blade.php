@extends('layouts.app')

@section('title', 'Laporan Operasional per Divisi - GENIE ERP')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">
    <!-- Header & Breadcrumb -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7] transition-colors flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <span class="text-slate-800 dark:text-white font-semibold">Reporting per Divisi</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Pusat Laporan & Ekspor Data per Divisi</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Ekspor operasional harian, performa layanan, utilisasi armada, dan laporan keuangan langsung ke format Excel (.xlsx)</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('finance.pnl') }}" class="btn-apple-gray px-3.5 py-2 text-xs inline-flex items-center gap-1.5 text-[#0284c7]">
                <i data-lucide="coins" class="w-4 h-4"></i>
                <span>Laporan PnL per Job</span>
            </a>
        </div>
    </div>

    <!-- Filter Global Periode Tanggal -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 sm:p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <form method="GET" action="{{ route('reports.division.index') }}" class="flex flex-wrap items-center gap-4 text-xs">
            <span class="font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                <i data-lucide="calendar" class="w-4 h-4 text-[#0284c7]"></i>
                Filter Periode Tanggal Ekspor:
            </span>
            <div class="flex items-center gap-2">
                <input type="date" id="global_date_from" value="{{ request('date_from') }}" class="text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white">
                <span class="text-slate-400">s/d</span>
                <input type="date" id="global_date_to" value="{{ request('date_to') }}" class="text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white">
            </div>
            <span class="text-[11px] text-slate-400">Tanggal akan otomatis diterapkan saat mengklik tombol ekspor di bawah</span>
        </form>
    </div>

    <!-- Apple HIG Bento Grid 4 Divisi (MoM Poin 6) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 1. Divisi Marketing -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col justify-between hover:border-blue-500/30 transition-all">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="p-3 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                            <i data-lucide="trending-up" class="w-6 h-6"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Divisi Marketing & Sales</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pipeline Penawaran & Rasio Konversi</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                        Win Rate: {{ $marketingWinRate }}%
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 py-3 border-y border-black/[0.04] dark:border-white/[0.04] text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Total Quotation Terbit</span>
                        <span class="font-bold text-base text-slate-800 dark:text-slate-200">{{ $totalQuotations }} Lembar</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Pipeline Berjalan</span>
                        <span class="font-bold text-base text-blue-600 dark:text-blue-400">Rp {{ number_format($totalPipelineValue, 0, ',', '.') }}</span>
                    </div>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-4 leading-relaxed">
                    Data berisi nomor quotation, nama prospek, layanan, rute origin-destination, masa berlaku, dan status konversi menjadi invoice/job order.
                </p>
            </div>

            <div class="mt-6 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button onclick="downloadReport('marketing')" class="w-full btn-apple-filled py-2.5 text-xs flex items-center justify-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Export Rekap Marketing (.xlsx)</span>
                </button>
            </div>
        </div>

        <!-- 2. Divisi Dokumen Pabean -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col justify-between hover:border-emerald-500/30 transition-all">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="p-3 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="file-check-2" class="w-6 h-6"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Divisi Dokumen Pabean</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Status Dokumen PIB / PEB & Respon CEISA</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                        {{ $sppbCount }} SPPB Selesai
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 py-3 border-y border-black/[0.04] dark:border-white/[0.04] text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Jalur Hijau</span>
                        <span class="font-bold text-base text-emerald-600 dark:text-emerald-400">{{ $jalurHijauCount }} Dokumen</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Jalur Merah (Behandle)</span>
                        <span class="font-bold text-base text-rose-600 dark:text-rose-400">{{ $jalurMerahCount }} Dokumen</span>
                    </div>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-4 leading-relaxed">
                    Data mencakup nomor aju, nomor pendaftaran, klasifikasi jalur, tagihan bea masuk & pajak pabean, serta tracking respon resmi Bea Cukai.
                </p>
            </div>

            <div class="mt-6 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button onclick="downloadReport('document')" class="w-full btn-apple-filled py-2.5 text-xs flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Export Rekap Dokumen (.xlsx)</span>
                </button>
            </div>
        </div>

        <!-- 3. Divisi Traffic & Lapangan -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col justify-between hover:border-amber-500/30 transition-all">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="p-3 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <i data-lucide="truck" class="w-6 h-6"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Divisi Traffic & Lapangan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Utilisasi Armada & Performa Delivery (POD)</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                        {{ $activeDeliveries }} Kargo Bergerak
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 py-3 border-y border-black/[0.04] dark:border-white/[0.04] text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Kargo Tiba di Gudang (POD)</span>
                        <span class="font-bold text-base text-slate-800 dark:text-slate-200">{{ $completedPod }} Pengiriman</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Template Surat Jalan</span>
                        <span class="font-bold text-base text-amber-600 dark:text-amber-400">4 Format Aktif</span>
                    </div>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-4 leading-relaxed">
                    Data berisi nama kapal, voyage, nomor kontainer, vendor trucking, nopol, driver, log ETA, Gate Out, dan tanda terima POD gudang klien.
                </p>
            </div>

            <div class="mt-6 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button onclick="downloadReport('traffic')" class="w-full btn-apple-filled py-2.5 text-xs flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Export Rekap Traffic (.xlsx)</span>
                </button>
            </div>
        </div>

        <!-- 4. Divisi Finance & Akuntansi -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col justify-between hover:border-purple-500/30 transition-all">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="p-3 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400">
                            <i data-lucide="receipt" class="w-6 h-6"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Divisi Finance & Penagihan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Cashflow, Tagihan Invoice & Aging AR</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                        Kas: Rp {{ number_format($cashBalance, 0, ',', '.') }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 py-3 border-y border-black/[0.04] dark:border-white/[0.04] text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Piutang Berjalan (AR)</span>
                        <span class="font-bold text-base text-rose-600 dark:text-rose-400">Rp {{ number_format($totalAr, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Invoice Telah Lunas</span>
                        <span class="font-bold text-base text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                    </div>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-4 leading-relaxed">
                    Data memuat rincian invoice, tanggal jatuh tempo, pemotongan deposit klien, sisa piutang, dan klasifikasi umur piutang (Aging AR).
                </p>
            </div>

            <div class="mt-6 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button onclick="downloadReport('finance')" class="w-full btn-apple-filled py-2.5 text-xs flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Export Rekap Finance (.xlsx)</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function downloadReport(division) {
        const from = document.getElementById('global_date_from').value;
        const to = document.getElementById('global_date_to').value;
        let url = `{{ route('reports.division.export') }}?division=${division}`;
        if (from) url += `&date_from=${from}`;
        if (to) url += `&date_to=${to}`;
        window.location.href = url;
    }
</script>
@endsection