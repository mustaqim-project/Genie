@extends('layouts.app')

@section('title', 'Finance — Invoicing Klien (P19) & Talangan Pabean')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800">Finance</span> / Invoicing Klien
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Invoice Penagihan Klien (P19)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi talangan pabean (reimbursable), biaya pelayaran, jasa forwarder/PPJK, dan PPN 1.1%</p>
        </div>
    </div>

    <!-- Table Invoices -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8 text-xs">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <span class="font-bold text-slate-800">Daftar Tagihan Invoice Klien</span>
            <span class="text-slate-400">Total: {{ $invoices->total() }} invoice</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">No. Invoice & Tanggal</th>
                        <th class="py-3 px-4">Klien (Importir)</th>
                        <th class="py-3 px-4">Job Order & BL</th>
                        <th class="py-3 px-4 text-right">Talangan Pabean</th>
                        <th class="py-3 px-4 text-right">Total Tagihan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="font-bold text-sky-600 hover:text-sky-800 hover:underline">
                                    {{ $inv->invoice_number ?? $inv->invoice_no }}
                                </a>
                                <div class="text-[10px] text-slate-400">{{ $inv->invoice_date ? $inv->invoice_date->format('d/m/Y') : '-' }}</div>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $inv->client->name ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-600">
                                {{ $inv->shipment->project_no ?? $inv->shipment->shipment_number ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-slate-700">
                                Rp {{ number_format($inv->reimbursable_amount ?? $inv->talangan_pabean_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($inv->total_amount ?? $inv->total_tagihan_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($inv->status === 'PAID')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        LUNAS
                                    </span>
                                @elseif($inv->jatuh_tempo && $inv->jatuh_tempo->isPast())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        JATUH TEMPO
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        MENUNGGU
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}"
                                   class="px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-[11px]">
                                    Detail &rarr;
                                </a>
                                <a href="{{ route('pdf.invoice', $inv->id) }}" target="_blank"
                                   class="px-2.5 py-1.5 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 font-bold text-[11px]">
                                    Cetak PDF
                                </a>
                                @if($inv->status !== 'PAID')
                                    <form action="{{ route('finance.invoices.pay', $inv->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="payment_date" value="{{ now()->format('Y-m-d') }}">
                                        <input type="hidden" name="payment_method" value="Bank Transfer BCA">
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px]">
                                            Tandai Lunas
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada invoice klien diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $invoices->links() }}
        </div>
    </div>

</div>
@endsection
