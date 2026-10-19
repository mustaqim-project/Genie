@extends('layouts.app')

@section('title', 'Tim DO - Tracing & Delivery Order')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / Core Bisnis / <span class="text-slate-800">Tim DO</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Delivery Order & Pelayaran (Shipping Line)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Tracing kapal, perhitungan free time kontainer, permintaan invoice pelayaran, dan penebusan DO</p>
        </div>
        <div>
            <button class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Kirim Email Invoice Pelayaran</span>
            </button>
        </div>
    </div>

    <!-- Alert Demurrage Warning -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-start space-x-3 text-xs text-amber-900">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <strong class="font-bold">Monitoring Free Time Kontainer:</strong> Kontainer pelayaran wajib dikembalikan sebelum batas free time berakhir (maksimal 7–14 hari sejak kapal sandar) untuk mencegah denda *demurrage/detention* pelayaran.
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <span class="font-bold text-xs text-slate-800">Daftar Pengurusan DO Pelayaran</span>
            <span class="text-xs text-slate-400">Total: {{ $shipments->total() }} shipment impor</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Nomor Aju & B/L</th>
                        <th class="py-3 px-4">Kapal & Voyage</th>
                        <th class="py-3 px-4">Pelabuhan Muat</th>
                        <th class="py-3 px-4">Status DO</th>
                        <th class="py-3 px-4">Sisa Free Time</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipments as $shp)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">
                                <span class="font-bold text-slate-800">{{ $shp->nomor_aju }}</span>
                                <div class="text-[10px] text-slate-400">Supplier: {{ $shp->nama_mitra_luar_negeri }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                <div class="font-semibold">{{ $shp->raw_payload['pengangkut'][0]['namaPengangkut'] ?? 'COSCO SHIPPING 021E' }}</div>
                                <div class="text-[10px] text-slate-400">Voyage: {{ $shp->raw_payload['pengangkut'][0]['nomorPengangkut'] ?? '021E' }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-600">
                                {{ $shp->raw_payload['header']['kodePelabuhanMuat'] ?? 'CNSHA' }} &rarr; {{ $shp->kode_kantor }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">
                                    DO_ISSUED (LUNAS)
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-emerald-600">
                                5 Hari Lagi
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('bc20.show', $shp->id) }}" class="text-xs font-semibold text-brand-600 hover:underline">
                                    Detail DO &rarr;
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
