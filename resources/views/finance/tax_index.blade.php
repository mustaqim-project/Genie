@extends('layouts.app')

@section('title', 'Perpajakan Forwarding & PPJK - PPN 1.1% & PPh 23')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12" x-data="{ activeTab: 'ppn_keluaran' }">

    <!-- Header & Period Filter -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1 flex items-center gap-1.5">
                <a href="{{ route('finance.index') }}" class="hover:underline text-slate-600">Finance</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Perpajakan Forwarding & PPJK</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Kepatuhan Pajak & Rekapitulasi PPN / PPh 23</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Standar PMK No. 71/PMK.03/2022 (DPP Nilai Lain PPN Efektif 1.1%), Rekap Faktur Masukan, dan Estimasi PPh 23
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Filter Bulan / Tahun -->
            <form method="GET" action="{{ route('finance.tax.index') }}" class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-xs text-xs">
                <select name="month" class="bg-transparent border-0 font-medium text-slate-700 focus:ring-0 cursor-pointer text-xs" onchange="this.form.submit()">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <span class="text-slate-300">|</span>
                <select name="year" class="bg-transparent border-0 font-medium text-slate-700 focus:ring-0 cursor-pointer text-xs" onchange="this.form.submit()">
                    @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>

            <!-- Export e-Faktur CSV -->
            <a href="{{ route('finance.tax.export', ['year' => $year, 'month' => $month]) }}" 
               class="min-h-[44px] inline-flex items-center pl-4 pr-2 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                <span>Export e-Faktur (CSV)</span>
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-800 text-emerald-400 ml-2.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </a>
        </div>
    </div>

    <!-- Alert Edukasi Regulasi PMK 71/2022 -->
    <div class="mb-6 p-4 rounded-2xl bg-sky-50/80 border border-sky-100 flex items-start gap-3 text-xs text-sky-900">
        <svg class="w-5 h-5 text-sky-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Perpajakan Forwarding & PPJK Sesuai Regulasi Indonesia:</span>
            <p class="text-sky-800 leading-relaxed">
                1) <strong>PPN Jasa Pengurusan Transportasi (Freight Forwarding)</strong> dikenakan PPN dengan <em>DPP Nilai Lain</em> sebesar 10% dari jumlah tagihan jasa atau efektif <strong>1.1%</strong> (PMK No. 71/PMK.03/2022).<br>
                2) <strong>Biaya Talangan Pabean (Bea Masuk & Pajak Impor MPN G3)</strong> merupakan <em>reimbursable pass-through outlay</em> dan <strong>bukan objek PPN</strong> serta tidak dipungut PPh 23.<br>
                3) Faktur Pajak Keluaran Forwarding menggunakan kode transaksi faktur <strong>04</strong> (DPP Nilai Lain).
            </p>
        </div>
    </div>

    <!-- Apple HIG Bento Grid KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- PPN Keluaran 1.1% -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium mb-2">
                <span class="uppercase tracking-wider font-bold text-[10px] text-sky-700">PPN Keluaran (1.1%)</span>
                <span class="p-1.5 rounded-lg bg-sky-50 text-sky-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-slate-900 tracking-tight">
                Rp {{ number_format($totalPpnKeluaran, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>DPP Jasa: Rp {{ number_format($totalDppJasa, 0, ',', '.') }}</span>
                <span class="font-bold text-sky-600">PMK 71/2022</span>
            </div>
        </div>

        <!-- PPN Masukan 11% -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium mb-2">
                <span class="uppercase tracking-wider font-bold text-[10px] text-indigo-700">PPN Masukan (Kredit)</span>
                <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-slate-900 tracking-tight">
                Rp {{ number_format($totalPpnMasukan, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Dari Vendor Pelayaran & TPS</span>
                <span class="font-bold text-indigo-600">11% Normal</span>
            </div>
        </div>

        <!-- Net PPN Kurang/Lebih Bayar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium mb-2">
                <span class="uppercase tracking-wider font-bold text-[10px] {{ $netPpnPayable >= 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                    {{ $netPpnPayable >= 0 ? 'PPN Kurang Bayar' : 'PPN Lebih Bayar' }}
                </span>
                <span class="p-1.5 rounded-lg {{ $netPpnPayable >= 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono {{ $netPpnPayable >= 0 ? 'text-amber-600' : 'text-emerald-600' }} tracking-tight">
                Rp {{ number_format(abs($netPpnPayable), 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Setoran Masa SPT PPN</span>
                <span class="font-bold {{ $netPpnPayable >= 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                    {{ $netPpnPayable >= 0 ? 'Wajib Setor' : 'Kompensasi' }}
                </span>
            </div>
        </div>

        <!-- Estimasi PPh 23 (2%) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium mb-2">
                <span class="uppercase tracking-wider font-bold text-[10px] text-purple-700">Estimasi PPh 23 (2%)</span>
                <span class="p-1.5 rounded-lg bg-purple-50 text-purple-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-slate-900 tracking-tight">
                Rp {{ number_format($totalPph23Withheld, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Potongan dari Klien</span>
                <span class="font-bold text-purple-600">Bukti Potong Unifikasi</span>
            </div>
        </div>
    </div>

    <!-- Kalender Kepatuhan Pajak (Tax Due Date Schedule) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 mb-6">
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>Jadwal Batas Akhir & Kepatuhan Pelaporan Pajak Masa (Tax Calendar)</span>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($taxCalendar as $cal)
                <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800">{{ $cal['title'] }}</span>
                            @if($cal['status'] === 'OVERDUE')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Lewat Waktu</span>
                            @elseif($cal['status'] === 'URGENT')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">H-{{ $cal['days_left'] }} Hari</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Tepat Waktu</span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500 mb-1">Masa Pajak: <strong>{{ $cal['period'] }}</strong></div>
                        <div class="text-xs text-slate-700 font-semibold mb-2">Jatuh Tempo: {{ $cal['due_date'] }}</div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $cal['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Navigation Tabs (Segmented Control Anti-Slop) -->
    <div class="inline-flex p-1 rounded-2xl bg-slate-200/60 border border-slate-200/80 mb-6 text-xs font-semibold gap-1">
        <button type="button" @click="activeTab = 'ppn_keluaran'"
                :class="activeTab === 'ppn_keluaran' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                class="min-h-[40px] sm:min-h-[44px] px-4 py-2 rounded-xl transition-all font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
            Faktur Keluaran (PPN 1.1%) ({{ $invoices->count() }})
        </button>
        <button type="button" @click="activeTab = 'ppn_masukan'"
                :class="activeTab === 'ppn_masukan' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                class="min-h-[40px] sm:min-h-[44px] px-4 py-2 rounded-xl transition-all font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
            Faktur Masukan (Vendor AP) ({{ $vendorBills->count() }})
        </button>
    </div>

    <!-- TAB 1: Faktur Pajak Keluaran (Invoices) -->
    <div x-show="activeTab === 'ppn_keluaran'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-xs font-bold text-slate-900">Daftar Faktur Pajak Keluaran (Kode 04 - DPP Nilai Lain)</h3>
                <p class="text-[11px] text-slate-500">Pemberitahuan PPN Jasa Pengurusan Transportasi & PPJK atas dasar PMK No. 71/PMK.03/2022</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-700">Total PPN: Rp {{ number_format($totalPpnKeluaran, 0, ',', '.') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">No. Invoice & Tanggal</th>
                        <th class="py-3 px-4">Nama Importir / Klien</th>
                        <th class="py-3 px-4 text-right">DPP Jasa (IDR)</th>
                        <th class="py-3 px-4 text-right">PPN 1.1% (IDR)</th>
                        <th class="py-3 px-4 text-right">Talangan Non-PPN (IDR)</th>
                        <th class="py-3 px-4 text-right">Total Tagihan (IDR)</th>
                        <th class="py-3 px-4 text-center">Status Bayar</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        @php
                            $dpp = (float) ($inv->jasa_ppjk_idr + $inv->biaya_trucking_idr);
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-4">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="font-bold text-sky-600 hover:underline font-mono">
                                    {{ $inv->invoice_no }}
                                </a>
                                <div class="text-[11px] text-slate-400">
                                    {{ $inv->tanggal ? $inv->tanggal->format('d M Y') : '-' }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800">{{ $inv->client?->name ?? 'Klien Umum' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">NPWP: {{ $inv->client?->npwp ?? '00.000.000.0-000.000' }}</div>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-slate-800">
                                Rp {{ number_format($dpp, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-sky-700">
                                Rp {{ number_format($inv->ppn_jasa_idr, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-500">
                                Rp {{ number_format($inv->talangan_pabean_idr, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($inv->total_tagihan_idr, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($inv->status === 'PAID')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Lunas</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Belum Lunas</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition-colors">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Tidak ada transaksi invoice terbit pada periode {{ \Carbon\Carbon::create(2026, $month, 1)->translatedFormat('F') }} {{ $year }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: Faktur Pajak Masukan (Vendor AP) -->
    <div x-show="activeTab === 'ppn_masukan'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden" x-cloak>
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-xs font-bold text-slate-900">Daftar Faktur Pajak Masukan (Vendor Pelayaran & Depo Kontainer)</h3>
                <p class="text-[11px] text-slate-500">Kredit Pajak Masukan dari transaksi pengeluaran resmi logistik</p>
            </div>
            <span class="text-xs font-mono font-bold text-indigo-700">Total Kredit: Rp {{ number_format($totalPpnMasukan, 0, ',', '.') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Ref. Tagihan Vendor</th>
                        <th class="py-3 px-4">Tipe Vendor</th>
                        <th class="py-3 px-4">Shipment Terkait</th>
                        <th class="py-3 px-4 text-right">Nilai Tagihan Bruto (IDR)</th>
                        <th class="py-3 px-4 text-right">Estimasi PPN Masukan 11% (IDR)</th>
                        <th class="py-3 px-4 text-center">Status Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vendorBills as $bill)
                        @php
                            $isVat = in_array($bill->vendor_type, ['SHIPPING_LINE', 'TPS']);
                            $ppnVal = $isVat ? round((float) $bill->amount * (11 / 111), 2) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                {{ $bill->ref_no }}
                                <div class="text-[10px] text-slate-400 font-sans">
                                    {{ $bill->created_at ? $bill->created_at->format('d M Y') : '-' }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $bill->vendor_type_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                {{ $bill->shipment?->shipment_number ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-slate-900">
                                Rp {{ number_format($bill->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold {{ $isVat ? 'text-indigo-700' : 'text-slate-400' }}">
                                @if($isVat)
                                    Rp {{ number_format($ppnVal, 0, ',', '.') }}
                                @else
                                    <span class="text-[10px] italic">Non-Kredit / Flat</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($bill->status === 'PAID')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Lunas</span>
                                @elseif($bill->status === 'APPROVED')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800">Disetujui</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Tidak ada tagihan vendor pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
