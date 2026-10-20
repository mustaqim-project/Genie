@extends('layouts.app')

@section('title', 'CRM — Surat Penawaran Harga (Quotation) Forwarder & PPJK')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12">

    <!-- ZONA 1: EXECUTIVE ANCHOR & BENTO KPI GRID -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('crm.index') }}" class="hover:text-slate-800 transition-colors">CRM</a>
                <span>/</span>
                <span class="text-sky-700 font-bold">Surat Penawaran (Quotation)</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center space-x-2">
                <span>Surat Penawaran Harga (Quotation)</span>
                <span class="px-2 py-0.5 text-xs font-mono font-bold bg-sky-100 text-sky-800 rounded-lg">P01 Pre-Ops</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Penawaran tarif freight, handling customs clearance PPJK, trucking inland, dan validitas masa berlaku tarif kargo.
            </p>
        </div>
        <div class="flex items-center space-x-2.5">
            <button onclick="document.getElementById('modal-create-quotation').classList.remove('hidden')"
                    class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition-all active:scale-95 cursor-pointer">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Penawaran Baru</span>
            </button>
        </div>
    </div>

    <!-- Apple HIG Bento 4-KPI Summary Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center space-x-3.5 hover:border-slate-300 transition-all">
            <div class="w-11 h-11 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="overflow-hidden">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Penawaran</div>
                <div class="text-xl font-extrabold text-slate-900 font-mono">{{ $stats['total'] ?? $quotations->total() }}</div>
                <div class="text-[10px] text-slate-400 truncate">Semua arsip quotation</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center space-x-3.5 hover:border-slate-300 transition-all">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div class="overflow-hidden">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Draft Disiapkan</div>
                <div class="text-xl font-extrabold text-slate-700 font-mono">{{ $stats['draft'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 truncate">Kalkulasi biaya internal</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center space-x-3.5 hover:border-slate-300 transition-all">
            <div class="w-11 h-11 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </div>
            <div class="overflow-hidden">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Terkirim ke Klien</div>
                <div class="text-xl font-extrabold text-sky-700 font-mono">{{ $stats['sent'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 truncate">Menunggu konfirmasi prospek</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center space-x-3.5 hover:border-slate-300 transition-all">
            <div class="w-11 h-11 rounded-xl bg-[#34C759]/10 text-[#34C759] flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="overflow-hidden">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Deal Disetujui</div>
                <div class="text-xl font-extrabold text-emerald-700 font-mono">{{ $stats['accepted'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 truncate">Siap buka proyek shipment</div>
            </div>
        </div>
    </div>

    <!-- ZONA 2: TOOLBAR FILTER STATUS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 mb-6">
        <form method="GET" action="{{ route('crm.quotations.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="status" class="px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                    <option value="">-- Semua Status Quotation --</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT (Kalkulasi)</option>
                    <option value="SENT" {{ request('status') === 'SENT' ? 'selected' : '' }}>SENT (Terkirim)</option>
                    <option value="ACCEPTED" {{ request('status') === 'ACCEPTED' ? 'selected' : '' }}>ACCEPTED (Disetujui / Deal)</option>
                    <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>REJECTED (Batal)</option>
                </select>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition-all shadow-xs active:scale-95 cursor-pointer">
                    Filter
                </button>
                @if(request('status'))
                    <a href="{{ route('crm.quotations.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold transition-all">
                        Reset
                    </a>
                @endif
            </div>
            <div class="text-slate-400 text-[11px] text-right">
                Menampilkan <strong>{{ $quotations->count() }}</strong> dari <strong>{{ $quotations->total() }}</strong> penawaran
            </div>
        </form>
    </div>

    <!-- ZONA 3: DATA LEDGER TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <span class="font-bold text-xs text-slate-800">Daftar Penawaran Harga Forwarder & PPJK</span>
            <span class="text-[11px] text-slate-400">Total: {{ $quotations->total() }} penawaran</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-600 font-semibold border-b border-slate-100 uppercase text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4">No. Quotation & Tgl</th>
                        <th class="py-3.5 px-4">Klien Prospek</th>
                        <th class="py-3.5 px-4">Rute Pengiriman</th>
                        <th class="py-3.5 px-4 text-right">Total Estimasi (IDR)</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($quotations as $quo)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono">
                                <span class="font-bold text-slate-900">{{ $quo->quotation_number ?? $quo->quotation_no }}</span>
                                <div class="text-[10px] text-slate-400 mt-0.5">Tgl: {{ $quo->issue_date ? $quo->issue_date->format('d/m/Y') : ($quo->created_at ? $quo->created_at->format('d/m/Y') : '-') }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-800">
                                <div class="font-bold text-slate-900">{{ $quo->client->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">NPWP: {{ $quo->client->npwp ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                <div class="font-medium text-slate-800">{{ $quo->origin_port ?? 'Shanghai' }} &rarr; {{ $quo->destination_port ?? 'Tanjung Priok' }}</div>
                                @if($quo->valid_until)
                                    <div class="text-[10px] text-slate-400 mt-0.5">Berlaku s/d: {{ \Carbon\Carbon::parse($quo->valid_until)->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 text-sm">
                                Rp {{ number_format($quo->total_estimate ?? $quo->total_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono
                                    {{ $quo->status === 'ACCEPTED' ? 'bg-emerald-100 text-emerald-800' : ($quo->status === 'SENT' ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $quo->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('pdf.quotation', $quo->id) }}" target="_blank"
                                   class="inline-flex items-center space-x-1 px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-[11px] shadow-xs transition-all active:scale-95">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak PDF</span>
                                </a>
                                @if($quo->status === 'SENT')
                                    <form action="{{ route('crm.quotations.status', $quo->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="status" value="ACCEPTED">
                                        <button type="submit" class="inline-flex items-center space-x-1 px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs transition-all active:scale-95 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Deal (Setujui)</span>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-800">Belum Ada Penawaran Harga</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Klik tombol "Buat Penawaran Baru" di atas untuk menerbitkan penawaran harga pertama Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $quotations->links() }}
        </div>
    </div>

</div>

<!-- Modal Form Buat Quotation -->
<div id="modal-create-quotation" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xl max-w-md w-full p-6 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Buat Surat Penawaran Harga Baru</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Penetapan tarif freight & jasa pengurusan kepabeanan</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-create-quotation').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('crm.quotations.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Pilih Klien / Prospek <span class="text-rose-500">*</span></label>
                <select name="client_id" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                    <option value="">-- Pilih Klien --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pelabuhan Asal <span class="text-rose-500">*</span></label>
                    <input type="text" name="origin_port" required value="Shanghai (CNSHA)" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pelabuhan Tujuan <span class="text-rose-500">*</span></label>
                    <input type="text" name="destination_port" required value="Tanjung Priok (IDTPP)" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Estimasi Total Penawaran (IDR) <span class="text-rose-500">*</span></label>
                <input type="number" name="total_estimate" required placeholder="Contoh: 26800000" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Masa Berlaku Penawaran Sampai</label>
                <input type="date" name="valid_until" value="{{ now()->addDays(30)->format('Y-m-d') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-create-quotation').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-semibold transition-all shadow-xs">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition-all shadow-xs active:scale-95 cursor-pointer">Terbitkan Quotation</button>
            </div>
        </form>
    </div>
</div>
@endsection
