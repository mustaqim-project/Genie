@extends('layouts.app')

@section('title', 'CRM — Surat Penawaran Harga (Quotation) Forwarder & PPJK')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800">CRM</span> / Quotation
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Surat Penawaran Harga (Quotation)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penawaran tarif freight, handling customs clearance PPJK, trucking, dan validitas penawaran</p>
        </div>
        <div>
            <button onclick="document.getElementById('modal-create-quotation').classList.remove('hidden')"
                    class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Penawaran Baru</span>
            </button>
        </div>
    </div>

    <!-- Table Quotations -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8 text-xs">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <span class="font-bold text-slate-800">Daftar Penawaran Harga Forwarder</span>
            <span class="text-slate-400">Total: {{ $quotations->total() }} penawaran</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">No. Quotation & Tgl</th>
                        <th class="py-3 px-4">Klien Prospek</th>
                        <th class="py-3 px-4">Rute Pengiriman</th>
                        <th class="py-3 px-4 text-right">Total Estimasi (IDR)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($quotations as $quo)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">
                                <span class="font-bold text-slate-900">{{ $quo->quotation_number ?? $quo->quotation_no }}</span>
                                <div class="text-[10px] text-slate-400">{{ $quo->issue_date ? $quo->issue_date->format('d/m/Y') : '-' }}</div>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $quo->client->name ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                {{ $quo->origin_port ?? 'Shanghai' }} &rarr; {{ $quo->destination_port ?? 'Tanjung Priok' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($quo->total_estimate ?? $quo->total_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                    {{ $quo->status === 'ACCEPTED' ? 'bg-emerald-100 text-emerald-800' : ($quo->status === 'SENT' ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $quo->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right space-x-1.5">
                                <a href="{{ route('pdf.quotation', $quo->id) }}" target="_blank"
                                   class="px-2.5 py-1.5 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 font-bold text-[11px]">
                                    Cetak PDF
                                </a>
                                @if($quo->status === 'SENT')
                                    <form action="{{ route('crm.quotations.status', $quo->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="status" value="ACCEPTED">
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px]">
                                            Deal (Setujui)
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada penawaran harga diterbitkan.
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
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-sm font-bold text-slate-900">Buat Surat Penawaran Harga Baru</h3>
            <button onclick="document.getElementById('modal-create-quotation').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('crm.quotations.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Pilih Klien / Prospek <span class="text-rose-500">*</span></label>
                <select name="client_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    <option value="">-- Pilih Klien --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pelabuhan Asal <span class="text-rose-500">*</span></label>
                    <input type="text" name="origin_port" required value="Shanghai (CNSHA)" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pelabuhan Tujuan <span class="text-rose-500">*</span></label>
                    <input type="text" name="destination_port" required value="Tanjung Priok (IDTPP)" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Estimasi Total Penawaran (IDR) <span class="text-rose-500">*</span></label>
                <input type="number" name="total_estimate" required placeholder="Contoh: 26800000" class="w-full px-3 py-2 rounded-xl border border-slate-200">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Masa Berlaku Penawaran Sampai</label>
                <input type="date" name="valid_until" value="{{ now()->addDays(30)->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-create-quotation').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold">Terbitkan Quotation</button>
            </div>
        </form>
    </div>
</div>
@endsection
