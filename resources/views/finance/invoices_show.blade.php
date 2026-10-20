@extends('layouts.app')

@section('title', 'Detail Invoice ' . ($invoice->invoice_number ?? $invoice->invoice_no))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-12" x-data="{ showPayModal: false }">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / 
                <a href="{{ route('finance.invoices.index') }}" class="hover:underline">Finance</a> / 
                <span class="text-slate-800 font-semibold">{{ $invoice->invoice_number ?? $invoice->invoice_no }}</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('finance.invoices.index') }}" 
                   class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" 
                   title="Kembali ke Daftar Tagihan">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-slate-900">
                    {{ $invoice->invoice_number ?? $invoice->invoice_no }}
                </h1>
                
                @if($invoice->status === 'PAID')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> LUNAS (PAID)
                    </span>
                @elseif($invoice->jatuh_tempo && $invoice->jatuh_tempo->isPast())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> JATUH TEMPO (OVERDUE)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> MENUNGGU PEMBAYARAN
                    </span>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2">
            <a href="{{ route('pdf.invoice', $invoice->id) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold shadow-xs transition-all">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Invoice (PDF)</span>
            </a>

            @if($invoice->status !== 'PAID')
                @can('pay', $invoice)
                <button type="button" @click="showPayModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm shadow-emerald-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Catat Pelunasan</span>
                </button>
                @endcan
            @endif
        </div>
    </div>

    <!-- Alert Success Notification -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs text-emerald-800">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Bento KPI Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 text-xs">
        <!-- Total Tagihan -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white p-5 rounded-2xl shadow-sm border border-slate-700/50">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Total Nilai Tagihan (Grand Total)</span>
            <span class="text-2xl font-black text-white">Rp {{ number_format($invoice->total_tagihan_idr, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-300 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Termasuk talangan pabean & PPN 1.1%</span>
            </div>
        </div>

        <!-- Talangan Pabean -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Talangan Bea Cukai (Reimbursable)</span>
            <span class="text-2xl font-black text-amber-600">Rp {{ number_format($invoice->talangan_pabean_idr, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-500">
                Billing MPN G3 (Non-PPN Jasa)
            </div>
        </div>

        <!-- Biaya Pelayaran & DO -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Pelayaran & Terminal DO</span>
            <span class="text-2xl font-black text-indigo-600">Rp {{ number_format($invoice->biaya_pelayaran_idr, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-500">
                Penebusan DO & THC Pelabuhan
            </div>
        </div>

        <!-- Jasa PPJK & Trucking -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Jasa PPJK & Angkutan Truk</span>
            <span class="text-2xl font-black text-slate-900">Rp {{ number_format($invoice->jasa_ppjk_idr + $invoice->biaya_trucking_idr, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-sky-700 font-medium">
                PPN Jasa: Rp {{ number_format($invoice->ppn_jasa_idr, 0, ',', '.') }} (1.1%)
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Detailed Breakdown Table -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-sm text-slate-900">Rincian Pos Biaya & Perpajakan (P19)</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Kalkulasi transparan komponen talangan bea cukai dan pendapatan jasa</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 font-mono text-[11px] font-bold">
                        PMK No. 71/2022
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Deskripsi Komponen Tagihan</th>
                                <th class="py-3 px-4 text-center">Kategori</th>
                                <th class="py-3 px-4 text-right">Jumlah (IDR)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- Kategori A: Talangan Pabean -->
                            <tr class="bg-slate-100/60 font-bold text-slate-700">
                                <td colspan="4" class="py-2.5 px-4 text-[11px]">
                                    A. PUNGUTAN RESMI PABEAN (REIMBURSABLE / TALANGAN BEBAS PPN JASA)
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 text-center text-slate-400">1</td>
                                <td class="py-2.5 px-4">
                                    <div class="font-medium text-slate-900">Bea Masuk & Pajak Impor (Billing MPN G3)</div>
                                    <div class="text-[10px] text-slate-500 font-mono">Kode Billing: {{ $invoice->shipment->ceisaDocument->kode_billing ?? '028192819201928' }}</div>
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">Talangan</span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono font-semibold text-slate-800">
                                    Rp {{ number_format($invoice->talangan_pabean_idr, 0, ',', '.') }}
                                </td>
                            </tr>

                            <!-- Kategori B: Pelayaran -->
                            <tr class="bg-slate-100/60 font-bold text-slate-700">
                                <td colspan="4" class="py-2.5 px-4 text-[11px]">
                                    B. BIAYA PELAYARAN & TERMINAL PELABUHAN (SHIPPING LINE CHARGES)
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 text-center text-slate-400">2</td>
                                <td class="py-2.5 px-4">
                                    <div class="font-medium text-slate-900">Penebusan Delivery Order (DO), THC, & Lift On/Off</div>
                                    <div class="text-[10px] text-slate-500">Shipping Line: {{ $invoice->shipment->shippingVendor->name ?? $invoice->shipment->nama_kapal ?? 'Pelayaran Resmi' }}</div>
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">Pelayaran</span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono font-semibold text-slate-800">
                                    Rp {{ number_format($invoice->biaya_pelayaran_idr, 0, ',', '.') }}
                                </td>
                            </tr>

                            <!-- Kategori C: Jasa PPJK & Trucking -->
                            <tr class="bg-slate-100/60 font-bold text-slate-700">
                                <td colspan="4" class="py-2.5 px-4 text-[11px]">
                                    C. JASA PENGURUSAN PPJK & TRUCKING (FORWARDING SERVICES - KENA PPN)
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 text-center text-slate-400">3</td>
                                <td class="py-2.5 px-4">
                                    <div class="font-medium text-slate-900">Customs Clearance, EDI Pabean & Penanganan Dokumen</div>
                                    <div class="text-[10px] text-slate-500">Handling Fee PIB BC 2.0 / PEB BC 3.0</div>
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-sky-50 text-sky-700 border border-sky-200">Jasa PPJK</span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono font-semibold text-slate-800">
                                    Rp {{ number_format($invoice->jasa_ppjk_idr, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 text-center text-slate-400">4</td>
                                <td class="py-2.5 px-4">
                                    <div class="font-medium text-slate-900">Trucking Inland Transport Trailer</div>
                                    <div class="text-[10px] text-slate-500">Pelabuhan Tanjung Priok &rarr; Gudang Destinasi Importir</div>
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-sky-50 text-sky-700 border border-sky-200">Transport</span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono font-semibold text-slate-800">
                                    Rp {{ number_format($invoice->biaya_trucking_idr, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 divide-y divide-slate-100 font-semibold border-t border-slate-200">
                            <tr>
                                <td colspan="3" class="py-2 px-4 text-right text-slate-600">Subtotal Biaya (A + B + C):</td>
                                <td class="py-2 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($invoice->subtotal_idr, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="py-2 px-4 text-right text-sky-700">
                                    PPN Jasa Forwarding (1.1% atas Jasa C sesuai PMK 71/2022):
                                </td>
                                <td class="py-2 px-4 text-right font-mono font-bold text-sky-700">
                                    Rp {{ number_format($invoice->ppn_jasa_idr, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="bg-slate-900 text-white font-bold text-sm">
                                <td colspan="3" class="py-3 px-4 text-right">TOTAL TAGIHAN INVOICE (IDR):</td>
                                <td class="py-3 px-4 text-right font-mono text-emerald-400">
                                    Rp {{ number_format($invoice->total_tagihan_idr, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Petunjuk Rekening Pembayaran Resmi -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs text-xs">
                <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span>Instruksi Pembayaran Resmi (Bank Transfer)</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-slate-600">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="font-bold text-slate-900">BANK MANDIRI (KCP Tanjung Priok)</div>
                        <div class="text-base font-mono font-bold text-slate-800 my-1">120-00-1928374-1</div>
                        <div class="text-[11px] text-slate-500">Atas Nama: <strong>PT AGUNG TRANS SOLUSINDO</strong></div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="font-bold text-slate-900">BANK CENTRAL ASIA (KCU Enggano)</div>
                        <div class="text-base font-mono font-bold text-slate-800 my-1">084-3928172</div>
                        <div class="text-[11px] text-slate-500">Atas Nama: <strong>PT AGUNG TRANS SOLUSINDO</strong></div>
                    </div>
                </div>
                <p class="text-[11px] text-slate-400 mt-3 leading-relaxed">
                    *Harap sertakan nomor invoice <code>{{ $invoice->invoice_number ?? $invoice->invoice_no }}</code> pada berita transfer dan konfirmasikan bukti pembayaran ke tim Finance.
                </p>
            </div>
        </div>

        <!-- Right 1 Col: Metadata & Action Cards -->
        <div class="space-y-6">

            <!-- Card Status Pembayaran -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs text-xs">
                <h3 class="font-bold text-slate-900 mb-3 pb-2 border-b border-slate-100">Status Pembayaran Tagihan</h3>
                @if($invoice->status === 'PAID')
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 mb-3">
                        <div class="font-bold text-sm mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Lunas Terverifikasi</span>
                        </div>
                        <div class="text-[11px] text-slate-600 space-y-1 mt-2">
                            <div>Tanggal Pelunasan: <strong>{{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : '-' }}</strong></div>
                            <div>Keterangan: <em>{{ $invoice->catatan_pembayaran ?? 'Pelunasan transfer bank' }}</em></div>
                        </div>
                    </div>
                @else
                    <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 mb-3">
                        <div class="font-bold mb-1">Tagihan Belum Lunas</div>
                        <p class="text-[11px] text-amber-700 leading-relaxed">
                            Invoice jatuh tempo pada <strong>{{ $invoice->jatuh_tempo ? $invoice->jatuh_tempo->format('d/m/Y') : '-' }}</strong>.
                        </p>
                    </div>

                    @can('pay', $invoice)
                    <button type="button" @click="showPayModal = true"
                            class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-center block shadow-xs transition-colors">
                        Catat Pelunasan Sekarang &rarr;
                    </button>
                    @endcan
                @endif
            </div>

            <!-- Card Rujukan Klien -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs text-xs">
                <h3 class="font-bold text-slate-900 mb-3 pb-2 border-b border-slate-100">Informasi Klien (Importir)</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Perusahaan</span>
                        <span class="font-bold text-slate-800 text-sm">{{ $invoice->client->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">NPWP Importir</span>
                        <span class="font-mono text-slate-700">{{ $invoice->client->npwp ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Kontak PIC</span>
                        <span class="text-slate-700">{{ $invoice->client->pic_name ?? '-' }} ({{ $invoice->client->phone ?? '-' }})</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Alamat</span>
                        <span class="text-slate-600 leading-relaxed">{{ $invoice->client->address ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card Rujukan Shipment -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs text-xs">
                <h3 class="font-bold text-slate-900 mb-3 pb-2 border-b border-slate-100">Rujukan Operasional Pengiriman</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Nomor Proyek (Job Order)</span>
                        <a href="{{ $invoice->shipment ? route('core.document.show', $invoice->shipment->id) : '#' }}" 
                           class="font-mono font-bold text-sky-600 hover:underline">
                            {{ $invoice->shipment->project_no ?? $invoice->shipment->shipment_number ?? '-' }}
                        </a>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Nomor Bill of Lading (B/L)</span>
                        <span class="font-mono text-slate-800">{{ $invoice->shipment->nomor_bl_awb ?? $invoice->shipment->bl_number ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Kapal & Voyage</span>
                        <span class="text-slate-700">{{ $invoice->shipment->nama_kapal ?? '-' }} / {{ $invoice->shipment->voyage_no ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Rute Pelabuhan</span>
                        <span class="text-slate-600">{{ $invoice->shipment->pelabuhan_muat ?? '-' }} &rarr; {{ $invoice->shipment->pelabuhan_bongkar ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Muatan Petikemas</span>
                        <span class="text-slate-700">{{ $invoice->shipment->nomor_kontainer ?? '1x40HC' }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Modal Catat Pelunasan Pembayaran (Mandat Mutlak: Standalone Backdrop z-[200] & Mobile Bottom-Sheet) -->
    <div x-show="showPayModal" 
         class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="pay-modal-title"
         x-cloak>
        <!-- Independent Standalone Zero-Gap Backdrop -->
        <div x-show="showPayModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-md"
             @click="showPayModal = false"
             aria-hidden="true"></div>

        <!-- Dialog Shell: Mobile Bottom-Sheet -> Desktop Centered -->
        <div x-show="showPayModal"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative z-10 bg-white w-full rounded-t-[28px] sm:rounded-3xl max-w-lg max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border border-slate-200/80 text-xs">
            
            <!-- Mobile Grab Handle -->
            <div class="sm:hidden w-12 h-1.5 bg-slate-200 rounded-full mx-auto mt-3 mb-1"></div>

            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 id="pay-modal-title" class="font-bold text-slate-900 text-sm sm:text-base">Pencatatan Pelunasan Invoice</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Konfirmasi penerimaan pembayaran piutang klien (AR)</p>
                </div>
                <button type="button" 
                        @click="showPayModal = false" 
                        class="w-10 h-10 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900"
                        aria-label="Tutup modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('finance.invoices.pay', $invoice->id) }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nomor Invoice</label>
                            <input type="text" value="{{ $invoice->invoice_number ?? $invoice->invoice_no }}" disabled
                                   class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-mono text-xs cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Total Tagihan (IDR)</label>
                            <input type="text" value="Rp {{ number_format($invoice->total_tagihan_idr, 0, ',', '.') }}" disabled
                                   class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-900 font-mono font-bold text-xs cursor-not-allowed">
                        </div>
                    </div>

                    <div>
                        <label for="payment_date" class="block font-semibold text-slate-700 mb-1">Tanggal Pembayaran *</label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all">
                    </div>

                    <div>
                        <label for="account_id" class="block font-semibold text-slate-700 mb-1">Rekening Bank Tujuan Penerimaan *</label>
                        <select name="account_id" id="account_id" required
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all bg-white font-medium">
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
                        <label for="notes" class="block font-semibold text-slate-700 mb-1">Nomor Referensi / Catatan</label>
                        <input type="text" name="notes" id="notes" placeholder="Contoh: Ref Transfer BCA-91823719"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all">
                    </div>
                </div>

                <!-- Sticky Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showPayModal = false"
                            class="min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        Batal
                    </button>
                    <!-- Single Primary Action with button-in-button design-taste-frontend pattern -->
                    <button type="submit"
                            class="min-h-[44px] inline-flex items-center pl-4 pr-2 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all shadow-xs shadow-emerald-600/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                        <span>Konfirmasi Pelunasan</span>
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-700/60 text-white ml-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
