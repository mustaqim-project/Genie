@extends('layouts.app')

@section('title', 'Dokumen BC 3.0 (Ekspor / PEB)')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800">BC 3.0 (Ekspor)</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Pemberitahuan Ekspor Barang (BC 3.0)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manajemen dan pengajuan dokumen PEB ke server CEISA 4.0 DJBC untuk izin muat ekspor (NPE)</p>
        </div>
        <div>
            <a href="{{ route('bc30.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-md shadow-indigo-600/20 transition-all hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Pengajuan BC 3.0 Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm mb-6">
        <form action="{{ route('bc30.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nomor Aju / Pembeli LN / No Daftar..." class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <select name="status" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">-- Semua Status Dokumen --</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                    <option value="SUBMITTED" {{ request('status') === 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED</option>
                    <option value="PROSES" {{ request('status') === 'PROSES' ? 'selected' : '' }}>PROSES VALIDASI</option>
                    <option value="NPE" {{ request('status') === 'NPE' ? 'selected' : '' }}>NPE (TERBIT)</option>
                    <option value="REJECT" {{ request('status') === 'REJECT' ? 'selected' : '' }}>REJECT</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition-colors">
                    Filter
                </button>
                <a href="{{ route('bc30.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Nomor Aju (26 Digit)</th>
                        <th class="py-3 px-4">Kantor Pabean Muat</th>
                        <th class="py-3 px-4">Pembeli Luar Negeri</th>
                        <th class="py-3 px-4">Devisa Ekspor (FOB)</th>
                        <th class="py-3 px-4">Status CEISA</th>
                        <th class="py-3 px-4">Nomor NPE</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($documents as $doc)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-4 font-mono font-semibold text-slate-800">
                                <a href="{{ route('bc30.show', $doc->id) }}" class="text-indigo-600 hover:underline">
                                    {{ $doc->nomor_aju }}
                                </a>
                                @if($doc->nomor_daftar)
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">No Pen: {{ $doc->nomor_daftar }} ({{ $doc->tanggal_daftar?->format('d/m/Y') }})</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                <span class="font-semibold">{{ $doc->kode_kantor }}</span>
                                <div class="text-[10px] text-slate-400 truncate max-w-[140px]">{{ $doc->nama_kantor }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-800">{{ $doc->nama_mitra_luar_negeri }}</div>
                                <div class="text-[10px] text-slate-400">Tujuan: {{ $doc->negara_mitra_luar_negeri }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $doc->kode_valuta }} {{ number_format($doc->total_nilai, 2) }}</div>
                                <div class="text-[10px] text-slate-400">Rp {{ number_format($doc->total_nilai_idr, 0, ',', '.') }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $doc->status_badge_class }}">
                                    {{ $doc->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono">
                                @if($doc->nomor_sppb_npe)
                                    <span class="text-emerald-700 font-bold">{{ $doc->nomor_sppb_npe }}</span>
                                    <div class="text-[10px] text-slate-400">{{ $doc->tanggal_sppb_npe?->format('d/m/Y') }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('bc30.show', $doc->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 font-semibold text-xs transition-colors">
                                    Kelola &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                Belum ada pengajuan dokumen ekspor BC 3.0. Klik tombol <strong>"Buat Pengajuan BC 3.0 Baru"</strong> untuk membuat pengajuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
