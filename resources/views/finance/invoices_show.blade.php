@extends('layouts.app')

@section('title', 'Detail Invoice ' . ($invoice->invoice_number ?? $invoice->invoice_no))

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" x-data="{ showPayModal: false }">

    <!-- Breadcrumb -->
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Finance', 'url' => route('finance.index')],
        ['label' => 'Daftar Invoice', 'url' => route('finance.invoices.index')],
        ['label' => $invoice->invoice_number ?? $invoice->invoice_no]
    ]" />

    <!-- Apple HIG Module Header Bar -->
    <div class="mb-6 rounded-[16px] bg-white dark:bg-[#1C1C1E] p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.invoices.index') }}" 
               class="btn-apple-gray !p-2 rounded-xl" 
               title="Kembali ke Daftar Tagihan">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold font-apple-display tracking-tight text-slate-900 dark:text-white">
                        {{ $invoice->invoice_number ?? $invoice->invoice_no }}
                    </h1>
                    @if($invoice->status === 'PAID')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span> LUNAS (PAID)
                        </span>
                    @elseif($invoice->jatuh_tempo && $invoice->jatuh_tempo->isPast())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> OVERDUE
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> MENUNGGU PEMBAYARAN
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Tagihan resmi Freight Forwarding & Jasa PPJK Kepabeanan (P19)
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2">
            <a href="{{ route('pdf.invoice', $invoice->id) }}" target="_blank"
               class="btn-apple-gray">
                <i data-lucide="printer" class="w-4 h-4 text-sky-600 dark:text-sky-400"></i>
                <span>Cetak Invoice (PDF)</span>
            </a>

            @if($invoice->status !== 'PAID')
                @can('pay', $invoice)
                <button type="button" @click="showPayModal = true"
                        class="btn-apple-filled !bg-emerald-600 hover:!bg-emerald-700 dark:!bg-emerald-600 text-white">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Catat Pelunasan</span>
                </button>
                @endcan
            @endif
        </div>
    </div>

    <!-- Bento KPI Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 text-xs">
        <!-- Total Tagihan -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white p-5 rounded-2xl shadow-sm border border-slate-700/50">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Total Nilai Tagihan (Grand Total)</span>
            <span class="text-2xl font-black font-mono tabular-nums text-white">Rp {{ number_format($invoice->total_tagihan_idr, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-300 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Termasuk talangan pabean & PPN 1.1%</span>
            </div>
        </div>

        <!-- Talangan Pabean -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Talangan Bea Cukai (Reimbursable)</span>
            <span class="text-2xl font-black font-mono tabular-nums text-amber-600 dark:text-amber-400">Rp {{ number_format($invoice->talangan_pabean_idr, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                Billing MPN G3 (Non-PPN Jasa)
            </div>
        </div>

        <!-- Biaya Pelayaran & DO -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Pelayaran & Terminal DO</span>
            <span class="text-2xl font-black font-mono tabular-nums text-indigo-600 dark:text-indigo-400">Rp {{ number_format($invoice->biaya_pelayaran_idr, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                Penebusan DO & THC Pelabuhan
            </div>
        </div>

        <!-- Jasa PPJK & Trucking -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Jasa PPJK & Angkutan Truk</span>
            <span class="text-2xl font-black font-mono tabular-nums text-slate-900 dark:text-white">Rp {{ number_format($invoice->jasa_ppjk_idr + $invoice->biaya_trucking_idr, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-sky-700 dark:text-sky-400 font-medium">
                PPN Jasa: Rp {{ number_format($invoice->ppn_jasa_idr, 0, ',', '.') }} (1.1%)
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Detailed Breakdown Table -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-sm text-slate-900 dark:text-white">Rincian Pos Biaya & Perpajakan (P19)</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kalkulasi transparan komponen talangan bea cukai dan pendapatan jasa</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-400 font-mono text-[11px] font-bold border border-sky-100 dark:border-sky-800">
                        PMK No. 71/2022
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-[#2C2C2E] text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-100 dark:border-white/10">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Deskripsi Komponen Tagihan</th>
                                <th class="py-3 px-4 text-center">Kategori</th>
                                <th class="py-3 px-4 text-right">Jumlah (IDR)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            <!-- Kategori A: Talangan Pabean -->
                            <tr class="bg-slate-100/60 dark:bg-[#252528] font-bold text-slate-700 dark:text-slate-200">
                                <td colspan="4" class="py-2.5 px-4 text-[11px]">
                                    A. PUNGUTAN RESMI PABEAN (REIMBURSABLE / TALANGAN BEBAS PPN JASA)
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 text-center text-slate-400">1</td>
                                <td class="py-2.5 px-4">
                                    <div class="font-medium text-slate-900 dark:text-white">Bea Masuk & Pajak Impor (Billing MPN G3)</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">Kode Billing: {{ $invoice->shipment->ceisaDocument->kode_billing ?? '028192819201928' }}</div>
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">Talangan</span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono font-semibold text-slate-800 dark:text-slate-200 tabular-nums">
                                    Rp {{ number_format($invoice->talangan_pabean_idr, 0, ',', '.') }}
                                </td>
                            </tr>

                            <!-- Kategori B: Pelayaran -->
                            <tr class="bg-slate-100/60 dark:bg-[#252528] font-bold text-slate-700 dark:text-slate-200">
                                <td colspan="4" class="py-2.5 px-4 text-[11px]">
                                    B. BIAYA PELAYARAN & TERMINAL PELABUHAN (SHIPPING LINE CHARGES)
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 text-center text-slate-400">2</td>
                                <td class="py-2.5 px-4">
                                    <div class="font-medium text-slate-900 dark:text-white">Penebusan Delivery Order (DO), THC, & Lift On/Off</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400">Shipping Line: {{ $invoice->shipment->shippingVendor->name ?? $invoice->shipment->nama_kapal ?? 'Pelayaran Resmi' }}</div>
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800">Pelayaran</span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono font-semibold text-slate-800 dark:text-slate-200 tabular-nums">
                                    Rp {{ number_format($invoice->biaya_pelayaran_idr, 0, ',', '.') }}
                                </td>
                            </tr>

                            <!-- Kategori C: Jasa PPJK & Trucking -->
                            <tr class="bg-slate-100/60 dark:bg-[#252528] font-bold text-slate-700 dark:text-slate-200">
                                <td colspan="4" class="py-2.5 px-4 text-[11px]">
                                    C. JASA PENGURUSAN PPJK & TRUCKING (FORWARDING SERVICES - KENA PPN)
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 text-center text-slate-400">3</td>
                                <td class="py-2.5 px-4">
                                    <div class="font-medium text-slate-900 dark:text-white">Customs Clearance, EDI Pabean & Penanganan Dokumen</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400">Handling Fee PIB BC 2.0 / PEB BC 3.0</div>
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-800">Jasa PPJK</span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono font-semibold text-slate-800 dark:text-slate-200 tabular-nums">
                                    Rp {{ number_format($invoice->jasa_ppjk_idr, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 text-center text-slate-400">4</td>
                                <td class="py-2.5 px-4">
                                    <div class="font-medium text-slate-900 dark:text-white">Trucking Inland Transport Trailer</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400">Pelabuhan Tanjung Priok &rarr; Gudang Destinasi Importir</div>
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-800">Transport</span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono font-semibold text-slate-800 dark:text-slate-200 tabular-nums">
                                    Rp {{ number_format($invoice->biaya_trucking_idr, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 dark:bg-[#252528] divide-y divide-slate-100 dark:divide-white/5 font-semibold border-t border-slate-200 dark:border-white/10">
                            <tr>
                                <td colspan="3" class="py-2 px-4 text-right text-slate-600 dark:text-slate-300">Subtotal Biaya (A + B + C):</td>
                                <td class="py-2 px-4 text-right font-mono font-bold text-slate-900 dark:text-white tabular-nums">
                                    Rp {{ number_format($invoice->subtotal_idr, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="py-2 px-4 text-right text-sky-700 dark:text-sky-400">
                                    PPN Jasa Forwarding (1.1% atas Jasa C sesuai PMK 71/2022):
                                </td>
                                <td class="py-2 px-4 text-right font-mono font-bold text-sky-700 dark:text-sky-400 tabular-nums">
                                    Rp {{ number_format($invoice->ppn_jasa_idr, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="bg-slate-900 text-white font-bold text-sm">
                                <td colspan="3" class="py-3 px-4 text-right">TOTAL TAGIHAN INVOICE (IDR):</td>
                                <td class="py-3 px-4 text-right font-mono text-emerald-400 tabular-nums">
                                    Rp {{ number_format($invoice->total_tagihan_idr, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Petunjuk Rekening Pembayaran Resmi -->
            <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs text-xs">
                <h3 class="font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-4 h-4 text-slate-600 dark:text-slate-400"></i>
                    <span>Instruksi Pembayaran Resmi (Bank Transfer)</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-slate-600 dark:text-slate-300">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#2C2C2E] border border-slate-100 dark:border-white/5">
                        <div class="font-bold text-slate-900 dark:text-white">BANK MANDIRI (KCP Tanjung Priok)</div>
                        <div class="text-base font-mono font-bold text-slate-800 dark:text-white my-1 tabular-nums">120-00-1928374-1</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">Atas Nama: <strong>PT AGUNG TRANS SOLUSINDO</strong></div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#2C2C2E] border border-slate-100 dark:border-white/5">
                        <div class="font-bold text-slate-900 dark:text-white">BANK CENTRAL ASIA (KCU Enggano)</div>
                        <div class="text-base font-mono font-bold text-slate-800 dark:text-white my-1 tabular-nums">084-3928172</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">Atas Nama: <strong>PT AGUNG TRANS SOLUSINDO</strong></div>
                    </div>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-3 leading-relaxed">
                    *Harap sertakan nomor invoice <code class="text-sky-600 dark:text-sky-400 font-mono">{{ $invoice->invoice_number ?? $invoice->invoice_no }}</code> pada berita transfer dan konfirmasikan bukti pembayaran ke tim Finance.
                </p>
            </div>
        </div>

        <!-- Right 1 Col: Metadata & Action Cards -->
        <div class="space-y-6">

            <!-- Card Status Pembayaran -->
            <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs text-xs">
                <h3 class="font-bold text-slate-900 dark:text-white mb-3 pb-2 border-b border-slate-100 dark:border-white/10">Status Pembayaran Tagihan</h3>
                @if($invoice->status === 'PAID')
                    <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 mb-3">
                        <div class="font-bold text-sm mb-1 flex items-center gap-1.5">
                            <i data-lucide="check-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                            <span>Lunas Terverifikasi</span>
                        </div>
                        <div class="text-[11px] text-slate-600 dark:text-slate-300 space-y-1 mt-2">
                            <div>Tanggal Pelunasan: <strong class="tabular-nums">{{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : '-' }}</strong></div>
                            <div>Keterangan: <em>{{ $invoice->catatan_pembayaran ?? 'Pelunasan transfer bank' }}</em></div>
                        </div>
                    </div>
                @else
                    <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 mb-3">
                        <div class="font-bold mb-1">Tagihan Belum Lunas</div>
                        <p class="text-[11px] text-amber-700 dark:text-amber-400 leading-relaxed">
                            Invoice jatuh tempo pada <strong class="tabular-nums">{{ $invoice->jatuh_tempo ? $invoice->jatuh_tempo->format('d/m/Y') : '-' }}</strong>.
                        </p>
                    </div>

                    @can('pay', $invoice)
                    <button type="button" @click="showPayModal = true"
                            class="w-full btn-apple-filled !bg-emerald-600 hover:!bg-emerald-700 justify-center">
                        <span>Catat Pelunasan Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                    @endcan
                @endif
            </div>

            <!-- Card Rujukan Klien -->
            <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs text-xs">
                <h3 class="font-bold text-slate-900 dark:text-white mb-3 pb-2 border-b border-slate-100 dark:border-white/10">Informasi Klien (Importir)</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Nama Perusahaan</span>
                        <span class="font-bold text-slate-800 dark:text-white text-sm">{{ $invoice->client->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">NPWP Importir</span>
                        <span class="font-mono text-slate-700 dark:text-slate-300 tabular-nums">{{ $invoice->client->npwp ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Kontak PIC</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ $invoice->client->pic_name ?? '-' }} ({{ $invoice->client->phone ?? '-' }})</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Alamat</span>
                        <span class="text-slate-600 dark:text-slate-400 leading-relaxed">{{ $invoice->client->address ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card Rujukan Shipment -->
            <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs text-xs">
                <h3 class="font-bold text-slate-900 dark:text-white mb-3 pb-2 border-b border-slate-100 dark:border-white/10">Rujukan Operasional Pengiriman</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Nomor Proyek (Job Order)</span>
                        <a href="{{ $invoice->shipment ? route('core.document.show', $invoice->shipment->id) : '#' }}" 
                           class="font-mono font-bold text-[#007AFF] hover:underline">
                            {{ $invoice->shipment->project_no ?? $invoice->shipment->shipment_number ?? '-' }}
                        </a>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Nomor Bill of Lading (B/L)</span>
                        <span class="font-mono text-slate-800 dark:text-slate-200">{{ $invoice->shipment->nomor_bl_awb ?? $invoice->shipment->bl_number ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Kapal & Voyage</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ $invoice->shipment->nama_kapal ?? '-' }} / {{ $invoice->shipment->voyage_no ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Rute Pelabuhan</span>
                        <span class="text-slate-600 dark:text-slate-400">{{ $invoice->shipment->pelabuhan_muat ?? '-' }} &rarr; {{ $invoice->shipment->pelabuhan_bongkar ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Muatan Petikemas</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ $invoice->shipment->nomor_kontainer ?? '1x40HC' }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Modal Catat Pelunasan Pembayaran -->
    <div x-show="showPayModal" 
         class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="pay-modal-title"
         x-cloak>
        <!-- Independent Backdrop -->
        <div x-show="showPayModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md"
             @click="showPayModal = false"
             aria-hidden="true"></div>

        <!-- Dialog Shell -->
        <div x-show="showPayModal"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative z-10 bg-white dark:bg-[#1C1C1E] w-full rounded-t-[28px] sm:rounded-3xl max-w-lg max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border border-slate-200/80 dark:border-white/10 text-xs">
            
            <!-- Mobile Grab Handle -->
            <div class="sm:hidden w-12 h-1.5 bg-slate-200 dark:bg-white/20 rounded-full mx-auto mt-3 mb-1"></div>

            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-white/10">
                <div>
                    <h3 id="pay-modal-title" class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Pencatatan Pelunasan Invoice</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Konfirmasi penerimaan pembayaran piutang klien (AR)</p>
                </div>
                <button type="button" 
                        @click="showPayModal = false" 
                        class="btn-apple-gray !p-2 rounded-full"
                        aria-label="Tutup modal">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('finance.invoices.pay', $invoice->id) }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Invoice</label>
                            <input type="text" value="{{ $invoice->invoice_number ?? $invoice->invoice_no }}" disabled
                                   class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-600 dark:text-slate-300 font-mono text-xs cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Tagihan (IDR)</label>
                            <input type="text" value="Rp {{ number_format($invoice->total_tagihan_idr, 0, ',', '.') }}" disabled
                                   class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white font-mono font-bold text-xs cursor-not-allowed tabular-nums">
                        </div>
                    </div>

                    <div>
                        <label for="payment_date" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Pembayaran *</label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all">
                    </div>

                    <div>
                        <label for="account_id" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Rekening Bank Tujuan Penerimaan *</label>
                        <select name="account_id" id="account_id" required
                                class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all font-medium">
                            @if(isset($bankAccounts) && $bankAccounts->isNotEmpty())
                                @foreach($bankAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->bank }} - {{ $acc->account_no }})</option>
                                @endforeach
                            @else
                                <option value="">Bank Central Asia (BCA) Operasional</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="notes" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Referensi / Catatan</label>
                        <input type="text" name="notes" id="notes" placeholder="Contoh: Ref Transfer BCA-91823719"
                               class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all">
                    </div>
                </div>

                <!-- Sticky Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-[#252528] border-t border-slate-100 dark:border-white/10 flex items-center justify-end gap-3">
                    <button type="button" @click="showPayModal = false"
                            class="btn-apple-gray">
                        Batal
                    </button>
                    <button type="submit"
                            class="btn-apple-filled !bg-emerald-600 hover:!bg-emerald-700">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Konfirmasi Pelunasan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
