@extends('layouts.app')

@section('title', 'Finance — Invoicing Klien (P19) & Talangan Pabean')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" 
     x-data="{ 
         showCreateModal: false, 
         talangan: 0, 
         pelayaran: 0, 
         ppjk: 1500000, 
         trucking: 2500000, 
         get subtotal() { return Number(this.talangan || 0) + Number(this.pelayaran || 0) + Number(this.ppjk || 0) + Number(this.trucking || 0); }, 
         get ppn() { return Math.round((Number(this.ppjk || 0) + Number(this.trucking || 0)) * 0.011); }, 
         get total() { return this.subtotal + this.ppn; } 
     }">

    <!-- 1. ZONE 1: BREADCRUMB, HEADER & EXECUTIVE KPI SUMMARY -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <a href="{{ route('finance.index') }}" class="hover:text-[#007AFF] transition-colors">Finance</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <span class="text-slate-800 dark:text-white font-semibold">Invoice Klien (P19)</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Invoice Penagihan Klien (P19)</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Rekapitulasi talangan pabean (reimbursable), biaya pelayaran, jasa freight forwarder / PPJK, dan PPN 1.1%.</p>
        </div>
        <div class="flex items-center gap-2">
            @can('create', \App\Models\ClientInvoice::class)
            <button type="button" @click="showCreateModal = true" class="btn-apple-filled">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Terbitkan Invoice Klien Baru</span>
            </button>
            @endcan
            <a href="{{ route('finance.index') }}" 
               class="btn-apple-gray px-3.5 py-2 text-xs inline-flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Pusat Keuangan</span>
            </a>
        </div>
    </div>

    <!-- Apple HIG Bento Grid KPI Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- KPI 1: Total Invoice -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex items-center gap-3.5 hover:border-[#007AFF]/30 transition-all">
            <div class="w-10 h-10 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Invoice</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">{{ $stats['total'] ?? $invoices->total() }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500">Seluruh berkas terbit</div>
            </div>
        </div>

        <!-- KPI 2: Lunas (Paid) -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex items-center gap-3.5 hover:border-emerald-500/30 transition-all">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Lunas (Paid)</div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">{{ $stats['paid'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500">Pembayaran terverifikasi</div>
            </div>
        </div>

        <!-- KPI 3: Menunggu Pelunasan -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex items-center gap-3.5 hover:border-amber-500/30 transition-all">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Menunggu Bayar</div>
                <div class="text-xl font-bold text-amber-600 dark:text-amber-400 font-mono tabular-nums">{{ $stats['unpaid'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500">Piutang belum tertagih</div>
            </div>
        </div>

        <!-- KPI 4: Total Nilai Tagihan -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex items-center gap-3.5 hover:border-purple-500/30 transition-all">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                <i data-lucide="receipt" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Nilai Tagihan</div>
                <div class="text-base font-bold text-purple-600 dark:text-purple-400 font-mono tabular-nums truncate">Rp {{ number_format($stats['total_amount'] ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500">Akumulasi seluruh invoice</div>
            </div>
        </div>
    </div>

    <!-- 2. ZONE 2: FILTER TOOLBAR -->
    <div class="bg-white dark:bg-[#1C1C1E] p-4 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <form action="{{ route('finance.invoices.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center text-xs">
            <div class="sm:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari Nomor Invoice, Nama Klien / Importir, No Project..." 
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>

            <div class="sm:col-span-3">
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">-- Semua Status Pembayaran --</option>
                    <option value="UNPAID" {{ request('status') === 'UNPAID' ? 'selected' : '' }}>MENUNGGU PEMBAYARAN</option>
                    <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>LUNAS (PAID)</option>
                    <option value="OVERDUE" {{ request('status') === 'OVERDUE' ? 'selected' : '' }}>JATUH TEMPO</option>
                </select>
            </div>

            <div class="sm:col-span-3 flex items-center gap-2">
                <button type="submit" class="btn-apple-filled flex-1 py-2 text-xs">
                    Filter
                </button>
                <a href="{{ route('finance.invoices.index') }}" class="btn-apple-gray py-2 px-3.5 text-xs">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- 3. ZONE 3: DATA LEDGER TABLE -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-hidden text-xs">
        <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.06] flex items-center justify-between">
            <span class="font-bold text-slate-900 dark:text-white text-sm">Daftar Tagihan Invoice Klien</span>
            <span class="px-2.5 py-1 rounded-lg bg-black/5 dark:bg-white/10 text-slate-600 dark:text-slate-400 font-mono text-[11px] font-bold tabular-nums">Total: {{ $invoices->total() }} invoice</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-black/[0.06] dark:border-white/[0.06]">
                    <tr>
                        <th class="py-3 px-4 cursor-pointer hover:text-slate-800 dark:hover:text-white transition-colors" @click="sortBy(0, 'string', $event)">
                            <div class="flex items-center gap-1">
                                <span>No. Invoice & Tanggal</span>
                                <i data-lucide="chevrons-up-down" class="w-3 h-3 opacity-40"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4">Klien (Importir)</th>
                        <th class="py-3 px-4">Job Order & BL</th>
                        <th class="py-3 px-4 text-right cursor-pointer hover:text-slate-800 dark:hover:text-white transition-colors" @click="sortBy(3, 'number', $event)">
                            <div class="flex items-center justify-end gap-1">
                                <span>Talangan Pabean</span>
                                <i data-lucide="chevrons-up-down" class="w-3 h-3 opacity-40"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 text-right cursor-pointer hover:text-slate-800 dark:hover:text-white transition-colors" @click="sortBy(4, 'number', $event)">
                            <div class="flex items-center justify-end gap-1">
                                <span>Total Tagihan</span>
                                <i data-lucide="chevrons-up-down" class="w-3 h-3 opacity-40"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3.5 px-4 font-mono" data-sort-value="{{ $inv->invoice_number ?? $inv->invoice_no }}">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="font-bold text-[#007AFF] hover:underline block">
                                    {{ $inv->invoice_number ?? $inv->invoice_no }}
                                </a>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-sans mt-0.5 tabular-nums">{{ $inv->invoice_date ? $inv->invoice_date->format('d/m/Y') : '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $inv->client->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-400">
                                {{ $inv->shipment->project_no ?? $inv->shipment->shipment_number ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-semibold text-slate-700 dark:text-slate-300 tabular-nums" data-sort-value="{{ $inv->reimbursable_amount ?? $inv->talangan_pabean_idr ?? 0 }}">
                                Rp {{ number_format($inv->reimbursable_amount ?? $inv->talangan_pabean_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white tabular-nums" data-sort-value="{{ $inv->total_amount ?? $inv->total_tagihan_idr ?? 0 }}">
                                Rp {{ number_format($inv->total_amount ?? $inv->total_tagihan_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($inv->status === 'PAID')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> LUNAS
                                    </span>
                                @elseif($inv->jatuh_tempo && $inv->jatuh_tempo->isPast())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> JATUH TEMPO
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> MENUNGGU
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}"
                                   class="btn-apple-gray text-[11px] px-2.5 py-1 inline-flex items-center gap-1">
                                    <span>Detail</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>
                                <a href="{{ route('pdf.invoice', $inv->id) }}" target="_blank"
                                   class="btn-apple-tinted text-[11px] px-2.5 py-1 inline-flex items-center gap-1">
                                    <i data-lucide="printer" class="w-3 h-3"></i>
                                    <span>PDF</span>
                                </a>
                                @if($inv->status !== 'PAID')
                                    <form action="{{ route('finance.invoices.pay', $inv->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Tandai invoice {{ $inv->invoice_number ?? $inv->invoice_no }} sebagai lunas?')">
                                        @csrf
                                        <input type="hidden" name="payment_date" value="{{ now()->format('Y-m-d') }}">
                                        <input type="hidden" name="payment_method" value="Bank Transfer BCA">
                                        <button type="submit" class="btn-apple-filled text-[11px] px-2.5 py-1 cursor-pointer">
                                            Tandai Lunas
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <div class="w-10 h-10 rounded-2xl bg-black/5 dark:bg-white/10 text-slate-400 mx-auto flex items-center justify-center text-lg mb-2">
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                </div>
                                <div class="font-semibold text-slate-700 dark:text-slate-300 text-xs">Belum ada invoice penagihan klien yang diterbitkan</div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Invoice diterbitkan otomatis setelah dokumen pabean dan biaya operasional shipment tervalidasi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.06] bg-black/[0.02] dark:bg-white/[0.02]">
            {{ $invoices->links() }}
        </div>
    </div>

    <!-- 3. MODAL: TERBITKAN INVOICE KLIEN (AR) MANUAL -->
    @can('create', \App\Models\ClientInvoice::class)
    <div x-show="showCreateModal" 
         class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         role="dialog"
         aria-modal="true"
         x-cloak>
        <div x-show="showCreateModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md"
             @click="showCreateModal = false"
             aria-hidden="true"></div>

        <div x-show="showCreateModal"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative z-10 bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-3xl border border-slate-200/90 dark:border-white/10 shadow-2xl max-w-xl w-full p-6 text-xs max-h-[90vh] overflow-y-auto">
            
            <div class="sm:hidden w-12 h-1.5 bg-slate-200 dark:bg-white/20 rounded-full mx-auto -mt-2 mb-4"></div>

            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/10 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Terbitkan Invoice Penagihan Klien (AR)</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Penetapan tagihan talangan pabean, pelayaran, jasa PPJK & PPN 1.1%</p>
                </div>
                <button type="button" @click="showCreateModal = false" class="btn-apple-gray !p-2 rounded-full">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('finance.invoices.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Klien / Importir <span class="text-rose-500">*</span></label>
                    <select name="client_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                        <option value="">-- Pilih Importir / Klien --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (NPWP: {{ $c->npwp ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Hubungkan ke Shipment / Project (Opsional)</label>
                    <select name="shipment_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                        <option value="">-- Tanpa Relasi Shipment (Invoice Standalone) --</option>
                        @foreach($shipments as $s)
                            <option value="{{ $s->id }}">{{ $s->project_no ?? $s->shipment_number }} — B/L: {{ $s->nomor_bl_awb ?? $s->bl_number ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Terbit <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal" required value="{{ now()->format('Y-m-d') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jatuh Tempo (Due Date) <span class="text-rose-500">*</span></label>
                        <input type="date" name="jatuh_tempo" required value="{{ now()->addDays(14)->format('Y-m-d') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-white/10">
                    <span class="block font-bold text-slate-800 dark:text-slate-200 mb-2">Rincian Pos Biaya & Rekapitulasi (IDR)</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-slate-600 dark:text-slate-400 mb-1">Talangan Pabean (Bea & Pajak Impor)</label>
                            <input type="number" step="any" name="talangan_pabean_idr" x-model.number="talangan" placeholder="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] font-mono">
                        </div>
                        <div>
                            <label class="block text-slate-600 dark:text-slate-400 mb-1">Biaya Pelayaran (THC / DO / Depo)</label>
                            <input type="number" step="any" name="biaya_pelayaran_idr" x-model.number="pelayaran" placeholder="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] font-mono">
                        </div>
                        <div>
                            <label class="block text-slate-600 dark:text-slate-400 mb-1">Jasa Pengurusan PPJK / Forwarding</label>
                            <input type="number" step="any" name="jasa_ppjk_idr" x-model.number="ppjk" placeholder="1500000" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] font-mono">
                        </div>
                        <div>
                            <label class="block text-slate-600 dark:text-slate-400 mb-1">Biaya Trucking / Transportasi Darat</label>
                            <input type="number" step="any" name="biaya_trucking_idr" x-model.number="trucking" placeholder="2500000" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] font-mono">
                        </div>
                    </div>
                </div>

                <!-- Bento Realtime Calculation Summary Box -->
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-white/[0.04] border border-slate-200/80 dark:border-white/10 space-y-1.5 font-mono">
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400 text-[11px]">
                        <span>Subtotal Biaya:</span>
                        <span class="font-semibold text-slate-900 dark:text-white" x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400 text-[11px]">
                        <span class="flex items-center gap-1 font-sans">
                            <span>PPN Jasa 1.1% (PMK 71/2022 atas Jasa):</span>
                            <span class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold">(Otomatis)</span>
                        </span>
                        <span class="font-semibold text-sky-600 dark:text-sky-400" x-text="'Rp ' + ppn.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="pt-1.5 border-t border-slate-200 dark:border-white/10 flex items-center justify-between text-xs font-bold">
                        <span class="font-sans text-slate-900 dark:text-white">Total Tagihan Invoice (AR):</span>
                        <span class="text-base text-[#007AFF]" x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan / Instruksi Pembayaran</label>
                    <textarea name="catatan" rows="2" placeholder="Contoh: Harap transfer ke Rekening BCA PT Agung Trans..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-white/10 flex items-center justify-end space-x-2">
                    <button type="button" @click="showCreateModal = false" class="btn-apple-gray">Batal</button>
                    <button type="submit" class="btn-apple-filled">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Terbitkan Invoice Klien</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan

</div>
@endsection
