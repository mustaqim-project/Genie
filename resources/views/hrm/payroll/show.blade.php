@extends('layouts.app')

@section('title', 'Rincian Batch Penggajian ' . $payrollRun->period . ' — GENIE ERP')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 pb-24">

    <!-- Top Navigation Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('hrm.index') }}" class="hover:underline text-slate-500">HRM & Kepegawaian</a>
                <span class="text-slate-400 mx-1">/</span>
                <a href="{{ route('hrm.index', ['tab' => 'payroll']) }}" class="hover:underline text-slate-500">Payroll</a>
                <span class="text-slate-400 mx-1">/</span>
                <span class="text-slate-800 font-semibold">{{ $payrollRun->period }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Batch Penggajian {{ $payrollRun->period }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold
                    {{ $payrollRun->status === 'DISBURSED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($payrollRun->status === 'APPROVED' ? 'bg-sky-100 text-sky-800 border border-sky-200' : 'bg-slate-200 text-slate-700') }}">
                    {{ $payrollRun->status }}
                </span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-2 flex-wrap">
            <a href="{{ route('hrm.payrolls.export-bank', $payrollRun->id) }}"
               class="px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-700 text-xs font-bold transition flex items-center space-x-1.5 shadow-xs"
               title="Format Transfer BCA KlikBCA Bisnis">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Format CSV Bank BCA</span>
            </a>

            <a href="{{ route('hrm.payrolls.export-excel', $payrollRun->id) }}"
               class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export Excel / CSV</span>
            </a>

            @if($payrollRun->status === 'DRAFT')
                <form method="POST" action="{{ route('hrm.payroll.approve', $payrollRun->id) }}" onsubmit="return confirm('Setujui batch payroll ini?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-sm shadow-sky-600/20 transition">
                        Setujui Batch (Approve)
                    </button>
                </form>
            @elseif($payrollRun->status === 'APPROVED')
                <form method="POST" action="{{ route('hrm.payroll.disburse', $payrollRun->id) }}" onsubmit="return confirm('Cairkan dan selesaikan pemotongan kasbon?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm shadow-emerald-600/20 transition">
                        Cairkan Payroll (Disburse)
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Bento Grid Summary Cards -->
    @php
        $totalGross = $payrollRun->payslips->sum('gross');
        $totalDeductions = $payrollRun->payslips->sum('deductions');
        $totalNet = $payrollRun->payslips->sum('net');
        $totalStaff = $payrollRun->payslips->count();
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Karyawan</span>
            <div class="text-2xl font-bold font-mono text-slate-900 mt-1">{{ $totalStaff }} Orang</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Staf aktif terdaftar</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Gaji Kotor (Gross)</span>
            <div class="text-2xl font-bold font-mono text-slate-900 mt-1">Rp {{ number_format($totalGross, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Termasuk tunjangan & lembur</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-500">Total Potongan Resmi</span>
            <div class="text-2xl font-bold font-mono text-rose-600 mt-1">Rp {{ number_format($totalDeductions, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">BPJS TK, Kesehatan, Kasbon & PPh 21</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-emerald-200 bg-emerald-50/30 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Total Take Home Pay (Net)</span>
            <div class="text-2xl font-extrabold font-mono text-emerald-700 mt-1">Rp {{ number_format($totalNet, 0, ',', '.') }}</div>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Total dana yang dicairkan</p>
        </div>
    </div>

    <!-- Tabel Daftar Slip Gaji Per Karyawan -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
        <h2 class="text-base font-bold text-slate-900">Daftar Rincian Penggajian Karyawan</h2>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Nama Pegawai & NIK</th>
                        <th class="px-4 py-3">Rekening Bank</th>
                        <th class="px-4 py-3">Gaji Kotor</th>
                        <th class="px-4 py-3">Potongan</th>
                        <th class="px-4 py-3">Take Home Pay (THP)</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($payrollRun->payslips as $slip)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $slip->employee?->full_name }}</div>
                                <div class="text-[10px] font-mono text-slate-400">{{ $slip->employee?->nik }} &bull; {{ $slip->employee?->position }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $slip->employee?->bank_name ?: 'BCA' }}</div>
                                <div class="font-mono text-slate-500">{{ $slip->employee?->bank_account_number ?: '-' }}</div>
                            </td>
                            <td class="px-4 py-3 font-mono font-medium text-slate-800">
                                Rp {{ number_format((float) $slip->gross, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 font-mono text-rose-600 font-medium">
                                Rp {{ number_format((float) $slip->deductions, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 font-mono font-bold text-emerald-700 text-sm">
                                Rp {{ number_format((float) $slip->net, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('hrm.payslips.print', $slip->id) }}" target="_blank"
                                   class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                                    <svg class="w-3.5 h-3.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    Cetak Slip Gaji
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
