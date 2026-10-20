@extends('layouts.app')

@section('title', 'Finance - Ikhtisar Keuangan & Tagihan')

@section('content')
<div class="w-full space-y-6">
    <!-- Header & Quick Navigation -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <span class="text-slate-800 dark:text-white font-semibold">Finance & Tagihan</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Pusat Keuangan & Penagihan Logistik</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Penagihan invoice klien (P19), hutang vendor operasional (AP), likuiditas kas & bank, serta perpajakan</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('finance.invoices.index') }}" 
               class="btn-apple-filled px-3.5 py-2 text-xs inline-flex items-center gap-1.5">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Daftar Invoice P19</span>
            </a>
            <a href="{{ route('finance.bills.index') }}" 
               class="btn-apple-gray px-3.5 py-2 text-xs inline-flex items-center gap-1.5 text-rose-600 dark:text-rose-400">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                <span>Hutang Vendor (AP)</span>
            </a>
            <a href="{{ route('finance.cash-bank.index') }}" 
               class="btn-apple-gray px-3.5 py-2 text-xs inline-flex items-center gap-1.5 text-[#007AFF]">
                <i data-lucide="landmark" class="w-4 h-4"></i>
                <span>Kas & Bank</span>
            </a>
        </div>
    </div>

    <!-- Apple HIG Bento Grid KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Piutang Berjalan (AR) -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs hover:border-amber-500/30 transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Piutang Berjalan (AR)</span>
                <span class="p-2 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-3xl font-bold font-mono text-slate-900 dark:text-white tracking-tight tabular-nums">
                Rp {{ number_format($totalAr, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between pt-2 border-t border-black/[0.06] dark:border-white/[0.06]">
                <span>Tagihan Belum Lunas</span>
                <span class="font-semibold text-amber-600 dark:text-amber-400">Piutang AR</span>
            </div>
        </div>

        <!-- Billing Pabean Terbayar -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs hover:border-emerald-500/30 transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Billing Pabean Terbayar</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-3xl font-bold font-mono text-emerald-600 dark:text-emerald-400 tracking-tight tabular-nums">
                Rp {{ number_format($totalBilling, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between pt-2 border-t border-black/[0.06] dark:border-white/[0.06]">
                <span>NTPN MPN G3 Terverifikasi</span>
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">Talangan Pass-Through</span>
            </div>
        </div>

        <!-- Kas & Bank Likuiditas Real-Time -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs hover:border-[#007AFF]/30 transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Likuiditas Kas & Bank</span>
                <span class="p-2 rounded-xl bg-[#007AFF]/10 text-[#007AFF]">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-3xl font-bold font-mono text-[#007AFF] tracking-tight tabular-nums">
                Rp {{ number_format($totalCashBank, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between pt-2 border-t border-black/[0.06] dark:border-white/[0.06]">
                <span>Saldo Rekening Aktif</span>
                <span class="font-semibold text-[#007AFF]">BCA, Mandiri, Kas Kecil</span>
            </div>
        </div>
    </div>

    <!-- Table Invoicing P19 -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-black/[0.06] dark:border-white/[0.06] flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">Daftar Tagihan Shipment Klien (Invoice P19)</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Penagihan jasa kepabeanan PPJK, trucking, serta penggantian talangan pabean</p>
            </div>
            <a href="{{ route('finance.invoices.index') }}" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                <span>Kelola Semua Invoice</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-black/[0.06] dark:border-white/[0.06]">
                    <tr>
                        <th class="py-3.5 px-4">Nomor Invoice & Shipment</th>
                        <th class="py-3.5 px-4">Nama Importir / Klien</th>
                        <th class="py-3.5 px-4 text-right">Talangan Pabean (IDR)</th>
                        <th class="py-3.5 px-4 text-right">Total Tagihan (IDR)</th>
                        <th class="py-3.5 px-4 text-center">Status Pembayaran</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-900 dark:text-white">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="font-bold text-[#007AFF] hover:underline">
                                    {{ $inv->invoice_number ?? $inv->invoice_no }}
                                </a>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-sans">
                                    Shipment: {{ $inv->shipment?->shipment_number ?? $inv->shipment?->project_no ?? '-' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-800 dark:text-slate-200">
                                {{ $inv->client->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-semibold text-slate-700 dark:text-slate-300 tabular-nums">
                                Rp {{ number_format($inv->talangan_pabean_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white tabular-nums">
                                Rp {{ number_format($inv->total_tagihan_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($inv->status === 'PAID')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80">
                                        Lunas (PAID)
                                    </span>
                                @elseif($inv->jatuh_tempo && $inv->jatuh_tempo->isPast())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80">
                                        Jatuh Tempo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200/80">
                                        Menunggu Pelunasan
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="btn-apple-gray text-[11px] px-2.5 py-1 inline-flex items-center gap-1">
                                    <span>Detail</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>
                                <a href="{{ route('pdf.invoice', $inv->id) }}" target="_blank" class="btn-apple-tinted text-[11px] px-2.5 py-1 inline-flex items-center gap-1">
                                    <i data-lucide="printer" class="w-3 h-3"></i>
                                    <span>PDF</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-2xl bg-black/5 dark:bg-white/10 flex items-center justify-center text-slate-400 mb-2">
                                        <i data-lucide="receipt" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-800 dark:text-white">Belum Ada Invoice Penagihan Aktif</p>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Invoice diterbitkan otomatis setelah dokumen pabean dan biaya operasional shipment tervalidasi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.06] bg-black/[0.02] dark:bg-white/[0.02]">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
