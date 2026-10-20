@extends('layouts.app')

@section('title', 'CRM — Surat Penawaran Harga (Quotation) Forwarder & PPJK')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" x-data="{ showCreateModal: false }">

    <!-- Breadcrumb -->
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'CRM', 'url' => route('crm.index')],
        ['label' => 'Surat Penawaran (Quotation)']
    ]" />

    <!-- Apple HIG Module Header Bar -->
    <div class="mb-6 rounded-[16px] bg-white dark:bg-[#1C1C1E] p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl sm:text-2xl font-bold font-apple-display tracking-tight text-slate-900 dark:text-white">
                    Surat Penawaran Harga (Quotation)
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] border border-sky-200 dark:border-sky-800">
                    P01 Pre-Ops
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Penawaran tarif freight, handling customs clearance PPJK, trucking inland, dan validitas masa berlaku tarif kargo
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="showCreateModal = true"
                    class="btn-apple-filled">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buat Penawaran Baru</span>
            </button>
        </div>
    </div>

    <!-- Apple HIG Bento 4-KPI Summary Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-slate-200/80 dark:border-white/10 shadow-xs flex items-center space-x-3.5 hover:border-slate-300 dark:hover:border-white/20 transition-all">
            <div class="w-11 h-11 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center flex-shrink-0">
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
            <div class="overflow-hidden">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Penawaran</div>
                <div class="text-xl font-extrabold text-slate-900 dark:text-white font-mono tabular-nums">{{ $stats['total'] ?? $quotations->total() }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500 truncate">Semua arsip quotation</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-slate-200/80 dark:border-white/10 shadow-xs flex items-center space-x-3.5 hover:border-slate-300 dark:hover:border-white/20 transition-all">
            <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="edit" class="w-5 h-5"></i>
            </div>
            <div class="overflow-hidden">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Draft Disiapkan</div>
                <div class="text-xl font-extrabold text-slate-700 dark:text-slate-300 font-mono tabular-nums">{{ $stats['draft'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500 truncate">Kalkulasi biaya internal</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-slate-200/80 dark:border-white/10 shadow-xs flex items-center space-x-3.5 hover:border-slate-300 dark:hover:border-white/20 transition-all">
            <div class="w-11 h-11 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center flex-shrink-0">
                <i data-lucide="send" class="w-5 h-5"></i>
            </div>
            <div class="overflow-hidden">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Terkirim ke Klien</div>
                <div class="text-xl font-extrabold text-sky-700 dark:text-sky-400 font-mono tabular-nums">{{ $stats['sent'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500 truncate">Menunggu konfirmasi prospek</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-slate-200/80 dark:border-white/10 shadow-xs flex items-center space-x-3.5 hover:border-slate-300 dark:hover:border-white/20 transition-all">
            <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div class="overflow-hidden">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Deal Disetujui</div>
                <div class="text-xl font-extrabold text-emerald-700 dark:text-emerald-400 font-mono tabular-nums">{{ $stats['accepted'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500 truncate">Siap buka proyek shipment</div>
            </div>
        </div>
    </div>

    <!-- Toolbar Filter Status -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs p-4 mb-6">
        <form method="GET" action="{{ route('crm.quotations.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="status" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    <option value="">-- Semua Status Quotation --</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT (Kalkulasi)</option>
                    <option value="SENT" {{ request('status') === 'SENT' ? 'selected' : '' }}>SENT (Terkirim)</option>
                    <option value="ACCEPTED" {{ request('status') === 'ACCEPTED' ? 'selected' : '' }}>ACCEPTED (Disetujui / Deal)</option>
                    <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>REJECTED (Batal)</option>
                </select>
                <button type="submit" class="btn-apple-filled !py-2">
                    Filter
                </button>
                @if(request('status'))
                    <a href="{{ route('crm.quotations.index') }}" class="btn-apple-gray !py-2">
                        Reset
                    </a>
                @endif
            </div>
            <div class="text-slate-400 dark:text-slate-500 text-[11px] text-right tabular-nums">
                Menampilkan <strong>{{ $quotations->count() }}</strong> dari <strong>{{ $quotations->total() }}</strong> penawaran
            </div>
        </form>
    </div>

    <!-- Data Ledger Table -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between bg-slate-50/50 dark:bg-white/[0.02]">
            <span class="font-bold text-xs text-slate-800 dark:text-white">Daftar Penawaran Harga Forwarder & PPJK</span>
            <span class="text-[11px] text-slate-400 dark:text-slate-500 tabular-nums">Total: {{ $quotations->total() }} penawaran</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-[#2C2C2E] text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-100 dark:border-white/10 uppercase text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4">No. Quotation & Tgl</th>
                        <th class="py-3.5 px-4">Klien Prospek</th>
                        <th class="py-3.5 px-4">Rute Pengiriman</th>
                        <th class="py-3.5 px-4 text-right">Total Estimasi (IDR)</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($quotations as $quo)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3.5 px-4 font-mono">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $quo->quotation_number ?? $quo->quotation_no }}</span>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 tabular-nums">Tgl: {{ $quo->issue_date ? $quo->issue_date->format('d/m/Y') : ($quo->created_at ? $quo->created_at->format('d/m/Y') : '-') }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-800 dark:text-slate-200">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $quo->client->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono tabular-nums">NPWP: {{ $quo->client->npwp ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                <div class="font-medium text-slate-800 dark:text-white">{{ $quo->origin_port ?? 'Shanghai' }} &rarr; {{ $quo->destination_port ?? 'Tanjung Priok' }}</div>
                                @if($quo->valid_until)
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 tabular-nums">Berlaku s/d: {{ \Carbon\Carbon::parse($quo->valid_until)->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white text-sm tabular-nums">
                                Rp {{ number_format($quo->total_estimate ?? $quo->total_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono
                                    {{ $quo->status === 'ACCEPTED' ? 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : ($quo->status === 'SENT' ? 'bg-sky-100 dark:bg-sky-950/50 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-800' : 'bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300') }}">
                                    {{ $quo->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('pdf.quotation', $quo->id) }}" target="_blank"
                                   class="btn-apple-gray !py-1 !px-2.5 !text-[11px]">
                                    <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400"></i>
                                    <span>PDF</span>
                                </a>
                                @if($quo->status === 'SENT')
                                    <form action="{{ route('crm.quotations.status', $quo->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="status" value="ACCEPTED">
                                        <button type="submit" class="btn-apple-filled !py-1 !px-2.5 !text-[11px] !bg-emerald-600 hover:!bg-emerald-700">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            <span>Deal</span>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/10 flex items-center justify-center text-slate-400 dark:text-slate-500 mb-2">
                                        <i data-lucide="file-question" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Belum Ada Penawaran Harga</p>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Klik tombol "Buat Penawaran Baru" di atas untuk menerbitkan penawaran harga pertama Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-white/10">
            {{ $quotations->links() }}
        </div>
    </div>

    <!-- Modal Form Buat Quotation -->
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
             class="relative z-10 bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-3xl border border-slate-200/90 dark:border-white/10 shadow-2xl max-w-md w-full p-6 text-xs">
            
            <div class="sm:hidden w-12 h-1.5 bg-slate-200 dark:bg-white/20 rounded-full mx-auto -mt-2 mb-4"></div>

            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/10 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Buat Surat Penawaran Harga Baru</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Penetapan tarif freight & jasa pengurusan kepabeanan</p>
                </div>
                <button type="button" @click="showCreateModal = false" class="btn-apple-gray !p-2 rounded-full">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('crm.quotations.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Klien / Prospek <span class="text-rose-500">*</span></label>
                    <select name="client_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                        <option value="">-- Pilih Klien --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pelabuhan Asal <span class="text-rose-500">*</span></label>
                        <input type="text" name="origin_port" required value="Shanghai (CNSHA)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pelabuhan Tujuan <span class="text-rose-500">*</span></label>
                        <input type="text" name="destination_port" required value="Tanjung Priok (IDTPP)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Estimasi Total Penawaran (IDR) <span class="text-rose-500">*</span></label>
                    <input type="number" name="total_estimate" required placeholder="Contoh: 26800000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Masa Berlaku Penawaran Sampai</label>
                    <input type="date" name="valid_until" value="{{ now()->addDays(30)->format('Y-m-d') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-white/10 flex items-center justify-end space-x-2">
                    <button type="button" @click="showCreateModal = false" class="btn-apple-gray">Batal</button>
                    <button type="submit" class="btn-apple-filled">Terbitkan Quotation</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
