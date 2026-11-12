@extends('layouts.app')

@section('title', 'Import Massal & Ekstraksi Dokumen (F15)')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">

    <!-- Page Header (macOS Toolbar Style) -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">
                    Import Massal & Ekstraksi Otomatis (F15)
                </h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#34C759]">
                    Genie Core v2.0
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 leading-snug">
                Unggah ratusan berkas ekspor/impor sekaligus (PDF, Gambar, ZIP, Folder), ekstraksi otomatis OCR & pos tarif, serta lompat tahap pabean historis (F16).
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('core.document.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] text-[13px] font-medium bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black dark:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Kembali ke Proyek
            </a>
        </div>
    </header>

    <!-- KPI Bento Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 shadow-sm">
            <span class="text-[12px] font-semibold text-black/45 dark:text-white/45">Total Batch Unggahan</span>
            <p class="text-[24px] font-bold text-black dark:text-white mt-1">{{ number_format($stats['total_batches']) }}</p>
            <span class="text-[11px] text-black/40 dark:text-white/40">Sesi pemrosesan batch</span>
        </div>
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 shadow-sm">
            <span class="text-[12px] font-semibold text-black/45 dark:text-white/45">Total Berkas Dipindai</span>
            <p class="text-[24px] font-bold text-[#007AFF] mt-1">{{ number_format($stats['total_files_processed']) }}</p>
            <span class="text-[11px] text-black/40 dark:text-white/40">Invoice, BL, PL, PIB, dll</span>
        </div>
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 shadow-sm">
            <span class="text-[12px] font-semibold text-black/45 dark:text-white/45">Menunggu Review Staf</span>
            <p class="text-[24px] font-bold text-[#FF9500] mt-1">{{ number_format($stats['pending_review_docs']) }}</p>
            <span class="text-[11px] text-[#FF9500]">Antrean verifikasi form</span>
        </div>
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 shadow-sm">
            <span class="text-[12px] font-semibold text-black/45 dark:text-white/45">Telah Diterapkan</span>
            <p class="text-[24px] font-bold text-[#34C759] mt-1">{{ number_format($stats['applied_docs']) }}</p>
            <span class="text-[11px] text-[#34C759]">Tersambung ke Proyek Aktif</span>
        </div>
    </div>

    <!-- Area Unggah Massal (Drag & Drop Card) -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-6 shadow-sm">
        <h2 class="text-[16px] font-bold text-black dark:text-white mb-2">Unggah Dokumen Massal Baru</h2>
        <p class="text-[13px] text-black/50 dark:text-white/50 mb-4">
            Pilih beberapa dokumen (PDF, JPG, PNG, TIFF, Excel) atau seret folder / file ZIP arsip kepabeanan langsung ke area di bawah ini.
        </p>

        <form action="{{ route('core.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Pilih Klien Importir/Eksportir (Opsional)</label>
                    <select name="client_id" class="w-full rounded-[10px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/15 px-3 py-2 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <option value="">-- Deteksi Otomatis dari Dokumen --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (NPWP: {{ $c->npwp }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Catatan Batch / Keterangan Arsip</label>
                    <input type="text" name="notes" placeholder="Contoh: Arsip Pengapalan Q3 2026 atau Proyek Khusus" class="w-full rounded-[10px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/15 px-3 py-2 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>
            </div>

            <!-- Drag & Drop Zone -->
            <div class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#007AFF] rounded-[16px] p-8 text-center transition bg-black/[0.02] dark:bg-white/[0.02] flex flex-col items-center justify-center cursor-pointer" onclick="document.getElementById('batchFileInput').click()">
                <svg class="w-12 h-12 text-[#007AFF] mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                <p class="text-[14px] font-semibold text-black dark:text-white">Klik untuk memilih berkas atau seret berkas ke sini</p>
                <p class="text-[12px] text-black/45 dark:text-white/45 mt-1">Mendukung banyak file PDF, gambar, scan, folder, atau berkas ZIP (Maksimal 50 MB per berkas)</p>
                <input type="file" id="batchFileInput" name="files[]" multiple required class="hidden" onchange="document.getElementById('selectedFilesCount').innerText = this.files.length + ' berkas terpilih siap diunggah.'">
                <span id="selectedFilesCount" class="text-[12px] font-medium text-[#007AFF] mt-2"></span>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[12px] text-[13px] font-semibold bg-[#007AFF] hover:bg-[#0066CC] text-white shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Mulai Ekstraksi Batch Cerdas
                </button>
            </div>
        </form>
    </div>

    <!-- Riwayat Batch Unggahan -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-[15px] font-bold text-black dark:text-white">Daftar Batch Import Dokumen</h3>
            <span class="text-[12px] text-black/45 dark:text-white/45">Diurutkan berdasarkan batch terbaru</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-black/50 dark:text-white/50 text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Nomor Batch</th>
                        <th class="px-6 py-3">Waktu Unggah</th>
                        <th class="px-6 py-3">Klien Target</th>
                        <th class="px-6 py-3 text-center">Jumlah Berkas</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/10">
                    @forelse($batches as $b)
                    <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition">
                        <td class="px-6 py-4 font-mono font-semibold text-[#007AFF]">
                            <a href="{{ route('core.import.show', $b->id) }}">{{ $b->batch_no }}</a>
                        </td>
                        <td class="px-6 py-4 text-black/70 dark:text-white/70">
                            {{ $b->created_at->format('d M Y H:i') }}
                            <span class="block text-[11px] text-black/40 dark:text-white/40">oleh {{ $b->uploader?->name ?? 'System' }}</span>
                        </td>
                        <td class="px-6 py-4 text-black/80 dark:text-white/80">
                            {{ $b->client?->name ?? 'Deteksi Campuran' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-semibold">{{ $b->processed_files }}</span> / {{ $b->total_files }}
                            <span class="block text-[10px] text-[#34C759] font-medium">{{ $b->success_count }} Sukses</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($b->status === 'COMPLETED')
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#34C759]">Selesai</span>
                            @elseif($b->status === 'PROCESSING')
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">Memproses</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30]">{{ $b->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('core.import.show', $b->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-[8px] text-[12px] font-semibold bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] transition">
                                Buka Batch
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-black/40 dark:text-white/40">
                            Belum ada batch import yang diunggah. Unggah berkas dokumen pertama Anda di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
        <div class="px-6 py-4 border-t border-black/5 dark:border-white/10">
            {{ $batches->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
