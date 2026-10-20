@extends('layouts.app')

@section('title', 'Buat Draf Penggajian (Payroll Wizard) — GENIE ERP')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 pb-24"
     x-data="{
        includeThr: false,
        totalNetEstimate: 0,
        employees: {{ Js::from($employees) }},
        formatRupiah(num) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(num || 0);
        }
     }">

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('hrm.index') }}" class="hover:underline text-slate-500">HRM & Kepegawaian</a>
                <span class="text-slate-400 mx-1">/</span>
                <span class="text-slate-800 font-semibold">Buat Draf Penggajian</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Kalkulasi Batch Penggajian Baru</h1>
            <p class="text-xs text-slate-500 mt-1">
                Hitung gaji pokok, tunjangan operasional forwarder, insentif PPJK, potongan BPJS TK/Kes, dan cicilan kasbon.
            </p>
        </div>

        <a href="{{ route('hrm.index', ['tab' => 'payroll']) }}"
           class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
            &larr; Kembali ke HRM Hub
        </a>
    </div>

    <!-- Main Wizard Form -->
    <form method="POST" action="{{ route('hrm.payrolls.store') }}" class="space-y-6">
        @csrf

        <!-- Periode & Opsi Tambahan -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Konfigurasi Periode & Bonus</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Bulan Periode</label>
                    <select name="month" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-800">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tahun Periode</label>
                    <select name="year" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-800">
                        @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="flex items-center pt-5">
                    <label class="relative flex items-center space-x-2.5 cursor-pointer">
                        <input type="checkbox" name="include_thr" value="1" x-model="includeThr"
                               class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <span class="text-xs font-bold text-slate-800">Sertakan Tunjangan Hari Raya (THR)</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Tabel Kompensasi Karyawan -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Rincian Kompensasi & Potongan Karyawan</h2>
                    <p class="text-xs text-slate-500">Periksa nominal gaji pokok, insentif lembur, dan potongan kasbon sebelum disimpan.</p>
                </div>
                <span class="text-xs font-bold text-slate-700 px-3 py-1 rounded-xl bg-slate-100">
                    {{ count($employees) }} Karyawan Aktif
                </span>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Nama Pegawai & NIK</th>
                            <th class="px-3 py-3">Gaji Pokok</th>
                            <th class="px-3 py-3">Uang Makan</th>
                            <th class="px-3 py-3">Transport</th>
                            <th class="px-3 py-3">Insentif PPJK</th>
                            <th class="px-3 py-3">Lembur Lapangan</th>
                            <th class="px-3 py-3">Potongan Kasbon</th>
                            <th class="px-3 py-3">BPJS (2% + 1%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($employees as $emp)
                            @php
                                $base = (float) $emp->base_salary;
                                $meal = 1200000;
                                $transport = 800000;
                                $customs = ($emp->team === 'DOCUMENT' || $emp->team === 'FIELD') ? 1500000 : 0;
                                $advance = $activeLoans[$emp->id] ?? null;
                                $kasbonDeduct = 0;
                                if ($advance && $advance->installments > 0) {
                                    $kasbonDeduct = min(round($advance->amount / $advance->installments), (float) $advance->balance);
                                }
                                $bpjsTotal = round($base * 0.03);
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900">{{ $emp->full_name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">{{ $emp->nik }} &bull; {{ $emp->position }}</div>
                                </td>
                                <td class="px-3 py-3">
                                    <input type="number" name="employees[{{ $emp->id }}][base_salary]" value="{{ $base }}"
                                           class="w-28 px-2 py-1.5 rounded-lg border border-slate-200 font-mono font-bold text-slate-800 text-xs">
                                </td>
                                <td class="px-3 py-3">
                                    <input type="number" name="employees[{{ $emp->id }}][allowance_meal]" value="{{ $meal }}"
                                           class="w-24 px-2 py-1.5 rounded-lg border border-slate-200 font-mono text-slate-700 text-xs">
                                </td>
                                <td class="px-3 py-3">
                                    <input type="number" name="employees[{{ $emp->id }}][allowance_transport]" value="{{ $transport }}"
                                           class="w-24 px-2 py-1.5 rounded-lg border border-slate-200 font-mono text-slate-700 text-xs">
                                </td>
                                <td class="px-3 py-3">
                                    <input type="number" name="employees[{{ $emp->id }}][allowance_customs]" value="{{ $customs }}"
                                           class="w-24 px-2 py-1.5 rounded-lg border border-slate-200 font-mono text-slate-700 text-xs">
                                </td>
                                <td class="px-3 py-3">
                                    <input type="number" name="employees[{{ $emp->id }}][overtime_pay]" value="0"
                                           class="w-24 px-2 py-1.5 rounded-lg border border-slate-200 font-mono text-slate-700 text-xs">
                                </td>
                                <td class="px-3 py-3">
                                    <input type="number" name="employees[{{ $emp->id }}][deduct_kasbon]" value="{{ $kasbonDeduct }}"
                                           class="w-24 px-2 py-1.5 rounded-lg border border-slate-200 font-mono text-rose-600 text-xs">
                                </td>
                                <td class="px-3 py-3 font-mono font-semibold text-slate-600">
                                    Rp {{ number_format($bpjsTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Submit Button Card -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100 flex-wrap gap-3">
                <span class="text-xs text-slate-500">
                    Sistem otomatis menghitung Take Home Pay bersih dan menerbitkan slip gaji digital dalam status <b>DRAFT</b>.
                </span>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('hrm.index', ['tab' => 'payroll']) }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md shadow-slate-900/20 active:scale-[0.98]">
                        Simpan & Generate Batch Payroll &rarr;
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
