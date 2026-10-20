@extends('layouts.app')

@section('title', 'Perpajakan Forwarding & PPJK - PPN 1.1% & PPh 23')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12" x-data="{ activeTab: 'ppn_keluaran' }">

    <!-- Breadcrumb -->
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Finance', 'url' => route('finance.index')],
        ['label' => 'Perpajakan Forwarding & PPJK']
    ]" />

    <!-- Apple HIG Module Header Bar -->
    <div class="mb-6 rounded-[16px] bg-white dark:bg-[#1C1C1E] p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl sm:text-2xl font-bold font-apple-display tracking-tight text-slate-900 dark:text-white">
                    Kepatuhan Pajak & Rekapitulasi PPN / PPh 23
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 dark:bg-sky-950/50 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                    PMK 71/2022
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Standar PMK No. 71/PMK.03/2022 (DPP Nilai Lain PPN Efektif 1.1%), Rekap Faktur Masukan, dan Estimasi PPh 23
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Filter Bulan / Tahun -->
            <form method="GET" action="{{ route('finance.tax.index') }}" class="flex items-center gap-2 bg-slate-50 dark:bg-[#2C2C2E] px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 shadow-xs text-xs">
                <select name="month" class="bg-transparent border-0 font-medium text-slate-700 dark:text-white focus:ring-0 cursor-pointer text-xs" onchange="this.form.submit()">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }} class="dark:bg-[#2C2C2E]">
                            {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <span class="text-slate-300 dark:text-white/20">|</span>
                <select name="year" class="bg-transparent border-0 font-medium text-slate-700 dark:text-white focus:ring-0 cursor-pointer text-xs" onchange="this.form.submit()">
                    @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }} class="dark:bg-[#2C2C2E]">{{ $y }}</option>
                    @endfor
                </select>
            </form>

            <!-- Export e-Faktur CSV -->
            <a href="{{ route('finance.tax.export', ['year' => $year, 'month' => $month]) }}" 
               class="btn-apple-filled">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Export e-Faktur (CSV)</span>
            </a>
        </div>
    </div>

    <!-- Alert Edukasi Regulasi PMK 71/2022 -->
    <div class="mb-6 p-4 rounded-2xl bg-sky-50 dark:bg-sky-950/40 border border-sky-100 dark:border-sky-800/60 flex items-start gap-3 text-xs text-sky-900 dark:text-sky-300">
        <i data-lucide="info" class="w-5 h-5 text-[#007AFF] flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Perpajakan Forwarding & PPJK Sesuai Regulasi Indonesia:</span>
            <p class="text-sky-800 dark:text-sky-400 leading-relaxed">
                1) <strong>PPN Jasa Pengurusan Transportasi (Freight Forwarding)</strong> dikenakan PPN dengan <em>DPP Nilai Lain</em> sebesar 10% dari jumlah tagihan jasa atau efektif <strong>1.1%</strong> (PMK No. 71/PMK.03/2022).<br>
                2) <strong>Biaya Talangan Pabean (Bea Masuk & Pajak Impor MPN G3)</strong> merupakan <em>reimbursable pass-through outlay</em> dan <strong>bukan objek PPN</strong> serta tidak dipungut PPh 23.<br>
                3) Faktur Pajak Keluaran Forwarding menggunakan kode transaksi faktur <strong>04</strong> (DPP Nilai Lain).
            </p>
        </div>
    </div>

    <!-- Apple HIG Bento Grid KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- PPN Keluaran 1.1% -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs relative overflow-hidden group hover:border-slate-300 dark:hover:border-white/20 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-medium mb-2">
                <span class="uppercase tracking-wider font-bold text-[10px] text-sky-700 dark:text-sky-400">PPN Keluaran (1.1%)</span>
                <span class="p-1.5 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-[#007AFF]">
                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono tabular-nums text-slate-900 dark:text-white tracking-tight">
                Rp {{ number_format($totalPpnKeluaran, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>DPP Jasa: Rp {{ number_format($totalDppJasa, 0, ',', '.') }}</span>
                <span class="font-bold text-[#007AFF]">PMK 71/2022</span>
            </div>
        </div>

        <!-- PPN Masukan 11% -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs relative overflow-hidden group hover:border-slate-300 dark:hover:border-white/20 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-medium mb-2">
                <span class="uppercase tracking-wider font-bold text-[10px] text-indigo-700 dark:text-indigo-400">PPN Masukan (Kredit)</span>
                <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                    <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono tabular-nums text-slate-900 dark:text-white tracking-tight">
                Rp {{ number_format($totalPpnMasukan, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Dari Vendor Pelayaran & TPS</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400">11% Normal</span>
            </div>
        </div>

        <!-- Net PPN Kurang/Lebih Bayar -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs relative overflow-hidden group hover:border-slate-300 dark:hover:border-white/20 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-medium mb-2">
                <span class="uppercase tracking-wider font-bold text-[10px] {{ $netPpnPayable >= 0 ? 'text-amber-700 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                    {{ $netPpnPayable >= 0 ? 'PPN Kurang Bayar' : 'PPN Lebih Bayar' }}
                </span>
                <span class="p-1.5 rounded-lg {{ $netPpnPayable >= 0 ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-600' : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600' }}">
                    <i data-lucide="scale" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono tabular-nums {{ $netPpnPayable >= 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }} tracking-tight">
                Rp {{ number_format(abs($netPpnPayable), 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Setoran Masa SPT PPN</span>
                <span class="font-bold {{ $netPpnPayable >= 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                    {{ $netPpnPayable >= 0 ? 'Wajib Setor' : 'Kompensasi' }}
                </span>
            </div>
        </div>

        <!-- Estimasi PPh 23 (2%) -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs relative overflow-hidden group hover:border-slate-300 dark:hover:border-white/20 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-medium mb-2">
                <span class="uppercase tracking-wider font-bold text-[10px] text-purple-700 dark:text-purple-400">Estimasi PPh 23 (2%)</span>
                <span class="p-1.5 rounded-lg bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono tabular-nums text-slate-900 dark:text-white tracking-tight">
                Rp {{ number_format($totalPph23Withheld, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Potongan dari Klien</span>
                <span class="font-bold text-purple-600 dark:text-purple-400">Bukti Potong Unifikasi</span>
            </div>
        </div>
    </div>

    <!-- Kalender Kepatuhan Pajak (Tax Due Date Schedule) -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs p-5 mb-6">
        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
            <i data-lucide="calendar" class="w-4 h-4 text-slate-600 dark:text-slate-400"></i>
            <span>Jadwal Batas Akhir & Kepatuhan Pelaporan Pajak Masa (Tax Calendar)</span>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($taxCalendar as $cal)
                <div class="p-4 rounded-xl border border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-[#2C2C2E]/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800 dark:text-white">{{ $cal['title'] }}</span>
                            @if($cal['status'] === 'OVERDUE')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">Lewat Waktu</span>
                            @elseif($cal['status'] === 'URGENT')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 animate-pulse border border-amber-200 dark:border-amber-800">H-{{ $cal['days_left'] }} Hari</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Tepat Waktu</span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Masa Pajak: <strong>{{ $cal['period'] }}</strong></div>
                        <div class="text-xs text-slate-700 dark:text-slate-300 font-semibold mb-2">Jatuh Tempo: {{ $cal['due_date'] }}</div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ $cal['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Navigation Tabs (Segmented Control Anti-Slop) -->
    <div class="inline-flex p-1 rounded-xl bg-slate-200/70 dark:bg-white/10 mb-6 text-xs font-semibold">
        <button type="button" @click="activeTab = 'ppn_keluaran'"
                :class="activeTab === 'ppn_keluaran' ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="py-2 px-4 rounded-lg transition-all font-bold">
            Faktur Keluaran (PPN 1.1%) ({{ $invoices->count() }})
        </button>
        <button type="button" @click="activeTab = 'ppn_masukan'"
                :class="activeTab === 'ppn_masukan' ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="py-2 px-4 rounded-lg transition-all font-bold">
            Faktur Masukan (Vendor AP) ({{ $vendorBills->count() }})
        </button>
    </div>

    <!-- TAB 1: Faktur Pajak Keluaran (Invoices) -->
    <div x-show="activeTab === 'ppn_keluaran'" class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
            <div>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white">Daftar Faktur Pajak Keluaran (Kode 04 - DPP Nilai Lain)</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Pemberitahuan PPN Jasa Pengurusan Transportasi & PPJK atas dasar PMK No. 71/PMK.03/2022</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 tabular-nums">Total PPN: Rp {{ number_format($totalPpnKeluaran, 0, ',', '.') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-[#2C2C2E] text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-100 dark:border-white/10">
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
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($invoices as $inv)
                        @php
                            $dpp = (float) ($inv->jasa_ppjk_idr + $inv->biaya_trucking_idr);
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="font-bold text-[#007AFF] hover:underline font-mono">
                                    {{ $inv->invoice_no }}
                                </a>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 tabular-nums">
                                    {{ $inv->tanggal ? $inv->tanggal->format('d M Y') : '-' }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800 dark:text-white">{{ $inv->client?->name ?? 'Klien Umum' }}</div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono tabular-nums">NPWP: {{ $inv->client?->npwp ?? '00.000.000.0-000.000' }}</div>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-slate-800 dark:text-slate-200 tabular-nums">
                                Rp {{ number_format($dpp, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-sky-700 dark:text-sky-400 tabular-nums">
                                Rp {{ number_format($inv->ppn_jasa_idr, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-500 dark:text-slate-400 tabular-nums">
                                Rp {{ number_format($inv->talangan_pabean_idr, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white tabular-nums">
                                Rp {{ number_format($inv->total_tagihan_idr, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($inv->status === 'PAID')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Lunas</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Belum Lunas</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="btn-apple-gray !py-1 !px-2.5 !text-[11px]">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                Tidak ada transaksi invoice terbit pada periode {{ \Carbon\Carbon::create(2026, $month, 1)->translatedFormat('F') }} {{ $year }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: Faktur Pajak Masukan (Vendor AP) -->
    <div x-show="activeTab === 'ppn_masukan'" class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs overflow-hidden" x-cloak>
        <div class="p-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
            <div>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white">Daftar Faktur Pajak Masukan (Vendor Pelayaran & Depo Kontainer)</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Kredit Pajak Masukan dari transaksi pengeluaran resmi logistik</p>
            </div>
            <span class="text-xs font-mono font-bold text-indigo-700 dark:text-indigo-400 tabular-nums">Total Kredit: Rp {{ number_format($totalPpnMasukan, 0, ',', '.') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-[#2C2C2E] text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-100 dark:border-white/10">
                    <tr>
                        <th class="py-3 px-4">Ref. Tagihan Vendor</th>
                        <th class="py-3 px-4">Tipe Vendor</th>
                        <th class="py-3 px-4">Shipment Terkait</th>
                        <th class="py-3 px-4 text-right">Nilai Tagihan Bruto (IDR)</th>
                        <th class="py-3 px-4 text-right">Estimasi PPN Masukan 11% (IDR)</th>
                        <th class="py-3 px-4 text-center">Status Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($vendorBills as $bill)
                        @php
                            $isVat = in_array($bill->vendor_type, ['SHIPPING_LINE', 'TPS']);
                            $ppnVal = $isVat ? round((float) $bill->amount * (11 / 111), 2) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800 dark:text-white">
                                {{ $bill->ref_no }}
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-sans tabular-nums">
                                    {{ $bill->created_at ? $bill->created_at->format('d M Y') : '-' }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300">
                                    {{ $bill->vendor_type_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                                {{ $bill->shipment?->shipment_number ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-slate-900 dark:text-white tabular-nums">
                                Rp {{ number_format($bill->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold {{ $isVat ? 'text-indigo-700 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500' }} tabular-nums">
                                @if($isVat)
                                    Rp {{ number_format($ppnVal, 0, ',', '.') }}
                                @else
                                    <span class="text-[10px] italic">Non-Kredit / Flat</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($bill->status === 'PAID')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Lunas</span>
                                @elseif($bill->status === 'APPROVED')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 dark:bg-sky-950/50 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-800">Disetujui</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 dark:text-slate-500">
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
