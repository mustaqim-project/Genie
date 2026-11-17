@extends('layouts.app')

@section('title', 'Kamus HS Code per Klien - Dokumen Pabean')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" x-data="{ openAddModal: false }">
    <!-- Header & Action -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7] transition-colors flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <a href="{{ route('core.tracking.index') }}" class="hover:text-[#0284c7] transition-colors">Core Dokumen</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <span class="text-slate-800 dark:text-white font-semibold">Kamus HS Code Klien</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Kamus HS Code per Klien</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Basis data preseden pos tarif khusus tiap importir/eksportir untuk mempercepat draft PIB/PEB saat repeat order</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button @click="openAddModal = true" class="btn-apple-filled px-4 py-2 text-xs inline-flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Tambah Referensi Klien</span>
            </button>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Search & Filter Card -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 sm:p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <form method="GET" action="{{ route('core.client-hs-codes.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <!-- Filter Klien -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Pilih Klien</label>
                <select name="client_id" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7] focus:border-[#0284c7]">
                    <option value="">-- Semua Klien --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->nama_perusahaan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search Uraian / HS Code -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Cari Uraian / Nomor HS</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kata kunci atau HS Code..." class="w-full pl-8 pr-3 text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7] focus:border-[#0284c7]">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            <!-- Tombol Filter -->
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-apple-filled flex-1 py-2 text-xs flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan Filter</span>
                </button>
                <a href="{{ route('core.client-hs-codes.index') }}" class="btn-apple-gray py-2 px-3 text-xs flex items-center justify-center text-slate-500">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Kamus HS Code Klien -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Database Referensi Tarif Klien</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pos tarif, perizinan lartas, dan spesifikasi barang yang biasa diimpor/diekspor oleh klien</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-neutral-800 text-slate-600 dark:text-slate-400">
                {{ $records->total() }} Referensi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-slate-50/60 dark:bg-neutral-800/40 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-4">Klien / Perusahaan</th>
                        <th class="py-3 px-4">HS Code & Uraian Klien</th>
                        <th class="py-3 px-4">Spesifikasi Teknis</th>
                        <th class="py-3 px-4 text-center">Tarif BM / PPN / PPh</th>
                        <th class="py-3 px-4">Regulasi & Lartas</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04] text-slate-700 dark:text-slate-300">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-neutral-800/30 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $rec->client->nama_perusahaan ?? '-' }}</span>
                                <div class="text-[10px] text-slate-400">Asal: {{ $rec->negara_asal_default ?? 'CN' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-mono font-bold text-blue-600 dark:text-blue-400 text-xs">
                                    {{ $rec->hsCode->code ?? '-' }}
                                </div>
                                <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5 max-w-xs truncate" title="{{ $rec->uraian_barang_client }}">
                                    {{ $rec->uraian_barang_client }}
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-500 max-w-xs truncate" title="{{ $rec->spesifikasi_teknis ?? '-' }}">
                                {{ $rec->spesifikasi_teknis ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1 text-[11px] font-medium">
                                    <span class="px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">BM {{ $rec->tarif_bm_persen }}%</span>
                                    <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">PPN {{ $rec->tarif_ppn_persen }}%</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-neutral-800 text-slate-700 dark:text-slate-300">PPh {{ $rec->tarif_pph_persen }}%</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($rec->catatan_lartas)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded-full">
                                        <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                        <span>{{ Str::limit($rec->catatan_lartas, 40) }}</span>
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-400">Bebas Lartas</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <form action="{{ route('core.client-hs-codes.destroy', $rec->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus referensi HS Code ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 transition-colors" title="Hapus Referensi">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                <i data-lucide="book-open" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                Belum ada kamus HS Code tersimpan untuk klien ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                {{ $records->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Referensi HS Code Klien -->
    <div x-show="openAddModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl max-w-xl w-full p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xl space-y-4" @click.away="openAddModal = false">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-blue-500/10 text-blue-600">
                        <i data-lucide="book-plus" class="w-4 h-4"></i>
                    </span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tambah Preseden HS Code Klien</h3>
                </div>
                <button @click="openAddModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('core.client-hs-codes.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Klien / Importir <span class="text-rose-500">*</span></label>
                    <select name="client_id" required class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7]">
                        <option value="">-- Pilih Perusahaan Klien --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->nama_perusahaan }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih dari Master HS Code</label>
                        <select name="hs_code_id" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7]">
                            <option value="">-- Atau Ketik Manual di Kanan --</option>
                            @foreach($masterHsCodes as $mhs)
                                <option value="{{ $mhs->id }}">{{ $mhs->code }} - {{ Str::limit($mhs->description, 25) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor HS Manual (Jika Baru)</label>
                        <input type="text" name="hs_code_manual" placeholder="Misal: 8471.30.20" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Uraian Barang Khusus Klien <span class="text-rose-500">*</span></label>
                    <textarea name="uraian_barang_client" required rows="2" placeholder="Nama dagang atau deskripsi produk yang tercantum pada Invoice / Packing List klien..." class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7]"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Spesifikasi Teknis / Tipe</label>
                        <input type="text" name="spesifikasi_teknis" placeholder="Misal: Brand XYZ, Model 2026" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Negara Asal Default (Kode ISO)</label>
                        <input type="text" name="negara_asal_default" value="CN" placeholder="Misal: CN, JP, US, SG" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7]">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarif BM (%)</label>
                        <input type="number" step="0.01" name="tarif_bm_persen" value="0" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarif PPN (%)</label>
                        <input type="number" step="0.01" name="tarif_ppn_persen" value="11" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarif PPh (%)</label>
                        <input type="number" step="0.01" name="tarif_pph_persen" value="2.5" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Lartas / Dokumen Perizinan</label>
                    <input type="text" name="catatan_lartas" placeholder="Misal: LS Surveyor, Persetujuan Impor (PI), Karantina" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7]">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="openAddModal = false" class="btn-apple-gray px-4 py-2 text-xs">
                        Batal
                    </button>
                    <button type="submit" class="btn-apple-filled px-4 py-2 text-xs flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>Simpan ke Kamus</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection