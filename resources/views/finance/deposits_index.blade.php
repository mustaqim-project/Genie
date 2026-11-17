@extends('layouts.app')

@section('title', 'Buku Kas Deposit Klien — Finance')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">
    <!-- Header Page -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7] transition-colors flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <a href="{{ route('finance.index') }}" class="hover:text-[#0284c7] transition-colors">Finance</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <span class="text-slate-800 dark:text-white font-semibold">Deposit Klien</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Buku Besar Deposit Klien</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pemantauan saldo titipan dana talangan pabean, jaminan kontainer, dan pembayaran uang muka klien (MoM Poin 10)</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-create-deposit').classList.remove('hidden')" class="btn-apple-filled !h-9 text-xs inline-flex items-center gap-1.5 !bg-[#0284c7] hover:!bg-[#0066D6]">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>+ Catat Deposit Baru</span>
            </button>
        </div>
    </div>

    <!-- Apple HIG Bento Grid KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Saldo Deposit Aktif</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400">
                Rp {{ number_format($totalSaldoAktif, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Dana titipan siap potong invoice / talangan</div>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Akumulasi Deposit Masuk</span>
                <span class="p-2 rounded-xl bg-sky-500/10 text-[#0284c7]">
                    <i data-lucide="arrow-down-circle" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white">
                Rp {{ number_format($totalDepositMasuk, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Total transfer dana masuk dari klien</div>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Deposit Terpakai</span>
                <span class="p-2 rounded-xl bg-indigo-500/10 text-indigo-600">
                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-indigo-600 dark:text-indigo-400">
                Rp {{ number_format($totalDepositTerpakai, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Pemotongan invoice pabean &amp; operasional</div>
        </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-black/[0.05] dark:border-white/[0.05] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-[#0284c7]"></i>
                <span>Riwayat Setoran Deposit Klien</span>
            </h3>

            <form method="GET" action="{{ route('finance.deposits.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <select name="client_id" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-slate-800 dark:text-white">
                    <option value="">-- Semua Klien --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ $clientId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-slate-800 dark:text-white">
                    <option value="">-- Semua Status --</option>
                    <option value="CONFIRMED" {{ $status == 'CONFIRMED' ? 'selected' : '' }}>CONFIRMED (Ada Saldo)</option>
                    <option value="DEPLETED" {{ $status == 'DEPLETED' ? 'selected' : '' }}>DEPLETED (Habis)</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-white/[0.02] text-slate-500 font-semibold border-b border-black/[0.05] dark:border-white/[0.05] text-[11px]">
                    <tr>
                        <th class="py-3 px-4">No. Transaksi</th>
                        <th class="py-3 px-4">Klien Importir / Eksportir</th>
                        <th class="py-3 px-4">Tanggal Masuk</th>
                        <th class="py-3 px-4 text-right">Nominal Masuk</th>
                        <th class="py-3 px-4 text-right">Saldo Tersisa</th>
                        <th class="py-3 px-4">Metode &amp; Ref</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.05]">
                    @forelse($deposits as $d)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-[#0284c7]">{{ $d->deposit_no }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                                {{ $d->client->name ?? '-' }}
                                <div class="text-[10px] text-slate-400 font-normal">NPWP: {{ $d->client->npwp ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ $d->tanggal->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                Rp {{ number_format($d->nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold {{ $d->saldo_sisa > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                Rp {{ number_format($d->saldo_sisa, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                <div>{{ $d->metode_pembayaran }} {{ $d->bank_name ? "({$d->bank_name})" : '' }}</div>
                                <div class="font-mono text-[10px] text-slate-400">{{ $d->nomor_referensi_bank ?: '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono {{ $d->status === 'CONFIRMED' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-slate-500/10 text-slate-500' }}">
                                    {{ $d->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right space-x-1">
                                @if($d->saldo_sisa > 0)
                                    <button type="button" onclick="openDeductModal('{{ $d->id }}', '{{ $d->deposit_no }}', '{{ $d->client->name }}', {{ $d->saldo_sisa }})" class="btn-apple-gray !h-7 !px-2.5 text-[11px] text-[#0284c7]">
                                        <span>Potong Saldo</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 italic">Belum ada transaksi deposit klien yang dicatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-black/[0.05] dark:border-white/[0.05]">
            {{ $deposits->links() }}
        </div>
    </div>
</div>

<!-- Modal Catat Setoran Deposit Baru -->
<div id="modal-create-deposit" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="app-modal-dialog app-modal-dialog-md rounded-t-[20px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] max-w-lg w-full">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 mb-4">
            <div>
                <h3 class="text-base font-bold text-black dark:text-white">+ Catat Setoran Deposit Klien</h3>
                <p class="text-xs text-slate-400">Penerimaan dana titipan pabean / jaminan kontainer</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-create-deposit').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-black dark:hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('finance.deposits.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block font-semibold text-xs mb-1">Klien <span class="text-rose-500">*</span></label>
                <select name="client_id" required class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs">
                    <option value="">-- Pilih Klien --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-xs mb-1">Tanggal Masuk <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-xs mb-1">Nominal Deposit (IDR) <span class="text-rose-500">*</span></label>
                    <input type="number" step="1000" name="nominal" required placeholder="Contoh: 50000000" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] font-mono font-bold text-xs">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-xs mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                    <select name="metode_pembayaran" required class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs">
                        <option value="BANK_TRANSFER">Transfer Bank</option>
                        <option value="CASH">Tunai / Cash</option>
                        <option value="GIRO">Bilyet Giro / Cek</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-xs mb-1">Bank Pengirim</label>
                    <input type="text" name="bank_name" placeholder="Misal: BCA / Mandiri" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs">
                </div>
            </div>
            <div>
                <label class="block font-semibold text-xs mb-1">No. Referensi / Berita Transfer</label>
                <input type="text" name="nomor_referensi_bank" placeholder="Contoh: TRF-BCA-202611-0091" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] font-mono text-xs">
            </div>
            <div>
                <label class="block font-semibold text-xs mb-1">Catatan Peruntukan</label>
                <input type="text" name="catatan" placeholder="Contoh: Titipan dana Bea Masuk &amp; PPN Impor SHP-202611-0001" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs">
            </div>
            <div class="pt-3 border-t border-black/5 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-create-deposit').classList.add('hidden')" class="btn-apple-gray !h-9 text-xs">Batal</button>
                <button type="submit" class="btn-apple-filled !h-9 text-xs !bg-[#0284c7]">Simpan Deposit</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Potong Saldo Deposit -->
<div id="modal-deduct-deposit" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="app-modal-dialog app-modal-dialog-md rounded-t-[20px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] max-w-lg w-full">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 mb-4">
            <div>
                <h3 class="text-base font-bold text-black dark:text-white">Potong Saldo Deposit Klien</h3>
                <p class="text-xs text-slate-400" id="deduct_subtitle">Alokasi pembayaran invoice / biaya</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-deduct-deposit').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-black dark:hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('finance.deposits.deduct') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="client_deposit_id" id="deduct_deposit_id">
            <div>
                <label class="block font-semibold text-xs mb-1">Maksimum Saldo Tersedia</label>
                <input type="text" id="deduct_max_display" readonly class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-slate-50 dark:bg-white/5 font-mono font-bold text-xs text-emerald-600">
            </div>
            <div>
                <label class="block font-semibold text-xs mb-1">Nominal Pemotongan (IDR) <span class="text-rose-500">*</span></label>
                <input type="number" step="1" name="nominal" id="deduct_nominal" required class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] font-mono font-bold text-xs">
            </div>
            <div>
                <label class="block font-semibold text-xs mb-1">Keterangan Pemotongan <span class="text-rose-500">*</span></label>
                <input type="text" name="keterangan" required placeholder="Contoh: Pembayaran Invoice P19 INV-202611-0001" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs">
            </div>
            <div class="pt-3 border-t border-black/5 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-deduct-deposit').classList.add('hidden')" class="btn-apple-gray !h-9 text-xs">Batal</button>
                <button type="submit" class="btn-apple-filled !h-9 text-xs !bg-indigo-600">Konfirmasi Potong Saldo</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeductModal(depositId, depositNo, clientName, saldo) {
    document.getElementById('deduct_deposit_id').value = depositId;
    document.getElementById('deduct_subtitle').textContent = `Deposit ${depositNo} — Klien: ${clientName}`;
    document.getElementById('deduct_max_display').value = `Rp ${saldo.toLocaleString('id-ID')}`;
    document.getElementById('deduct_nominal').max = saldo;
    document.getElementById('deduct_nominal').value = saldo;
    document.getElementById('modal-deduct-deposit').classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
}
</script>
@endsection
