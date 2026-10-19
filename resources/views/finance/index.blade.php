@extends('layouts.app')

@section('title', 'Finance - Penagihan & Hutang/Piutang')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800">Finance</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Finance & Pembayaran Operasional</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penagihan invoice klien (P19), hutang pelayaran/vendor (AP), piutang usaha (AR), dan kas & bank</p>
        </div>
        <div>
            <button class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md shadow-amber-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Invoice Penagihan Klien (P19)</span>
            </button>
        </div>
    </div>

    <!-- Finance Bento Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6 text-xs">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-slate-400 font-bold uppercase block mb-1">Total Piutang Berjalan (AR)</span>
            <span class="text-2xl font-black text-slate-900">Rp {{ number_format($totalAr, 0, ',', '.') }}</span>
            <span class="text-[10px] text-slate-500 block mt-2">Termasuk talangan pabean & jasa PPJK</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-slate-400 font-bold uppercase block mb-1">Billing Pabean Terbayar</span>
            <span class="text-2xl font-black text-emerald-600">Rp {{ number_format($totalBilling, 0, ',', '.') }}</span>
            <span class="text-[10px] text-slate-500 block mt-2">NTPN MPN G3 Terverifikasi</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-slate-400 font-bold uppercase block mb-1">Kas & Bank Tersedia</span>
            <span class="text-2xl font-black text-blue-600">Rp 850.000.000</span>
            <span class="text-[10px] text-slate-500 block mt-2">BCA Operasional & Mandiri Pabean</span>
        </div>
    </div>

    <!-- Table Invoicing -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <span class="font-bold text-xs text-slate-800">Daftar Penagihan Shipment (Invoice P19)</span>
            <span class="text-xs text-slate-400">Aging: 0–30 Hari</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Nomor Shipment & Aju</th>
                        <th class="py-3 px-4">Mitra / Klien</th>
                        <th class="py-3 px-4">Talangan Pabean (IDR)</th>
                        <th class="py-3 px-4">Total Tagihan (IDR)</th>
                        <th class="py-3 px-4">Status Invoice</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">
                                <span class="font-bold text-slate-800">{{ $inv->nomor_aju }}</span>
                                <div class="text-[10px] text-slate-400">{{ $inv->jenis_dokumen_label }}</div>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-700">
                                {{ $inv->nama_mitra_luar_negeri }}
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-amber-700">
                                Rp {{ number_format($inv->total_billing ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                Rp {{ number_format($inv->total_nilai_idr, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                    AR_OPEN (Menunggu Pelunasan)
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs">
                                    Cetak PDF &rarr;
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada invoice penagihan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
