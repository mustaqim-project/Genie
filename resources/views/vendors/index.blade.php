@extends('layouts.app')

@section('title', 'Master Vendor Pelayaran & Ekspedisi')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-black/90 dark:text-white/90">
                Master Vendor Pelayaran & Ekspedisi
            </h1>
            <p class="text-sm text-black/50 dark:text-white/50 mt-1">
                Kelola direktori mitra pelayaran (Shipping Lines), agen DO, ekspedisi truk, dan rekening perbankan untuk penebusan DO & settlement biaya.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('modal-add-vendor').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-medium transition shadow-xs">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Tambah Vendor
            </button>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white/70 dark:bg-white/5 backdrop-blur-md p-4 rounded-2xl border border-black/5 dark:border-white/10">
        <form method="GET" action="{{ route('vendors.index') }}" class="flex items-center gap-3 w-full sm:w-96">
            <div class="relative w-full">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Cari kode, nama vendor, atau PIC..."
                       class="w-full pl-10 pr-4 py-2 rounded-xl border border-black/10 dark:border-white/15 bg-white dark:bg-white/5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-black/5 dark:bg-white/10 hover:bg-black/10 text-sm font-medium transition">
                Cari
            </button>
        </form>
        <div class="text-sm text-black/50 dark:text-white/50">
            Total: <span class="font-mono font-bold text-black/80 dark:text-white/80 tabular-nums">{{ $vendors->total() }}</span> Vendor Terdaftar
        </div>
    </div>

    <!-- Vendor Table Bento Card -->
    <div class="bg-white/70 dark:bg-white/5 backdrop-blur-md rounded-2xl border border-black/5 dark:border-white/10 overflow-hidden shadow-2xs">
        <div class="overflow-x-auto custom-scrollbar relative">
            <table class="w-full text-left text-sm text-black/80 dark:text-white/80 border-separate border-spacing-0 min-w-max whitespace-nowrap">
                <thead class="bg-black/5 dark:bg-white/5 text-xs uppercase tracking-wider text-black/60 dark:text-white/60 font-semibold border-b border-black/5 dark:border-white/10">
                    <tr>
                        <th class="px-6 py-3.5">Kode & Vendor</th>
                        <th class="px-6 py-3.5">Kontak PIC DO</th>
                        <th class="px-6 py-3.5">Rekening Bank</th>
                        <th class="px-6 py-3.5">Portal / Web Tracking</th>
                        <th class="px-6 py-3.5 text-center">Total Proyek</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right sticky right-0 z-20 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md border-l border-black/5 dark:border-white/10 shadow-[-6px_0_12px_rgba(0,0,0,0.06)] table-freeze-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/10 font-normal">
                    @forelse($vendors as $vendor)
                    <tr class="hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-black/90 dark:text-white/90">{{ $vendor->name }}</div>
                            <div class="font-mono text-xs text-sky-600 dark:text-sky-400 mt-0.5">{{ $vendor->code }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium">{{ $vendor->pic_name ?? '—' }}</div>
                            <div class="text-xs text-black/50 dark:text-white/50 mt-0.5">
                                {{ $vendor->pic_phone ?? '' }} {{ $vendor->pic_email ? '(' . $vendor->pic_email . ')' : '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($vendor->bank_name)
                                <div class="font-mono text-xs font-semibold">{{ $vendor->bank_name }}: {{ $vendor->bank_account_no }}</div>
                                <div class="text-xs text-black/50 dark:text-white/50">a.n {{ $vendor->bank_account_name }}</div>
                            @else
                                <span class="text-black/30 dark:text-white/30 text-xs italic">Belum diisi</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($vendor->portal_url)
                                <a href="{{ $vendor->portal_url }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1 text-xs text-sky-600 dark:text-sky-400 hover:underline">
                                    <span>Buka Portal</span>
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            @else
                                <span class="text-black/30 dark:text-white/30 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold bg-black/5 dark:bg-white/10 tabular-nums">
                                {{ $vendor->shipments_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($vendor->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-300 dark:border-rose-800">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap sticky right-0 z-10 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md border-l border-black/5 dark:border-white/10 shadow-[-6px_0_12px_rgba(0,0,0,0.06)] table-freeze-right">
                            <div class="flex items-center justify-end gap-2">
                                <form action="{{ route('vendors.toggle-status', $vendor->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 text-black/60 dark:text-white/60 transition"
                                            title="{{ $vendor->is_active ? 'Nonaktifkan Vendor' : 'Aktifkan Vendor' }}">
                                        <i data-lucide="{{ $vendor->is_active ? 'pause-circle' : 'play-circle' }}" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" class="inline" onsubmit="return confirmSweetAlert(event, 'Hapus vendor ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950 text-rose-600 transition"
                                            title="Hapus Vendor">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-black/40 dark:text-white/40">
                            <i data-lucide="anchor" class="w-10 h-10 mx-auto mb-2 opacity-30"></i>
                            <p class="font-medium">Belum ada data vendor pelayaran terdaftar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vendors->hasPages())
        <div class="px-6 py-4 border-t border-black/5 dark:border-white/10">
            {{ $vendors->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Tambah Vendor -->
    <div id="modal-add-vendor" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs hidden">
        <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 w-full max-w-lg border border-black/10 dark:border-white/15 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <h3 class="text-lg font-bold text-black/90 dark:text-white/90">Daftarkan Vendor Pelayaran Baru</h3>
                <button onclick="document.getElementById('modal-add-vendor').classList.add('hidden')" class="p-1 text-black/40 hover:text-black/70 dark:text-white/40">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('vendors.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Nama Perusahaan Pelayaran / Ekspedisi *</label>
                    <input type="text" name="name" required placeholder="Contoh: PT Maersk Indonesia Line"
                           class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/15 bg-white dark:bg-white/5 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Nama PIC Operasional</label>
                        <input type="text" name="pic_name" placeholder="Budi Santoso"
                               class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/15 bg-white dark:bg-white/5 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">No. WhatsApp / Telp PIC</label>
                        <input type="text" name="pic_phone" placeholder="+62812345678"
                               class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/15 bg-white dark:bg-white/5 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Email Resmi Penebusan DO</label>
                        <input type="email" name="pic_email" placeholder="do.release@vendor.com"
                               class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/15 bg-white dark:bg-white/5 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Portal Penebusan DO (URL)</label>
                        <input type="url" name="portal_url" placeholder="https://portal.vendor.com"
                               class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/15 bg-white dark:bg-white/5 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>
                <div class="p-3 bg-black/5 dark:bg-white/5 rounded-2xl space-y-3">
                    <div class="text-xs font-bold text-black/80 dark:text-white/80">Informasi Rekening Bank Vendor</div>
                    <div class="grid grid-cols-3 gap-2">
                        <input type="text" name="bank_name" placeholder="Nama Bank (BCA/Mandiri)"
                               class="col-span-1 px-3 py-1.5 rounded-xl border border-black/10 dark:border-white/15 bg-white dark:bg-white/5 text-xs">
                        <input type="text" name="bank_account_no" placeholder="Nomor Rekening"
                               class="col-span-2 px-3 py-1.5 rounded-xl border border-black/10 dark:border-white/15 bg-white dark:bg-white/5 text-xs font-mono">
                    </div>
                    <input type="text" name="bank_account_name" placeholder="Nama Pemilik Rekening Sesuai Buku Tabungan"
                           class="w-full px-3 py-1.5 rounded-xl border border-black/10 dark:border-white/15 bg-white dark:bg-white/5 text-xs">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-vendor').classList.add('hidden')"
                            class="px-4 py-2 rounded-xl bg-black/5 dark:bg-white/10 text-sm font-medium">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-medium transition shadow-xs">
                        Simpan Vendor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
