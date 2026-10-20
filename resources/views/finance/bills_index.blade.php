@extends('layouts.app')

@section('title', 'Finance — Hutang Vendor (AP) & Pembayaran')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12" 
     x-data="{ 
         activeTab: 'list', 
         showCreateModal: false, 
         showPayModal: false,
         payBillId: null,
         payRefNo: '',
         payAmount: 0,
         openPay(id, ref, amount) {
             this.payBillId = id;
             this.payRefNo = ref;
             this.payAmount = amount;
             this.showPayModal = true;
         }
     }">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / 
                <a href="{{ route('finance.index') }}" class="hover:underline">Finance</a> / 
                <span class="text-slate-800 font-semibold">Hutang Vendor (AP)</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Hutang Vendor (AP) & Pembayaran Operasional</h1>
            <p class="text-xs text-slate-500 mt-0.5">Tagihan pelayaran (THC/DO/Demurrage), subkon truk inland, depo, dan jadwal umur hutang vendor</p>
        </div>

        <div class="flex items-center gap-2">
            @can('finance.bill')
            <button type="button" @click="showCreateModal = true"
                    class="min-h-[44px] inline-flex items-center pl-4 pr-2 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                <span>Catat Tagihan Vendor Baru</span>
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-800 text-emerald-400 ml-2.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </span>
            </button>
            @endcan
        </div>
    </div>

    <!-- Bento KPI Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 text-xs">
        <!-- Total Hutang AP -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Total Hutang Berjalan (AP)</span>
            <span class="text-2xl font-black text-rose-600">Rp {{ number_format($totalOpenAp, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-500">
                Kewajiban pihak ketiga belum dibayar
            </div>
        </div>

        <!-- Jatuh Tempo 7 Hari -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Jatuh Tempo &le; 7 Hari</span>
            <span class="text-2xl font-black text-amber-600">{{ $dueIn7Days }} <span class="text-sm font-semibold text-slate-500">Tagihan</span></span>
            <div class="mt-2 text-[11px] text-amber-700 font-medium">
                Perlu segera dijadwalkan bayar
            </div>
        </div>

        <!-- Menunggu Approval Pimpinan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Menunggu Approval Pimpinan</span>
            <span class="text-2xl font-black text-indigo-600">{{ $pendingApprovalCount }} <span class="text-sm font-semibold text-slate-500">Tagihan</span></span>
            <div class="mt-2 text-[11px] text-slate-500">
                Nominal &gt; Rp 10.000.000 (limitAP)
            </div>
        </div>

        <!-- Terbayar Bulan Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Pencairan Terbayar Bulan Ini</span>
            <span class="text-2xl font-black text-emerald-600">Rp {{ number_format($totalPaidThisMonth, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-500">
                Pencairan kas & bank terverifikasi
            </div>
        </div>
    </div>

    <!-- Kebijakan Otorisasi Banner -->
    <div class="mb-6 p-4 rounded-2xl bg-sky-50 border border-sky-200/80 flex items-start gap-3.5 text-xs text-sky-900">
        <svg class="w-5 h-5 text-sky-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <strong class="font-bold">Standar Otorisasi Keuangan (limitAP Prototipe):</strong>
            <p class="text-sky-800 mt-0.5 leading-relaxed">
                Tagihan vendor pelayaran atau pihak ketiga dengan nominal di atas <strong>Rp 10.000.000</strong> wajib diajukan oleh Finance dan disetujui oleh <strong>Pimpinan (R-OWN)</strong> sebelum pencairan dana dari rekening kas/bank dapat dilakukan. Tagihan di bawah limit dapat langsung dicairkan oleh Finance.
            </p>
        </div>
    </div>

    <!-- Tab Switcher (Apple Style) -->
    <div class="flex items-center space-x-2 border-b border-slate-200 mb-6 text-xs font-semibold">
        <button type="button" @click="activeTab = 'list'"
                :class="activeTab === 'list' ? 'border-sky-600 text-sky-600 border-b-2' : 'text-slate-500 hover:text-slate-700'"
                class="py-3 px-4 transition-colors flex items-center gap-2">
            <span>Daftar Tagihan Vendor (Bills)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-600 font-mono">{{ $bills->total() }}</span>
        </button>
        <button type="button" @click="activeTab = 'aging'"
                :class="activeTab === 'aging' ? 'border-sky-600 text-sky-600 border-b-2' : 'text-slate-500 hover:text-slate-700'"
                class="py-3 px-4 transition-colors flex items-center gap-2">
            <span>Jadwal Umur Hutang (AP Aging Schedule)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800 font-mono">0–90+ Hari</span>
        </button>
    </div>

    <!-- TAB 1: DAFTAR TAGIHAN VENDOR -->
    <div x-show="activeTab === 'list'">
        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs mb-4 text-xs">
            <form action="{{ route('finance.bills.index') }}" method="GET" class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                    <select name="status" onchange="this.form.submit()" 
                            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none text-slate-700">
                        <option value="ALL" {{ request('status') === 'ALL' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                        <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Menunggu Approval</option>
                        <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Disetujui (Siap Bayar)</option>
                        <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>Lunas (Terbayar)</option>
                    </select>

                    <select name="vendor_type" onchange="this.form.submit()" 
                            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none text-slate-700">
                        <option value="ALL" {{ request('vendor_type') === 'ALL' || !request('vendor_type') ? 'selected' : '' }}>Semua Tipe Vendor</option>
                        <option value="SHIPPING_LINE" {{ request('vendor_type') === 'SHIPPING_LINE' ? 'selected' : '' }}>Pelayaran (Shipping Line)</option>
                        <option value="TRUCKING" {{ request('vendor_type') === 'TRUCKING' ? 'selected' : '' }}>Subkon Trucking</option>
                        <option value="TPS" {{ request('vendor_type') === 'TPS' ? 'selected' : '' }}>Terminal TPS</option>
                        <option value="CUSTOMS" {{ request('vendor_type') === 'CUSTOMS' ? 'selected' : '' }}>Bea Cukai</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-72">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Ref / Shipment..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none text-slate-800">
                    <button type="submit" class="px-3 py-2 bg-slate-800 text-white rounded-xl font-bold">Cari</button>
                </div>
            </form>
        </div>

        <!-- Table Vendor Bills -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden text-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">No. Ref & Tipe Vendor</th>
                            <th class="py-3 px-4">Shipment (Job Order)</th>
                            <th class="py-3 px-4 text-right">Nominal Tagihan</th>
                            <th class="py-3 px-4">Jatuh Tempo</th>
                            <th class="py-3 px-4 text-center">Status Tagihan</th>
                            <th class="py-3 px-4 text-right">Aksi & Eksekusi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($bills as $b)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-4">
                                    <span class="font-mono font-bold text-slate-900 block">{{ $b->ref_no }}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700 mt-0.5">
                                        {{ $b->vendor_type_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($b->shipment)
                                        <a href="{{ route('core.document.show', $b->shipment->id) }}" class="font-mono font-bold text-sky-600 hover:underline block">
                                            {{ $b->shipment->project_no ?? $b->shipment->shipment_number }}
                                        </a>
                                        <span class="text-[10px] text-slate-400">BL: {{ $b->shipment->nomor_bl_awb ?? '-' }}</span>
                                    @else
                                        <span class="text-slate-400 font-italic">Biaya Umum / Tanpa Job</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($b->amount, 0, ',', '.') }}
                                    @if($b->amount > \App\Http\Controllers\VendorBillController::APPROVAL_THRESHOLD)
                                        <span class="block text-[9px] font-sans font-bold text-amber-600">&gt; Rp 10 Jt (Wajib Approval)</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-700">
                                        {{ $b->due_date ? $b->due_date->format('d/m/Y') : '-' }}
                                    </div>
                                    @if($b->status !== 'PAID' && $b->due_date)
                                        @php
                                            $diff = Carbon\Carbon::parse($b->due_date)->diffInDays(now(), false);
                                        @endphp
                                        @if($diff > 0)
                                            <span class="text-[10px] text-rose-600 font-bold block">Lewat {{ $diff }} hari</span>
                                        @elseif($diff >= -7)
                                            <span class="text-[10px] text-amber-600 font-bold block">Sisa {{ abs($diff) }} hari</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($b->status === 'PAID')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            LUNAS
                                        </span>
                                    @elseif($b->status === 'APPROVED')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">
                                            DISETUJUI
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            MENUNGGU PIMPINAN
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                                    <!-- Aksi Approval Pimpinan -->
                                    @if($b->status === 'PENDING')
                                        @can('finance.ap_approve')
                                            <form action="{{ route('finance.bills.approve', $b->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" 
                                                        onclick="return confirm('Setujui pencairan tagihan vendor {{ $b->ref_no }} senilai Rp {{ number_format($b->amount, 0, ',', '.') }}?')"
                                                        class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs">
                                                    ✓ Setujui Pimpinan
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[10px] text-slate-400 font-medium">Perlu Akun R-OWN</span>
                                        @endcan
                                    @endif

                                    <!-- Aksi Pencairan Pembayaran Finance -->
                                    @if($b->status === 'APPROVED')
                                        @can('finance.pay')
                                            <button type="button" 
                                                    @click="openPay('{{ $b->id }}', '{{ $b->ref_no }}', {{ $b->amount }})"
                                                    class="px-2.5 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-[11px] shadow-xs">
                                                Bayar Tagihan
                                            </button>
                                        @endcan
                                    @endif

                                    @if($b->status === 'PAID')
                                        @php $p = $b->payments->first(); @endphp
                                        <span class="text-[10px] text-slate-500">
                                            Dibayar {{ $p && $p->paid_at ? $p->paid_at->format('d/m/Y') : 'Lunas' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    Belum ada tagihan vendor yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $bills->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 2: AGING SCHEDULE UMUR HUTANG -->
    <div x-show="activeTab === 'aging'" x-cloak>
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden text-xs">
            <div class="p-5 border-b border-slate-100">
                <h2 class="font-bold text-sm text-slate-900">AP Aging Schedule (Jadwal Umur Hutang per Tipe Vendor)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Analisis jatuh tempo hutang lancar dan tagihan tertunggak untuk manajemen likuiditas kas</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Kategori Vendor</th>
                            <th class="py-3 px-4 text-right">Lancar (0–30 Hari)</th>
                            <th class="py-3 px-4 text-right">31–60 Hari</th>
                            <th class="py-3 px-4 text-right">61–90 Hari</th>
                            <th class="py-3 px-4 text-right">&gt; 90 Hari (Kritis)</th>
                            <th class="py-3 px-4 text-right font-bold text-slate-900">Total Hutang (IDR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono">
                        @php 
                            $grandTotal = 0; 
                            $g0_30 = 0; $g31_60 = 0; $g61_90 = 0; $gOver90 = 0;
                        @endphp
                        @forelse($agingSchedule as $item)
                            @php
                                $grandTotal += $item['total'];
                                $g0_30 += $item['current'];
                                $g31_60 += $item['days_31_60'];
                                $g61_90 += $item['days_61_90'];
                                $gOver90 += $item['over_90'];
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-4 font-sans font-bold text-slate-800">
                                    {{ $item['label'] }}
                                </td>
                                <td class="py-3 px-4 text-right text-slate-700">
                                    Rp {{ number_format($item['current'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right text-amber-600">
                                    Rp {{ number_format($item['days_31_60'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right text-orange-600">
                                    Rp {{ number_format($item['days_61_90'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right text-rose-600 font-bold">
                                    Rp {{ number_format($item['over_90'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    Rp {{ number_format($item['total'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 font-sans">
                                    Tidak ada hutang vendor berjalan. Semua tagihan telah lunas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(!empty($agingSchedule))
                    <tfoot class="bg-slate-50 divide-y divide-slate-100 font-semibold border-t border-slate-200 font-mono text-xs">
                        <tr class="bg-slate-100 font-bold text-slate-900">
                            <td class="py-3 px-4 font-sans">TOTAL GABUNGAN:</td>
                            <td class="py-3 px-4 text-right">Rp {{ number_format($g0_30, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right text-amber-700">Rp {{ number_format($g31_60, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right text-orange-700">Rp {{ number_format($g61_90, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right text-rose-700 font-black">Rp {{ number_format($gOver90, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-black text-rose-800">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL 1: FORM INPUT TAGIHAN VENDOR BARU (Mandat Mutlak: Standalone Backdrop z-[200] & Mobile Bottom-Sheet) -->
    <div x-show="showCreateModal" 
         class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         role="dialog"
         aria-modal="true"
         aria-labelledby="create-bill-title"
         x-cloak>
        <!-- Independent Standalone Zero-Gap Backdrop -->
        <div x-show="showCreateModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-md"
             @click="showCreateModal = false"
             aria-hidden="true"></div>

        <!-- Dialog Shell: Mobile Bottom-Sheet -> Desktop Centered -->
        <div x-show="showCreateModal"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative z-10 bg-white w-full rounded-t-[28px] sm:rounded-3xl max-w-lg max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border border-slate-200/80 text-xs">
            
            <!-- Mobile Grab Handle -->
            <div class="sm:hidden w-12 h-1.5 bg-slate-200 rounded-full mx-auto mt-3 mb-1"></div>

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 id="create-bill-title" class="font-bold text-slate-900 text-sm sm:text-base">Pendaftaran Tagihan Vendor Baru (AP)</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Tagihan pelayaran, depo, trucking inland, atau bea cukai</p>
                </div>
                <button type="button" 
                        @click="showCreateModal = false" 
                        class="w-10 h-10 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900"
                        aria-label="Tutup modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('finance.bills.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label for="vendor_type" class="block font-semibold text-slate-700 mb-1">Kategori Tipe Vendor *</label>
                        <select name="vendor_type" id="vendor_type" required
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all bg-white font-medium">
                            <option value="SHIPPING_LINE">Pelayaran (THC, DO Fee, Demurrage)</option>
                            <option value="TRUCKING">Subkon Trucking Inland Transport</option>
                            <option value="TPS">Terminal Petikemas TPS (Lift On/Off)</option>
                            <option value="CUSTOMS">Pungutan Resmi Bea Cukai</option>
                        </select>
                    </div>

                    <div>
                        <label for="shipment_id" class="block font-semibold text-slate-700 mb-1">Tautkan ke Shipment / Job Order (Opsional)</label>
                        <select name="shipment_id" id="shipment_id"
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all bg-white">
                            <option value="">-- Biaya Umum / Tanpa Kaitan Job --</option>
                            @foreach($activeShipments as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->project_no ?? $s->shipment_number }} — {{ $s->client->name ?? 'Klien' }} (BL: {{ $s->nomor_bl_awb ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="ref_no" class="block font-semibold text-slate-700 mb-1">Nomor Referensi Tagihan / Invoice Vendor *</label>
                        <input type="text" name="ref_no" id="ref_no" required placeholder="Contoh: INV-WANHAI-81923"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all font-mono">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="amount" class="block font-semibold text-slate-700 mb-1">Nominal Tagihan (IDR) *</label>
                            <input type="number" name="amount" id="amount" required min="1" step="0.01" placeholder="Contoh: 4500000"
                                   class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all font-mono font-bold">
                            <span class="text-[10px] text-slate-400 mt-1 block">&gt; Rp 10 Jt perlu approval Pimpinan</span>
                        </div>

                        <div>
                            <label for="due_date" class="block font-semibold text-slate-700 mb-1">Jatuh Tempo *</label>
                            <input type="date" name="due_date" id="due_date" required value="{{ date('Y-m-d', strtotime('+14 days')) }}"
                                   class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Sticky Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showCreateModal = false"
                            class="min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        Batal
                    </button>
                    <!-- Single Primary Action with button-in-button design-taste-frontend pattern -->
                    <button type="submit"
                            class="min-h-[44px] inline-flex items-center pl-4 pr-2 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        <span>Simpan Tagihan Vendor</span>
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-800 text-emerald-400 ml-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: FORM PENCAIRAN PEMBAYARAN TAGIHAN VENDOR (Mandat Mutlak: Standalone Backdrop z-[200] & Mobile Bottom-Sheet) -->
    <div x-show="showPayModal" 
         class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         role="dialog"
         aria-modal="true"
         aria-labelledby="pay-bill-title"
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
             class="relative z-10 bg-white w-full rounded-t-[28px] sm:rounded-3xl max-w-md max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border border-slate-200/80 text-xs">
            
            <!-- Mobile Grab Handle -->
            <div class="sm:hidden w-12 h-1.5 bg-slate-200 rounded-full mx-auto mt-3 mb-1"></div>

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 id="pay-bill-title" class="font-bold text-slate-900 text-sm sm:text-base">Pencairan Pembayaran Vendor</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Pengeluaran kas/bank untuk pelunasan hutang</p>
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

            <form :action="'{{ url('finance/bills') }}/' + payBillId + '/pay'" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor Referensi Tagihan</label>
                        <input type="text" :value="payRefNo" disabled
                               class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-mono text-xs cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nominal yang Dicairkan (IDR)</label>
                        <input type="text" :value="'Rp ' + Number(payAmount).toLocaleString('id-ID')" disabled
                               class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-900 font-mono font-bold text-xs cursor-not-allowed">
                    </div>

                    <div>
                        <label for="account_id" class="block font-semibold text-slate-700 mb-1">Rekening Kas / Bank Pengirim *</label>
                        <select name="account_id" id="account_id" required
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all bg-white font-medium">
                            @foreach($bankAccounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->name }} (Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-400 mt-1 block">Saldo kas/bank akan terpotong otomatis setelah konfirmasi</span>
                    </div>

                    <div>
                        <label for="paid_at" class="block font-semibold text-slate-700 mb-1">Tanggal Pencairan / Bayar *</label>
                        <input type="date" name="paid_at" id="paid_at" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all">
                    </div>

                    <div>
                        <label for="payment_ref" class="block font-semibold text-slate-700 mb-1">Nomor Referensi Transfer / Bukti</label>
                        <input type="text" name="payment_ref" id="payment_ref" placeholder="Contoh: TRF-BCA-81923"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all font-mono">
                    </div>
                </div>

                <!-- Sticky Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showPayModal = false"
                            class="min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        Batal
                    </button>
                    <!-- Single Primary Action with button-in-button design-taste-frontend pattern -->
                    <button type="submit"
                            class="min-h-[44px] inline-flex items-center pl-4 pr-2 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition-all shadow-xs shadow-sky-600/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-700">
                        <span>Konfirmasi Pencairan</span>
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-sky-700/60 text-white ml-2.5">
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
