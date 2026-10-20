@extends('layouts.app')

@section('title', 'Finance — Kas & Bank Operasional')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12"
     x-data="{ 
         activeTab: 'mutasi', 
         showTrxModal: false, 
         showTransferModal: false, 
         showAccountModal: false 
     }">

    <!-- Breadcrumb -->
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Finance', 'url' => route('finance.index')],
        ['label' => 'Kas & Bank']
    ]" />

    <!-- Apple HIG Module Header Bar -->
    <div class="mb-6 rounded-[16px] bg-white dark:bg-[#1C1C1E] p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl sm:text-2xl font-bold font-apple-display tracking-tight text-slate-900 dark:text-white">
                    Buku Kas & Bank Operasional
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    Liquidity
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Saldo multi-rekening, mutasi kas real-time, pindah buku antar-bank, dan rekonsiliasi pembukuan
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @can('finance.manage')
            <button type="button" @click="showTransferModal = true"
                    class="btn-apple-gray">
                <i data-lucide="arrow-left-right" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Pindah Buku</span>
            </button>

            <button type="button" @click="showTrxModal = true"
                    class="btn-apple-filled">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Catat Mutasi Kas</span>
            </button>

            <button type="button" @click="showAccountModal = true"
                    class="btn-apple-gray !p-2 rounded-xl"
                    title="Buka Rekening Baru"
                    aria-label="Buka Rekening Baru">
                <i data-lucide="landmark" class="w-4 h-4"></i>
            </button>
            @endcan
        </div>
    </div>

    <!-- Bento KPI Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 text-xs">
        <!-- Total Likuiditas Kas & Bank -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white p-5 rounded-2xl shadow-sm border border-slate-700/50">
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Total Likuiditas Kas & Bank</span>
            <span class="text-2xl font-black font-mono tabular-nums text-white">Rp {{ number_format($totalLiquidity, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-300 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Saldo aktif seluruh rekening operasional</span>
            </div>
        </div>

        <!-- Penerimaan Kas Masuk -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs">
            <span class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider block text-[10px] mb-1">Kas Masuk Bulan Ini</span>
            <span class="text-2xl font-black font-mono tabular-nums text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalInThisMonth, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                Pelunasan invoice klien & setoran
            </div>
        </div>

        <!-- Pengeluaran Kas Keluar -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs">
            <span class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider block text-[10px] mb-1">Kas Keluar Bulan Ini</span>
            <span class="text-2xl font-black font-mono tabular-nums text-rose-600 dark:text-rose-400">Rp {{ number_format($totalOutThisMonth, 0, ',', '.') }}</span>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                Pencairan vendor & talangan pabean
            </div>
        </div>

        <!-- Rekening Aktif -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs">
            <span class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider block text-[10px] mb-1">Jumlah Rekening Kas/Bank</span>
            <span class="text-2xl font-black font-mono tabular-nums text-slate-900 dark:text-white">{{ $accounts->count() }} <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Rekening</span></span>
            <div class="mt-2 text-[11px] text-[#007AFF] font-medium">
                Bank Mandiri, BCA & Kas Kecil
            </div>
        </div>
    </div>

    <!-- Cards Rekening Per Bank -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @foreach($accounts as $acc)
            <div class="bg-white dark:bg-[#1C1C1E] p-4 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300">
                            {{ $acc->bank }}
                        </span>
                        <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500 tabular-nums">
                            {{ $acc->transactions->count() }} Mutasi
                        </span>
                    </div>
                    <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $acc->name }}</div>
                    <div class="font-mono text-xs text-slate-500 dark:text-slate-400 mt-0.5 tabular-nums">{{ $acc->account_no }}</div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-white/10 flex items-baseline justify-between">
                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">Saldo Saat Ini</span>
                    <span class="text-lg font-mono font-black text-slate-900 dark:text-white tabular-nums">
                        Rp {{ number_format($acc->current_balance, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Segmented Tab Switcher (Apple Style) -->
    <div class="inline-flex p-1 rounded-xl bg-slate-200/70 dark:bg-white/10 mb-6 text-xs font-semibold">
        <button type="button" @click="activeTab = 'mutasi'"
                :class="activeTab === 'mutasi' ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="py-2 px-4 rounded-lg transition-all flex items-center gap-2">
            <span>Buku Mutasi Kas & Bank</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 font-mono tabular-nums">{{ $transactions->total() }}</span>
        </button>
        <button type="button" @click="activeTab = 'accounts'"
                :class="activeTab === 'accounts' ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="py-2 px-4 rounded-lg transition-all flex items-center gap-2">
            <span>Daftar Master Rekening Bank</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 font-mono tabular-nums">{{ $accounts->count() }}</span>
        </button>
    </div>

    <!-- TAB 1: BUKU MUTASI TRANSAKSI -->
    <div x-show="activeTab === 'mutasi'">
        <!-- Filter Bar -->
        <div class="bg-white dark:bg-[#1C1C1E] p-4 rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs mb-4 text-xs">
            <form action="{{ route('finance.cash-bank.index') }}" method="GET" class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                    <select name="account_id" onchange="this.form.submit()" 
                            class="px-3 py-2 bg-slate-50 dark:bg-[#2C2C2E] border border-slate-200 dark:border-white/15 dark:text-white rounded-xl focus:outline-none">
                        <option value="ALL" {{ request('account_id') === 'ALL' || !request('account_id') ? 'selected' : '' }}>Semua Rekening Bank</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ request('account_id') === $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }} ({{ $acc->bank }})
                            </option>
                        @endforeach
                    </select>

                    <select name="direction" onchange="this.form.submit()" 
                            class="px-3 py-2 bg-slate-50 dark:bg-[#2C2C2E] border border-slate-200 dark:border-white/15 dark:text-white rounded-xl focus:outline-none">
                        <option value="" {{ !request('direction') ? 'selected' : '' }}>Semua Arus Kas</option>
                        <option value="IN" {{ request('direction') === 'IN' ? 'selected' : '' }}>Kas Masuk (+) Saja</option>
                        <option value="OUT" {{ request('direction') === 'OUT' ? 'selected' : '' }}>Kas Keluar (-) Saja</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-72">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari uraian transaksi / ref..."
                           class="w-full px-3 py-2 bg-slate-50 dark:bg-[#2C2C2E] border border-slate-200 dark:border-white/15 dark:text-white rounded-xl focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    <button type="submit" class="btn-apple-filled !py-2">Cari</button>
                </div>
            </form>
        </div>

        <!-- Table Mutasi -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs overflow-hidden text-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-[#2C2C2E] text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-100 dark:border-white/10">
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
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5 font-mono">
                        @forelse($transactions as $t)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02]">
                                <td class="py-3 px-4">
                                    <div class="font-sans font-bold text-slate-800 dark:text-slate-200 tabular-nums">
                                        {{ $t->trx_date ? $t->trx_date->format('d/m/Y') : '-' }}
                                    </div>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">
                                        {{ $t->ref_type ?? 'TRX' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-sans">
                                    <span class="font-semibold text-slate-800 dark:text-white block">{{ $t->account->name ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono tabular-nums">{{ $t->account->account_no ?? '' }}</span>
                                </td>
                                <td class="py-3 px-4 font-sans text-slate-700 dark:text-slate-300">
                                    {{ $t->description }}
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums">
                                    @if($t->amount > 0)
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                            + Rp {{ number_format($t->amount, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-600">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums">
                                    @if($t->amount < 0)
                                        <span class="font-bold text-rose-600 dark:text-rose-400">
                                            - Rp {{ number_format(abs($t->amount), 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-600">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-sans">
                                    @if($t->reconciled)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            ✓ Match
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-400">
                                            Belum
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-sans">
                                    @can('finance.manage')
                                    <form action="{{ route('finance.cash-bank.transactions.reconcile', $t->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2 py-1 rounded-lg text-[10px] font-bold {{ $t->reconciled ? 'bg-slate-100 hover:bg-slate-200 text-slate-600 dark:bg-white/10 dark:hover:bg-white/15 dark:text-slate-300' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/50 dark:text-emerald-300' }}"
                                                title="Ubah status rekonsiliasi bank">
                                            {{ $t->reconciled ? 'Batal Rekon' : 'Tandai Match' }}
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 dark:text-slate-500 font-sans">
                                    Belum ada transaksi mutasi kas & bank yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-white/10 font-sans">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 2: DAFTAR REKENING MASTER -->
    <div x-show="activeTab === 'accounts'" x-cloak>
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs overflow-hidden text-xs">
            <div class="p-5 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-sm text-slate-900 dark:text-white">Daftar Rekening Bank & Kas Perusahaan</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar akun kas penampung operasional forwarder, billing pabean, dan kas kecil</p>
                </div>
                @can('finance.manage')
                <button type="button" @click="showAccountModal = true"
                        class="btn-apple-filled !py-2 !px-3">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Buka Akun Rekening Baru</span>
                </button>
                @endcan
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-[#2C2C2E] text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-100 dark:border-white/10">
                        <tr>
                            <th class="py-3 px-4">Nama Rekening</th>
                            <th class="py-3 px-4">Nama Bank / Institusi</th>
                            <th class="py-3 px-4">Nomor Rekening</th>
                            <th class="py-3 px-4 text-right">Saldo Saat Ini (IDR)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach($accounts as $acc)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02]">
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ $acc->name }}
                                </td>
                                <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                                    {{ $acc->bank }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-800 dark:text-slate-200 tabular-nums">
                                    {{ $acc->account_no }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white tabular-nums">
                                    Rp {{ number_format($acc->current_balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
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

    <!-- MODAL 1: CATAT MUTASI KAS MANUAL -->
    <div x-show="showTrxModal" 
         class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         role="dialog"
         aria-modal="true"
         aria-labelledby="trx-modal-title"
         x-cloak>
        <div x-show="showTrxModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md"
             @click="showTrxModal = false"
             aria-hidden="true"></div>

        <div x-show="showTrxModal"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative z-10 bg-white dark:bg-[#1C1C1E] w-full rounded-t-[28px] sm:rounded-3xl max-w-lg max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border border-slate-200/80 dark:border-white/10 text-xs">
            
            <div class="sm:hidden w-12 h-1.5 bg-slate-200 dark:bg-white/20 rounded-full mx-auto mt-3 mb-1"></div>

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-white/10">
                <div>
                    <h3 id="trx-modal-title" class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Pencatatan Mutasi Kas Operasional</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Catat penerimaan atau pengeluaran kas kecil & operasional</p>
                </div>
                <button type="button" 
                        @click="showTrxModal = false" 
                        class="btn-apple-gray !p-2 rounded-full"
                        aria-label="Tutup modal">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('finance.cash-bank.transactions.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label for="account_id" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Rekening Kas / Bank *</label>
                        <select name="account_id" id="account_id" required
                                class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all font-medium">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->name }} (Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Arus Kas Masuk / Keluar *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border border-slate-200 dark:border-white/15 flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                <input type="radio" name="type" value="IN" checked class="text-emerald-600 focus:ring-emerald-500">
                                <span class="font-bold text-emerald-700 dark:text-emerald-400">Kas Masuk (+)</span>
                            </label>
                            <label class="p-3 rounded-xl border border-slate-200 dark:border-white/15 flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                <input type="radio" name="type" value="OUT" class="text-rose-600 focus:ring-rose-500">
                                <span class="font-bold text-rose-700 dark:text-rose-400">Kas Keluar (-)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="amount" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nominal (IDR) *</label>
                        <input type="number" name="amount" id="amount" required min="1" step="0.01" placeholder="Contoh: 1500000"
                               class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all font-mono font-bold">
                    </div>

                    <div>
                        <label for="trx_date" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Transaksi *</label>
                        <input type="date" name="trx_date" id="trx_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all">
                    </div>

                    <div>
                        <label for="description" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Uraian / Keterangan Mutasi *</label>
                        <input type="text" name="description" id="description" required placeholder="Contoh: Pengisian kembali kas kecil operasional"
                               class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all">
                    </div>
                </div>

                <!-- Sticky Footer -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-[#252528] border-t border-slate-100 dark:border-white/10 flex items-center justify-end gap-3">
                    <button type="button" @click="showTrxModal = false"
                            class="btn-apple-gray">
                        Batal
                    </button>
                    <button type="submit"
                            class="btn-apple-filled">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Mutasi Kas</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: PINDAH BUKU / TRANSFER ANTAR-REKENING -->
    <div x-show="showTransferModal" 
         class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         role="dialog"
         aria-modal="true"
         aria-labelledby="transfer-modal-title"
         x-cloak>
        <div x-show="showTransferModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md"
             @click="showTransferModal = false"
             aria-hidden="true"></div>

        <div x-show="showTransferModal"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative z-10 bg-white dark:bg-[#1C1C1E] w-full rounded-t-[28px] sm:rounded-3xl max-w-lg max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border border-slate-200/80 dark:border-white/10 text-xs">
            
            <div class="sm:hidden w-12 h-1.5 bg-slate-200 dark:bg-white/20 rounded-full mx-auto mt-3 mb-1"></div>

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-white/10">
                <div>
                    <h3 id="transfer-modal-title" class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Pindah Buku Antar-Rekening Bank</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Transfer likuiditas kas operasional internal perusahaan</p>
                </div>
                <button type="button" 
                        @click="showTransferModal = false" 
                        class="btn-apple-gray !p-2 rounded-full"
                        aria-label="Tutup modal">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('finance.cash-bank.transfer') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label for="from_account_id" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Dari Rekening Asal *</label>
                        <select name="from_account_id" id="from_account_id" required
                                class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all font-medium">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->name }} (Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="to_account_id" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Ke Rekening Tujuan *</label>
                        <select name="to_account_id" id="to_account_id" required
                                class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all font-medium">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->name }} (Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="amount" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nominal Pindah Buku (IDR) *</label>
                        <input type="number" name="amount" id="amount" required min="1" step="0.01" placeholder="Contoh: 50000000"
                               class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all font-mono font-bold">
                    </div>

                    <div>
                        <label for="transfer_date" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Transfer *</label>
                        <input type="date" name="transfer_date" id="transfer_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all">
                    </div>

                    <div>
                        <label for="notes" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Pindah Buku</label>
                        <input type="text" name="notes" id="notes" placeholder="Contoh: Top-up saldo deposit MPN G3 Mandiri"
                               class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all">
                    </div>
                </div>

                <!-- Sticky Footer -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-[#252528] border-t border-slate-100 dark:border-white/10 flex items-center justify-end gap-3">
                    <button type="button" @click="showTransferModal = false"
                            class="btn-apple-gray">
                        Batal
                    </button>
                    <button type="submit"
                            class="btn-apple-tinted !bg-[#007AFF] !text-white hover:!bg-[#0062CC]">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        <span>Eksekusi Pindah Buku</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: TAMBAH REKENING BARU -->
    <div x-show="showAccountModal" 
         class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         role="dialog"
         aria-modal="true"
         aria-labelledby="account-modal-title"
         x-cloak>
        <div x-show="showAccountModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md"
             @click="showAccountModal = false"
             aria-hidden="true"></div>

        <div x-show="showAccountModal"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative z-10 bg-white dark:bg-[#1C1C1E] w-full rounded-t-[28px] sm:rounded-3xl max-w-lg max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border border-slate-200/80 dark:border-white/10 text-xs">
            
            <div class="sm:hidden w-12 h-1.5 bg-slate-200 dark:bg-white/20 rounded-full mx-auto mt-3 mb-1"></div>

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-white/10">
                <div>
                    <h3 id="account-modal-title" class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Buka Rekening Kas / Bank Baru</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Daftarkan rekening bank atau kas operasional baru ke sistem</p>
                </div>
                <button type="button" 
                        @click="showAccountModal = false" 
                        class="btn-apple-gray !p-2 rounded-full"
                        aria-label="Tutup modal">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('finance.cash-bank.accounts.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label for="name" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Rekening / Deskripsi *</label>
                        <input type="text" name="name" id="name" required placeholder="Contoh: Bank BNI Escrow Logistik"
                               class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all">
                    </div>

                    <div>
                        <label for="bank" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Bank / Institusi *</label>
                        <input type="text" name="bank" id="bank" required placeholder="Contoh: Bank Negara Indonesia (BNI)"
                               class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all">
                    </div>

                    <div>
                        <label for="account_no" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Rekening *</label>
                        <input type="text" name="account_no" id="account_no" required placeholder="Contoh: 0918-2831-928"
                               class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all font-mono">
                    </div>

                    <div>
                        <label for="initial_balance" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Saldo Awal Pembukaan (IDR)</label>
                        <input type="number" name="initial_balance" id="initial_balance" min="0" step="0.01" placeholder="0"
                               class="w-full px-3.5 py-2.5 border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white rounded-xl text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#007AFF] transition-all font-mono">
                    </div>
                </div>

                <!-- Sticky Footer -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-[#252528] border-t border-slate-100 dark:border-white/10 flex items-center justify-end gap-3">
                    <button type="button" @click="showAccountModal = false"
                            class="btn-apple-gray">
                        Batal
                    </button>
                    <button type="submit"
                            class="btn-apple-filled">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Buka Rekening</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
