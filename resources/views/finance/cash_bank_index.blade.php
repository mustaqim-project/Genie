@extends('layouts.app')

@section('title', 'Finance — Kas & Bank Operasional')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-12"
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

        <div class="flex items-center gap-2 flex-wrap">
            @can('finance.manage')
            <button type="button" @click="showTransferModal = true"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 text-xs font-bold transition-all shadow-2xs">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Pindah Buku (Transfer)</span>
            </button>

            <button type="button" @click="showTrxModal = true"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-xs">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Catat Mutasi Kas</span>
            </button>

            <button type="button" @click="showAccountModal = true"
                    class="p-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 transition-colors shadow-2xs"
                    title="Buka Rekening Baru">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            </button>
            @endcan
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs text-emerald-800">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-between text-xs text-rose-800">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span class="font-semibold">{{ session('error') }}</span>
        </div>
    </div>
    @endif

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
                        class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs">
                    + Buka Akun Baru
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

    <!-- MODAL 1: CATAT MUTASI KAS MANUAL -->
    <div x-show="showTrxModal" 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 text-xs"
             @click.outside="showTrxModal = false">
            
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Pencatatan Mutasi Kas Operasional</h3>
                <button type="button" @click="showTrxModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <form action="{{ route('finance.cash-bank.transactions.store') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="account_id" class="block font-semibold text-slate-700 mb-1">Rekening Kas / Bank *</label>
                        <select name="account_id" id="account_id" required
                                class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->name }} (Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Arus Kas Masuk / Keluar *</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="p-2.5 rounded-xl border border-slate-200 flex items-center gap-2 cursor-pointer hover:bg-slate-50">
                                <input type="radio" name="type" value="IN" checked class="text-emerald-600 focus:ring-emerald-500">
                                <span class="font-bold text-emerald-700">Kas Masuk (+)</span>
                            </label>
                            <label class="p-2.5 rounded-xl border border-slate-200 flex items-center gap-2 cursor-pointer hover:bg-slate-50">
                                <input type="radio" name="type" value="OUT" class="text-rose-600 focus:ring-rose-500">
                                <span class="font-bold text-rose-700">Kas Keluar (-)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="amount" class="block font-semibold text-slate-700 mb-1">Nominal (IDR) *</label>
                        <input type="number" name="amount" id="amount" required min="1" step="0.01" placeholder="Contoh: 1500000"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono font-bold">
                    </div>

                    <div>
                        <label for="trx_date" class="block font-semibold text-slate-700 mb-1">Tanggal Transaksi *</label>
                        <input type="date" name="trx_date" id="trx_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="description" class="block font-semibold text-slate-700 mb-1">Uraian / Keterangan Mutasi *</label>
                        <input type="text" name="description" id="description" required placeholder="Contoh: Pengisian kembali kas kecil operasional"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-2">
                    <button type="button" @click="showTrxModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition-colors shadow-xs">
                        Simpan Mutasi Kas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: PINDAH BUKU / TRANSFER ANTAR-REKENING -->
    <div x-show="showTransferModal" 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 text-xs"
             @click.outside="showTransferModal = false">
            
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Pindah Buku Antar-Rekening Bank</h3>
                <button type="button" @click="showTransferModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <form action="{{ route('finance.cash-bank.transfer') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="from_account_id" class="block font-semibold text-slate-700 mb-1">Dari Rekening Asal *</label>
                        <select name="from_account_id" id="from_account_id" required
                                class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
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
                                class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
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
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono font-bold">
                    </div>

                    <div>
                        <label for="transfer_date" class="block font-semibold text-slate-700 mb-1">Tanggal Transfer *</label>
                        <input type="date" name="transfer_date" id="transfer_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="notes" class="block font-semibold text-slate-700 mb-1">Catatan Pindah Buku</label>
                        <input type="text" name="notes" id="notes" placeholder="Contoh: Top-up saldo deposit MPN G3 Mandiri"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-2">
                    <button type="button" @click="showTransferModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold transition-colors shadow-xs">
                        Eksekusi Pindah Buku
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: TAMBAH REKENING BARU -->
    <div x-show="showAccountModal" 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 text-xs"
             @click.outside="showAccountModal = false">
            
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Buka Rekening Kas / Bank Baru</h3>
                <button type="button" @click="showAccountModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <form action="{{ route('finance.cash-bank.accounts.store') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block font-semibold text-slate-700 mb-1">Nama Rekening / Deskripsi *</label>
                        <input type="text" name="name" id="name" required placeholder="Contoh: Bank BNI Escrow Logistik"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="bank" class="block font-semibold text-slate-700 mb-1">Nama Bank / Institusi *</label>
                        <input type="text" name="bank" id="bank" required placeholder="Contoh: Bank Negara Indonesia (BNI)"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="account_no" class="block font-semibold text-slate-700 mb-1">Nomor Rekening *</label>
                        <input type="text" name="account_no" id="account_no" required placeholder="Contoh: 0918-2831-928"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label for="initial_balance" class="block font-semibold text-slate-700 mb-1">Saldo Awal Pembukaan (IDR)</label>
                        <input type="number" name="initial_balance" id="initial_balance" min="0" step="0.01" placeholder="0"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-2">
                    <button type="button" @click="showAccountModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition-colors shadow-xs">
                        Buka Rekening
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
