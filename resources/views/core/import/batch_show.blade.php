@extends('layouts.app')

@section('title', 'Detail Batch ' . $batch->batch_no)

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">

    <!-- Header Toolbar -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('core.import.index') }}" class="p-1 rounded-[8px] hover:bg-black/5 dark:hover:bg-white/10 text-black/60 dark:text-white/60 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                </a>
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">
                    Batch: {{ $batch->batch_no }}
                </h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                    {{ $documents->count() }} Dokumen Logis
                </span>
                @if($batch->type === 'EXPORT')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">EKSPOR (BC 3.0)</span>
                @elseif($batch->type === 'IMPORT')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF]">IMPOR (BC 2.0)</span>
                @endif
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 leading-snug">
                Diunggah oleh {{ $batch->uploader?->name ?? 'System' }} pada {{ $batch->created_at->format('d M Y H:i') }} | Status: {{ $batch->status }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasAnyPermission(['document.create', 'document.edit', 'document.delete']))
                <form action="{{ route('core.import.destroy', $batch->id) }}" method="POST" class="inline"
                      onsubmit="return confirm('Hapus draft batch {{ $batch->batch_no }} beserta seluruh aset berkas fisiknya?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-[10px] text-[13px] font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 transition cursor-pointer">
                        Hapus Draft Batch &amp; File
                    </button>
                </form>
            @endif
            <a href="{{ route('core.import.index') }}" class="px-3 py-1.5 rounded-[10px] text-[13px] font-medium bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black dark:text-white transition">
                Daftar Batch
            </a>
        </div>
    </header>

    <!-- Daftar Dokumen Logis Hasil Ekstraksi -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white">Dokumen Hasil Ekstraksi & Antrean Review</h3>
                <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5">Tinjau field form terisi otomatis dan bandingkan langsung dengan dokumen PDF sumber.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-black/50 dark:text-white/50 text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Nama Berkas Sumber</th>
                        <th class="px-6 py-3">Tipe Terdeteksi</th>
                        <th class="px-6 py-3">Kunci Kargo (B/L / Invoice)</th>
                        <th class="px-6 py-3 text-center">Status Review</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/10">
                    @forelse($documents as $doc)
                    @php
                        $blField = $doc->fields->where('field_key', 'nomor_bl_awb')->first();
                        $invField = $doc->fields->where('field_key', 'nomor_invoice')->first();
                    @endphp
                    <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition">
                        <td class="px-6 py-4">
                            <span class="font-medium text-black dark:text-white block">{{ $doc->file?->original_name }}</span>
                            <span class="text-[11px] text-black/40 dark:text-white/40">{{ $doc->file?->file_size_kb }} KB &bull; SHA: {{ substr($doc->file?->file_hash ?? '', 0, 8) }}...</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                                {{ $doc->document_type }}
                            </span>
                            <span class="block text-[11px] text-[#34C759] font-medium mt-0.5">
                                Akurasi: {{ number_format($doc->file?->type_confidence ?? 95, 1) }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 font-mono text-[12px]">
                            @if($blField)
                                <span class="block text-black dark:text-white font-semibold">B/L: {{ $blField->effective_value }}</span>
                            @endif
                            @if($invField)
                                <span class="block text-black/60 dark:text-white/60">INV: {{ $invField->effective_value }}</span>
                            @endif
                            @if(!$blField && !$invField)
                                <span class="text-black/30 dark:text-white/30 italic">Belum terpetakan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($doc->review_status === 'APPROVED')
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#34C759]">Disetujui</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#FF9500]/10 text-[#FF9500]">Perlu Review</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap space-x-1">
                            <a href="{{ route('core.import.review', $doc->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] text-[12px] font-semibold bg-[#007AFF] hover:bg-[#0066CC] text-white transition shadow-sm">
                                Review Layar Terbagi
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                            </a>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasAnyPermission(['document.create', 'document.edit', 'document.delete']))
                                <form action="{{ route('core.import.doc.destroy', $doc->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Hapus draft dokumen ini beserta file aset fisiknya?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-[8px] text-[12px] font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 transition cursor-pointer"
                                            title="Hapus Draft Dokumen & File Aset">
                                        Hapus Draft
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-black/40 dark:text-white/40">
                            Tidak ada dokumen yang ditemukan dalam batch ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
