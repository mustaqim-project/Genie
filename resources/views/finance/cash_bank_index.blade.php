@extends('layouts.app')

@section('title', 'Finance — Kas & Bank Operasional')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12"
     x-data="{ 
         activeTab: 'mutasi', 
         showTrxModal: false, 
         showTransferModal: false, 
         showAccountModal: false 
     }">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / 
                <a href="{{ route('finance.index') }}" class="hover:underline">Finance</a> / 
                <span class="text-slate-800 font-semibold">Kas & Bank</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Buku Kas & Bank Operasional</h1>
            <p class="text-xs text-slate-500 mt-0.5">Saldo multi-rekening, mutasi kas real-time, pindah buku antar-bank, dan rekonsiliasi pembukuan</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @can('finance.manage')
            <button type="button" @click="showTransferModal = true"
                    class="min-h-[44px] inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/90 text-xs font-bold transition-all shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Pindah Buku</span>
            </button>

            <button type="button" @click="showTrxModal = true"
                    class="min-h-[44px] inline-flex items-center pl-4 pr-2 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                <span>Catat Mutasi Kas</span>
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-800 text-emerald-400 ml-2.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </span>
            </button>

            <button type="button" @click="showAccountModal = true"
                    class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-xl bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/90 flex items-center justify-center transition-colors shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900"
                    title="Buka Rekening Baru"
                    aria-label="Buka Rekening Baru">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            </button>
            @endcan
        </div>
    </div>

    <!-- Bento KPI Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 text-xs">
        <!-- Total Likuiditas Kas & Bank -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white p-5 rounded-2xl shadow-sm border border-slate-700/50">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Total Likuiditas Kas & Bank</span>
            <span class="text-2xl font-black text-white">Rp {{ number_format($totalLiquidity, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-300 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Saldo aktif seluruh rekening operasional</span>
            </div>
        </div>

        <!-- Penerimaan Kas Masuk -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Kas Masuk Bulan Ini</span>
            <span class="text-2xl font-black text-emerald-600">Rp {{ number_format($totalInThisMonth, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-500">
                Pelunasan invoice klien & setoran
            </div>
        </div>

        <!-- Pengeluaran Kas Keluar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Kas Keluar Bulan Ini</span>
            <span class="text-2xl font-black text-rose-600">Rp {{ number_format($totalOutThisMonth, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-500">
                Pencairan vendor & talangan pabean
            </div>
        </div>

        <!-- Rekening Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Jumlah Rekening Kas/Bank</span>
            <span class="text-2xl font-black text-slate-900">{{ $accounts->count() }} <span class="text-sm font-semibold text-slate-500">Rekening</span></span>
            <div class="mt-2 text-[11px] text-sky-700 font-medium">
                Bank Mandiri, BCA & Kas Kecil
            </div>
        </div>
    </div>

    <!-- Cards Rekening Per Bank (Horizontal Scroll / Grid) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @foreach($accounts as $acc)
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                            {{ $acc->bank }}
                        </span>
                        <span class="text-[11px] font-mono text-slate-400">
                            {{ $acc->transactions->count() }} Mutasi
                        </span>
                    </div>
                    <div class="font-bold text-slate-900 text-sm">{{ $acc->name }}</div>
                    <div class="font-mono text-xs text-slate-500 mt-0.5">{{ $acc->account_no }}</div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-baseline justify-between">
                    <span class="text-[10px] uppercase font-bold text-slate-400">Saldo Saat Ini</span>
                    <span class="text-lg font-mono font-black text-slate-900">
                        Rp {{ number_format($acc->current_balance, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Tab Switcher -->
    <div class="flex items-center space-x-2 border-b border-slate-200 mb-6 text-xs font-semibold">
        <button type="button" @click="activeTab = 'mutasi'"
                :class="activeTab === 'mutasi' ? 'border-sky-600 text-sky-600 border-b-2' : 'text-slate-500 hover:text-slate-700'"
                class="py-3 px-4 transition-colors flex items-center gap-2">
            <span>Buku Mutasi Kas & Bank</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-600 font-mono">{{ $transactions->total() }}</span>
        </button>
        <button type="button" @click="activeTab = 'accounts'"
                :class="activeTab === 'accounts' ? 'border-sky-600 text-sky-600 border-b-2' : 'text-slate-500 hover:text-slate-700'"
                class="py-3 px-4 transition-colors flex items-center gap-2">
            <span>Daftar Master Rekening Bank</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-600 font-mono">{{ $accounts->count() }}</span>
        </button>
    </div>

    <!-- TAB 1: BUKU MUTASI TRANSAKSI -->
    <div x-show="activeTab === 'mutasi'">
        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs mb-4 text-xs">
            <form action="{{ route('finance.cash-bank.index') }}" method="GET" class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                    <select name="account_id" onchange="this.form.submit()" 
                            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none text-slate-700">
                        <option value="ALL" {{ request('account_id') === 'ALL' || !request('account_id') ? 'selected' : '' }}>Semua Rekening Bank</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ request('account_id') === $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }} ({{ $acc->bank }})
                            </option>
                        @endforeach
                    </select>

                    <select name="direction" onchange="this.form.submit()" 
                            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none text-slate-700">
                        <option value="" {{ !request('direction') ? 'selected' : '' }}>Semua Arus Kas</option>
                        <option value="IN" {{ request('direction') === 'IN' ? 'selected' : '' }}>Kas Masuk (+) Saja</option>
                        <option value="OUT" {{ request('direction') === 'OUT' ? 'selected' : '' }}>Kas Keluar (-) Saja</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-72">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari uraian transaksi / ref..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none text-slate-800">
                    <button type="submit" class="px-3 py-2 bg-slate-800 text-white rounded-xl font-bold">Cari</button>
                </div>
            </form>
        </div>

        <!-- Table Mutasi -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden text-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Tanggal & Ref</th>
                            <th class="py-3 px-4">Rekening Kas / Bank</th>
                            <th class="py-3 px-4">Uraian Transaksi</th>
                            <th class="py-3 px-4 text-right">Kas Masuk (IDR)</th>
                            <th class="py-3 px-4 text-right">Kas Keluar (IDR)</th>
                            <th class="py-3 px-4 text-center">Rekonsiliasi</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono">
                        @forelse($transactions as $t)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-4">
                                    <div class="font-sans font-bold text-slate-800">
                                        {{ $t->trx_date ? $t->trx_date->format('d/m/Y') : '-' }}
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-mono">
                                        {{ $t->ref_type ?? 'TRX' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-sans">
                                    <span class="font-semibold text-slate-800 block">{{ $t->account->name ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $t->account->account_no ?? '' }}</span>
                                </td>
                                <td class="py-3 px-4 font-sans text-slate-700">
                                    {{ $t->description }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($t->amount > 0)
                                        <span class="font-bold text-emerald-600">
                                            + Rp {{ number_format($t->amount, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($t->amount < 0)
                                        <span class="font-bold text-rose-600">
                                            - Rp {{ number_format(abs($t->amount), 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-sans">
                                    @if($t->reconciled)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            ✓ Match
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                            Belum
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-sans">
                                    @can('finance.manage')
                                    <form action="{{ route('finance.cash-bank.transactions.reconcile', $t->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2 py-1 rounded-lg text-[10px] font-bold {{ $t->reconciled ? 'bg-slate-100 hover:bg-slate-200 text-slate-600' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700' }}"
                                                title="Ubah status rekonsiliasi bank">
                                            {{ $t->reconciled ? 'Batal Rekon' : 'Tandai Match' }}
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 font-sans">
                                    Belum ada transaksi mutasi kas & bank yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 font-sans">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 2: DAFTAR REKENING MASTER -->
    <div x-show="activeTab === 'accounts'" x-cloak>
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden text-xs">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-sm text-slate-900">Daftar Rekening Bank & Kas Perusahaan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar akun kas penampung operasional forwarder, billing pabean, dan kas kecil</p>
                </div>
                @can('finance.manage')
                <button type="button" @click="showAccountModal = true"
                        class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-all active:scale-95 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Buka Akun Rekening Baru</span>
                </button>
                @endcan
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Nama Rekening</th>
                            <th class="py-3 px-4">Nama Bank / Institusi</th>
                            <th class="py-3 px-4">Nomor Rekening</th>
                            <th class="py-3 px-4 text-right">Saldo Saat Ini (IDR)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($accounts as $acc)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    {{ $acc->name }}
                                </td>
                                <td class="py-3 px-4 text-slate-700">
                                    {{ $acc->bank }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-800">
                                    {{ $acc->account_no }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($acc->current_balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL 1: CATAT MUTASI KAS MANUAL (Mandat Mutlak: Standalone Backdrop z-[200] & Mobile Bottom-Sheet) -->
    <div x-show="showTrxModal" 
         class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         role="dialog"
         aria-modal="true"
         aria-labelledby="trx-modal-title"
         x-cloak>
        <!-- Independent Standalone Zero-Gap Backdrop -->
        <div x-show="showTrxModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-md"
             @click="showTrxModal = false"
             aria-hidden="true"></div>

        <!-- Dialog Shell: Mobile Bottom-Sheet -> Desktop Centered -->
        <div x-show="showTrxModal"
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
                    <h3 id="trx-modal-title" class="font-bold text-slate-900 text-sm sm:text-base">Pencatatan Mutasi Kas Operasional</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Catat penerimaan atau pengeluaran kas kecil & operasional</p>
                </div>
                <button type="button" 
                        @click="showTrxModal = false" 
                        class="w-10 h-10 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900"
                        aria-label="Tutup modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('finance.cash-bank.transactions.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label for="account_id" class="block font-semibold text-slate-700 mb-1">Rekening Kas / Bank *</label>
                        <select name="account_id" id="account_id" required
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all bg-white font-medium">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->name }} (Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Arus Kas Masuk / Keluar *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border border-slate-200 flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 transition-colors">
                                <input type="radio" name="type" value="IN" checked class="text-emerald-600 focus:ring-emerald-500">
                                <span class="font-bold text-emerald-700">Kas Masuk (+)</span>
                            </label>
                            <label class="p-3 rounded-xl border border-slate-200 flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 transition-colors">
                                <input type="radio" name="type" value="OUT" class="text-rose-600 focus:ring-rose-500">
                                <span class="font-bold text-rose-700">Kas Keluar (-)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="amount" class="block font-semibold text-slate-700 mb-1">Nominal (IDR) *</label>
                        <input type="number" name="amount" id="amount" required min="1" step="0.01" placeholder="Contoh: 1500000"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all font-mono font-bold">
                    </div>

                    <div>
                        <label for="trx_date" class="block font-semibold text-slate-700 mb-1">Tanggal Transaksi *</label>
                        <input type="date" name="trx_date" id="trx_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all">
                    </div>

                    <div>
                        <label for="description" class="block font-semibold text-slate-700 mb-1">Uraian / Keterangan Mutasi *</label>
                        <input type="text" name="description" id="description" required placeholder="Contoh: Pengisian kembali kas kecil operasional"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all">
                    </div>
                </div>

                <!-- Sticky Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showTrxModal = false"
                            class="min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        Batal
                    </button>
                    <!-- Single Primary Action with button-in-button design-taste-frontend pattern -->
                    <button type="submit"
                            class="min-h-[44px] inline-flex items-center pl-4 pr-2 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        <span>Simpan Mutasi Kas</span>
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

    <!-- MODAL 2: PINDAH BUKU / TRANSFER ANTAR-REKENING (Mandat Mutlak: Standalone Backdrop z-[200] & Mobile Bottom-Sheet) -->
    <div x-show="showTransferModal" 
         class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         role="dialog"
         aria-modal="true"
         aria-labelledby="transfer-modal-title"
         x-cloak>
        <!-- Independent Standalone Zero-Gap Backdrop -->
        <div x-show="showTransferModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-md"
             @click="showTransferModal = false"
             aria-hidden="true"></div>

        <!-- Dialog Shell: Mobile Bottom-Sheet -> Desktop Centered -->
        <div x-show="showTransferModal"
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
                    <h3 id="transfer-modal-title" class="font-bold text-slate-900 text-sm sm:text-base">Pindah Buku Antar-Rekening Bank</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Transfer likuiditas kas operasional internal perusahaan</p>
                </div>
                <button type="button" 
                        @click="showTransferModal = false" 
                        class="w-10 h-10 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900"
                        aria-label="Tutup modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('finance.cash-bank.transfer') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label for="from_account_id" class="block font-semibold text-slate-700 mb-1">Dari Rekening Asal *</label>
                        <select name="from_account_id" id="from_account_id" required
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all bg-white font-medium">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->name }} (Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="to_account_id" class="block font-semibold text-slate-700 mb-1">Ke Rekening Tujuan *</label>
                        <select name="to_account_id" id="to_account_id" required
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all bg-white font-medium">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->name }} (Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="amount" class="block font-semibold text-slate-700 mb-1">Nominal Pindah Buku (IDR) *</label>
                        <input type="number" name="amount" id="amount" required min="1" step="0.01" placeholder="Contoh: 50000000"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all font-mono font-bold">
                    </div>

                    <div>
                        <label for="transfer_date" class="block font-semibold text-slate-700 mb-1">Tanggal Transfer *</label>
                        <input type="date" name="transfer_date" id="transfer_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all">
                    </div>

                    <div>
                        <label for="notes" class="block font-semibold text-slate-700 mb-1">Catatan Pindah Buku</label>
                        <input type="text" name="notes" id="notes" placeholder="Contoh: Top-up saldo deposit MPN G3 Mandiri"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all">
                    </div>
                </div>

                <!-- Sticky Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showTransferModal = false"
                            class="min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        Batal
                    </button>
                    <!-- Single Primary Action with button-in-button design-taste-frontend pattern -->
                    <button type="submit"
                            class="min-h-[44px] inline-flex items-center pl-4 pr-2 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition-all shadow-xs shadow-sky-600/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-700">
                        <span>Eksekusi Pindah Buku</span>
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-sky-700/60 text-white ml-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: TAMBAH REKENING BARU (Mandat Mutlak: Standalone Backdrop z-[200] & Mobile Bottom-Sheet) -->
    <div x-show="showAccountModal" 
         class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         role="dialog"
         aria-modal="true"
         aria-labelledby="account-modal-title"
         x-cloak>
        <!-- Independent Standalone Zero-Gap Backdrop -->
        <div x-show="showAccountModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-md"
             @click="showAccountModal = false"
             aria-hidden="true"></div>

        <!-- Dialog Shell: Mobile Bottom-Sheet -> Desktop Centered -->
        <div x-show="showAccountModal"
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
                    <h3 id="account-modal-title" class="font-bold text-slate-900 text-sm sm:text-base">Buka Rekening Kas / Bank Baru</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Daftarkan rekening bank atau kas operasional baru ke sistem</p>
                </div>
                <button type="button" 
                        @click="showAccountModal = false" 
                        class="w-10 h-10 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900"
                        aria-label="Tutup modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('finance.cash-bank.accounts.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label for="name" class="block font-semibold text-slate-700 mb-1">Nama Rekening / Deskripsi *</label>
                        <input type="text" name="name" id="name" required placeholder="Contoh: Bank BNI Escrow Logistik"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all">
                    </div>

                    <div>
                        <label for="bank" class="block font-semibold text-slate-700 mb-1">Nama Bank / Institusi *</label>
                        <input type="text" name="bank" id="bank" required placeholder="Contoh: Bank Negara Indonesia (BNI)"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all">
                    </div>

                    <div>
                        <label for="account_no" class="block font-semibold text-slate-700 mb-1">Nomor Rekening *</label>
                        <input type="text" name="account_no" id="account_no" required placeholder="Contoh: 0918-2831-928"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all font-mono">
                    </div>

                    <div>
                        <label for="initial_balance" class="block font-semibold text-slate-700 mb-1">Saldo Awal Pembukaan (IDR)</label>
                        <input type="number" name="initial_balance" id="initial_balance" min="0" step="0.01" placeholder="0"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:border-slate-900 transition-all font-mono">
                    </div>
                </div>

                <!-- Sticky Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showAccountModal = false"
                            class="min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        Batal
                    </button>
                    <!-- Single Primary Action with button-in-button design-taste-frontend pattern -->
                    <button type="submit"
                            class="min-h-[44px] inline-flex items-center pl-4 pr-2 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        <span>Buka Rekening</span>
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-800 text-emerald-400 ml-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
