@extends('layouts.app')

@section('title', "Detail Vendor - {$vendor->name}")

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vendors.index') }}" class="text-sm font-medium text-slate-500 hover:text-sky-600 transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Master Vendor
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-sm font-semibold text-slate-800">{{ $vendor->code }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">{{ $vendor->name }}</h1>
            <p class="text-sm text-slate-500">Shipping Line & Forwarding Vendor Profile</p>
        </div>
        <div class="flex items-center gap-2">
            @if($vendor->is_active)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                    Aktif
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span>
                    Nonaktif
                </span>
            @endif
            @if($vendor->portal_url)
                <a href="{{ $vendor->portal_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg border border-sky-200 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Buka Portal DO
                </a>
            @endif
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Informasi PIC & Kontak -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kontak Person (PIC)</h3>
                <span class="p-1 rounded bg-slate-50 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </span>
            </div>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">Nama PIC</span>
                    <span class="font-medium text-slate-800">{{ $vendor->pic_name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Email Resmi</span>
                    @if($vendor->pic_email)
                        <a href="mailto:{{ $vendor->pic_email }}" class="font-medium text-sky-600 hover:underline">{{ $vendor->pic_email }}</a>
                    @else
                        <span class="text-slate-400">-</span>
                    @endif
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Nomor Telepon</span>
                    @if($vendor->pic_phone)
                        <a href="tel:{{ $vendor->pic_phone }}" class="font-medium text-slate-800 hover:text-sky-600">{{ $vendor->pic_phone }}</a>
                    @else
                        <span class="text-slate-400">-</span>
                    @endif
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Alamat Kantor / Depo</span>
                    <span class="text-slate-700 text-xs">{{ $vendor->address ?? 'Belum ada data alamat' }}</span>
                </div>
            </div>
        </div>

        <!-- Rekening Pembayaran Tebus DO -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Rekening Pembayaran</h3>
                <span class="p-1 rounded bg-slate-50 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </span>
            </div>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">Nama Bank</span>
                    <span class="font-medium text-slate-800">{{ $vendor->bank_name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Nomor Rekening</span>
                    <span class="font-mono font-semibold text-slate-900 tracking-wider">{{ $vendor->bank_account_no ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Atas Nama Rekening</span>
                    <span class="font-medium text-slate-800">{{ $vendor->bank_account_name ?? '-' }}</span>
                </div>
                <div class="p-2.5 bg-slate-50 rounded-lg text-xs text-slate-600 border border-slate-100">
                    <p class="font-medium text-slate-700">Verifikasi Escrow:</p>
                    <p class="mt-0.5 text-slate-500">Rekening ini terdaftar untuk release invoice DO otomatis sistem.</p>
                </div>
            </div>
        </div>

        <!-- Aktivitas & Statistik Pengapalan -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Statistik Operasional</h3>
                <span class="p-1 rounded bg-slate-50 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </span>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-sky-50/50 rounded-lg border border-sky-100">
                    <span class="text-xs font-medium text-slate-600">Total Proyek Shipment</span>
                    <span class="text-lg font-bold text-sky-700">{{ $vendor->shipments_count ?? 0 }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block mb-1">Status Integrasi</span>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-medium text-slate-700">Wired to Workflow Engine</span>
                    </div>
                </div>
                <div class="pt-2">
                    <button type="button" onclick="document.getElementById('editVendorModal').classList.remove('hidden')" class="w-full px-3 py-2 text-xs font-medium text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg transition-colors text-center">
                        Edit Informasi Vendor
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Pengapalan Terkini -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900">Riwayat Pengapalan Terakhir (10 Terkini)</h3>
            <span class="text-xs text-slate-500">Terkait Shipping Line Ini</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3">No. B/L / AWB</th>
                        <th class="px-5 py-3">No. Pengajuan</th>
                        <th class="px-5 py-3">Kapal & Voyage</th>
                        <th class="px-5 py-3">Klien</th>
                        <th class="px-5 py-3">Status DO</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vendor->shipments as $sh)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 font-semibold text-slate-800">
                                {{ $sh->nomor_bl_awb ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-600">
                                {{ $sh->ceisaDocument?->nomor_aju ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-700">
                                {{ $sh->nama_kapal ?? '-' }} {{ $sh->voyage_no ? "({$sh->voyage_no})" : '' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-700">
                                {{ $sh->client?->name ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($sh->status_do === 'RELEASED')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">RELEASED</span>
                                @elseif($sh->status_do === 'PAID')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">PAID</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">{{ $sh->status_do ?? 'PENDING' }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('core.tracking.show', $sh->id) }}" class="text-xs font-medium text-sky-600 hover:text-sky-800">Lihat Proyek &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400 text-xs">
                                Belum ada pengapalan aktif yang tercatat menggunakan vendor ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Vendor -->
<div id="editVendorModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Edit Vendor: {{ $vendor->name }}</h3>
            <button type="button" onclick="document.getElementById('editVendorModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('vendors.update', $vendor->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Nama Vendor *</label>
                <input type="text" name="name" value="{{ old('name', $vendor->name) }}" required class="w-full text-xs rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nama PIC</label>
                    <input type="text" name="pic_name" value="{{ old('pic_name', $vendor->pic_name) }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Email PIC</label>
                    <input type="email" name="pic_email" value="{{ old('pic_email', $vendor->pic_email) }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Telepon PIC</label>
                    <input type="text" name="pic_phone" value="{{ old('pic_phone', $vendor->pic_phone) }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">URL Portal Release DO</label>
                    <input type="url" name="portal_url" value="{{ old('portal_url', $vendor->portal_url) }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $vendor->bank_name) }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">No. Rekening</label>
                    <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $vendor->bank_account_no) }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">A/N Rekening</label>
                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $vendor->bank_account_name) }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Alamat Lengkap</label>
                <textarea name="address" rows="2" class="w-full text-xs rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">{{ old('address', $vendor->address) }}</textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active_input" value="1" {{ $vendor->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                <label for="is_active_input" class="text-xs font-medium text-slate-700">Vendor Aktif</label>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('editVendorModal').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-sky-600 hover:bg-sky-700 rounded-lg shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
