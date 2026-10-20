@extends('layouts.app')

@section('title', 'Tim DO — Tracing & Delivery Order Pelayaran')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / Core Bisnis / <span class="text-slate-800">Tim DO</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Manajemen Delivery Order & Pelayaran</h1>
            <p class="text-xs text-slate-500 mt-0.5">Otomasi kirim email invoice ke pelayaran, tracking batas bebas (free time), dan penebusan DO</p>
        </div>
    </div>

    <!-- Alert Early Warning Demurrage -->
    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs mb-6 flex items-start space-x-3">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <strong class="font-bold">Monitoring Free Time Kontainer:</strong> Kontainer pelayaran wajib ditebus DO-nya dan dikembalikan sebelum batas free time berakhir (maksimal 7–14 hari) untuk menghindari biaya denda *demurrage/detention*.
        </div>
    </div>

    <!-- Table DO Workflow -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <span class="font-bold text-xs text-slate-800">Antrian Shipment Tim DO</span>
            <div class="flex items-center space-x-2 text-xs">
                <form method="GET" action="{{ route('core.do.index') }}" class="flex items-center space-x-2">
                    <select name="vendor_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs">
                        <option value="">-- Semua Maskapai Pelayaran --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">No. Shipment & B/L</th>
                        <th class="py-3 px-4">Maskapai Pelayaran</th>
                        <th class="py-3 px-4">Kapal & Voyage</th>
                        <th class="py-3 px-4">Status DO</th>
                        <th class="py-3 px-4">Batas Free Time</th>
                        <th class="py-3 px-4 text-right">Aksi Operasional</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipments as $shp)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">
                                <span class="font-bold text-slate-800">{{ $shp->shipment_number ?? $shp->project_no }}</span>
                                <div class="text-[10px] text-slate-400">BL: {{ $shp->bl_number ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800">{{ $shp->shippingVendor->name ?? 'MAERSK LINE' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $shp->shippingVendor->email ?? 'id.import@maersk.com' }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                <div>{{ $shp->vessel_name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">Voy: {{ $shp->voyage_number ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @if($shp->do_is_released)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        &check; DO RELEASED
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-0.5">No: {{ $shp->do_number }}</div>
                                @elseif($shp->status === 'DO_REQUESTED')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        EMAIL TERKIRIM
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        MENUNGGU PERMINTAAN
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800">{{ $shp->days_until_free_time_expires ?? 7 }} Hari Tersisa</div>
                                <div class="text-[10px] text-slate-400">s/d {{ now()->addDays(7)->format('d/m/Y') }}</div>
                            </td>
                            <td class="py-3 px-4 text-right space-x-1.5">
                                <!-- Otomasi Kirim Email -->
                                <form action="{{ route('core.do.send-email', $shp->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 font-semibold text-[11px] transition-colors" title="Kirim Email Invoice ke Pelayaran">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span>Email Pelayaran</span>
                                    </button>
                                </form>

                                <!-- Tombol Rilis DO Modal Trigger -->
                                <button onclick="openReleaseModal('{{ $shp->id }}', '{{ $shp->shipment_number ?? $shp->project_no }}')"
                                        class="px-2.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold text-[11px] transition-all shadow-xs active:scale-95">
                                    Rilis DO
                                </button>

                                <!-- Cetak Surat Permohonan DO -->
                                <a href="{{ route('pdf.do-request', $shp->id) }}" target="_blank"
                                   class="px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold text-[11px] transition-colors">
                                    PDF Surat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-800">Belum Ada Antrian Shipment Tim DO</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Shipment akan muncul otomatis setelah SPPB / Billing pabean terbit dari Tim Document.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $shipments->links() }}
        </div>
    </div>

</div>

<!-- Modal Release DO (Apple HIG z-[200] Standalone Modal Architecture) -->
<div id="modal-release-do" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-slate-950/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-t-[24px] sm:rounded-2xl border border-slate-200/80 shadow-2xl max-w-md w-full p-6 text-xs transform transition-all">
        <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-4 sm:hidden"></div>
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-sm font-bold text-slate-900">Konfirmasi Rilis Delivery Order (DO)</h3>
            <button onclick="document.getElementById('modal-release-do').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="form-release-do" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nomor DO Resmi dari Pelayaran <span class="text-rose-500">*</span></label>
                <input type="text" name="do_number" required placeholder="Contoh: DO-MAE-202610-0921" class="w-full px-3 py-2 rounded-xl border border-slate-200">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Masa Berlaku DO Berakhir (Valid Until) <span class="text-rose-500">*</span></label>
                <input type="date" name="do_expired_at" required value="{{ now()->addDays(7)->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Unggah Berkas DO Asli (PDF/Scan)</label>
                <input type="file" name="do_file" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-release-do').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">Simpan & Rilis DO</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReleaseModal(shipmentId, shipmentNumber) {
    const modal = document.getElementById('modal-release-do');
    const form = document.getElementById('form-release-do');
    form.action = `/core/do/${shipmentId}/release-do`;
    modal.classList.remove('hidden');
}
</script>
@endsection
