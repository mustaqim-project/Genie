@extends('layouts.app')

@section('title', 'Tim Traffic & Lapangan - Gate Out & Behandle')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / Core Bisnis / <span class="text-slate-800">Tim Traffic</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Traffic, Penjaluran & Lapangan (Port Operations)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penetapan jalur CEISA, pemeriksaan fisik lapangan (behandle), hold/notul, gate out TPS pelabuhan, dan trucking</p>
        </div>
        <div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-purple-600 mr-2"></span>
                Mobile App Sync Ready (R-FLD)
            </span>
        </div>
    </div>

    <!-- Quick Status Filter Pills -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-xs">
            <span class="text-slate-400 font-medium block">Jalur Hijau</span>
            <span class="font-bold text-emerald-600 text-lg">2 Kontainer</span>
            <span class="text-[10px] text-slate-500 block">Langsung SPPB</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-xs">
            <span class="text-slate-400 font-medium block">Jalur Kuning (SPJK)</span>
            <span class="font-bold text-amber-600 text-lg">0 Kontainer</span>
            <span class="text-[10px] text-slate-500 block">Dokumen Pelengkap</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-xs">
            <span class="text-slate-400 font-medium block">Jalur Merah (SPJM)</span>
            <span class="font-bold text-rose-600 text-lg">0 Kontainer</span>
            <span class="text-[10px] text-slate-500 block">Jadwal Behandle</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-xs">
            <span class="text-slate-400 font-medium block">Gate Out Siap</span>
            <span class="font-bold text-blue-600 text-lg">2 Kontainer</span>
            <span class="text-[10px] text-slate-500 block">SPPB + DO Valid</span>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <span class="font-bold text-xs text-slate-800">Daftar Kontainer Lapangan Pelabuhan</span>
            <span class="text-xs text-slate-400">Total: {{ $shipments->total() }} kargo</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Nomor Aju</th>
                        <th class="py-3 px-4">Tipe Dokumen</th>
                        <th class="py-3 px-4">Penjaluran Pabean</th>
                        <th class="py-3 px-4">Dokumen Rilis</th>
                        <th class="py-3 px-4">Status Pengeluaran</th>
                        <th class="py-3 px-4 text-right">Aksi Lapangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipments as $shp)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">
                                <span class="font-bold text-slate-800">{{ $shp->nomor_aju }}</span>
                                <div class="text-[10px] text-slate-400">{{ $shp->nama_mitra_luar_negeri }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $shp->jenis_dokumen === '20' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }}">
                                    {{ $shp->jenis_dokumen_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Jalur {{ $shp->jalur ?? 'HIJAU' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-700">
                                {{ $shp->nomor_sppb_npe ?? 'Menunggu Rilis' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                    SIAP GATE OUT
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ $shp->jenis_dokumen === '20' ? route('bc20.show', $shp->id) : route('bc30.show', $shp->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                                    Proses Truk &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada shipment terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
