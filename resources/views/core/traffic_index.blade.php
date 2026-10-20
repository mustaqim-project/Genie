@extends('layouts.app')

@section('title', 'Tim Traffic & Lapangan — Penjaluran, Gate Out & POD')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / Core Bisnis / <span class="text-slate-800">Tim Traffic</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Traffic, Penjaluran & Lapangan Pelabuhan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Monitoring respon penjaluran CEISA (Hijau/Kuning/Merah), behandle, pengeluaran truk (Gate Out) dan POD</p>
        </div>
    </div>

    <!-- Customs Channel Status Tabs -->
    <!-- 4 Customs Channel Filter Pills (Apple HIG Minimalist Indicators) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6 text-xs font-semibold">
        <a href="{{ route('core.traffic.index', ['channel' => 'HIJAU']) }}" 
           class="p-3.5 rounded-xl border {{ request('channel') === 'HIJAU' ? 'bg-emerald-50 border-emerald-300 text-emerald-800 shadow-xs' : 'bg-white border-slate-200/80 text-slate-700 hover:bg-slate-50' }} flex items-center justify-between transition-all active:scale-[0.99]">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Jalur Hijau (SPPB)</span>
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100/80 text-emerald-800 font-bold">Siap Gate Out</span>
        </a>
        <a href="{{ route('core.traffic.index', ['channel' => 'KUNING']) }}" 
           class="p-3.5 rounded-xl border {{ request('channel') === 'KUNING' ? 'bg-amber-50 border-amber-300 text-amber-800 shadow-xs' : 'bg-white border-slate-200/80 text-slate-700 hover:bg-slate-50' }} flex items-center justify-between transition-all active:scale-[0.99]">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span>Jalur Kuning (SPJK)</span>
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100/80 text-amber-800 font-bold">Periksa Dok</span>
        </a>
        <a href="{{ route('core.traffic.index', ['channel' => 'MERAH']) }}" 
           class="p-3.5 rounded-xl border {{ request('channel') === 'MERAH' ? 'bg-rose-50 border-rose-300 text-rose-800 shadow-xs' : 'bg-white border-slate-200/80 text-slate-700 hover:bg-slate-50' }} flex items-center justify-between transition-all active:scale-[0.99]">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span>Jalur Merah (SPJM)</span>
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-rose-100/80 text-rose-800 font-bold">Behandle Fisik</span>
        </a>
        <a href="{{ route('core.traffic.index', ['channel' => 'HOLD']) }}" 
           class="p-3.5 rounded-xl border {{ request('channel') === 'HOLD' ? 'bg-purple-50 border-purple-300 text-purple-800 shadow-xs' : 'bg-white border-slate-200/80 text-slate-700 hover:bg-slate-50' }} flex items-center justify-between transition-all active:scale-[0.99]">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                <span>Sengketa / Notul</span>
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-100/80 text-purple-800 font-bold">SPTNP / NPD</span>
        </a>
    </div>

    <!-- Table Traffic Workflow -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <span class="font-bold text-xs text-slate-800">Antrian Pergerakan Kargo & Armada Truk</span>
            <span class="text-xs text-slate-400">Total: {{ $shipments->total() }} kargo</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">No. Shipment & B/L</th>
                        <th class="py-3 px-4">Importir</th>
                        <th class="py-3 px-4">Jalur Pabean</th>
                        <th class="py-3 px-4">Status Rilis DO</th>
                        <th class="py-3 px-4">Status Pengiriman</th>
                        <th class="py-3 px-4 text-right">Aksi Lapangan & Trucking</th>
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
                                <div class="font-semibold text-slate-800">{{ $shp->client->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">Tujuan: Gudang Pabrik</div>
                            </td>
                            <td class="py-3 px-4">
                                @php $channel = $shp->customs_channel ?? 'HIJAU'; @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                    {{ $channel === 'HIJAU' ? 'bg-emerald-100 text-emerald-800' : ($channel === 'MERAH' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    JALUR {{ $channel }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($shp->do_is_released)
                                    <span class="text-emerald-700 font-bold">&check; RILIS (No: {{ $shp->do_number }})</span>
                                @else
                                    <span class="text-rose-600 font-bold">&times; BELUM RILIS</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono
                                    {{ $shp->status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800' : ($shp->status === 'GATE_OUT' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $shp->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                                @if($shp->status === 'GATE_OUT')
                                    <!-- Tombol Konfirmasi POD Modal Trigger -->
                                    <button onclick="openPodModal('{{ $shp->id }}', '{{ $shp->shipment_number ?? $shp->project_no }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs transition-all active:scale-95 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Konfirmasi POD</span>
                                    </button>
                                @elseif($shp->status !== 'DELIVERED')
                                    <!-- Tombol Gate Out Modal Trigger -->
                                    <button onclick="openGateOutModal('{{ $shp->id }}', '{{ $shp->shipment_number ?? $shp->project_no }}')"
                                            {{ !$shp->do_is_released ? 'disabled title="Wajib rilis DO terlebih dahulu"' : '' }}
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-[11px] transition-all shadow-xs active:scale-95
                                                {{ $shp->do_is_released ? 'bg-slate-900 hover:bg-slate-800 text-white cursor-pointer' : 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed' }}">
                                        <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                        <span>Gate Out TPS</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-[11px] bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Kargo Selesai (POD)</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada shipment pada antrian Tim Traffic.
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

<!-- Modal Gate Out TPS (Apple HIG z-[200] Standalone Modal Architecture) -->
<div id="modal-gate-out" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-slate-950/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-t-[24px] sm:rounded-2xl border border-slate-200/80 shadow-2xl max-w-md w-full p-6 text-xs transform transition-all">
        <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-4 sm:hidden"></div>
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-sm font-bold text-slate-900">Eksekusi Pengeluaran Truk (Gate Out TPS)</h3>
            <button onclick="document.getElementById('modal-gate-out').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="form-gate-out" action="" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nomor Polisi Truk Trailer <span class="text-rose-500">*</span></label>
                <input type="text" name="truck_plate_number" required placeholder="Contoh: B 9281 UEM" class="w-full px-3 py-2 rounded-xl border border-slate-200">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Pengemudi / Supir <span class="text-rose-500">*</span></label>
                <input type="text" name="driver_name" required placeholder="Contoh: Joko Susilo" class="w-full px-3 py-2 rounded-xl border border-slate-200">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nomor Surat Jalan Ekspedisi <span class="text-rose-500">*</span></label>
                <input type="text" name="surat_jalan_number" required placeholder="Contoh: SJ-ATS-202610-0081" class="w-full px-3 py-2 rounded-xl border border-slate-200">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-gate-out').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition-all shadow-xs active:scale-95">Izin Gate Out</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal POD Delivery (Apple HIG z-[200] Standalone Modal Architecture) -->
<div id="modal-pod" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-slate-950/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-t-[24px] sm:rounded-2xl border border-slate-200/80 shadow-2xl max-w-md w-full p-6 text-xs transform transition-all">
        <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-4 sm:hidden"></div>
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-sm font-bold text-slate-900">Konfirmasi Bukti Terima (Proof of Delivery / POD)</h3>
            <button onclick="document.getElementById('modal-pod').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="form-pod" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Penerima Barang di Gudang Klien <span class="text-rose-500">*</span></label>
                <input type="text" name="receiver_name" required placeholder="Contoh: Bpk. Bambang Pamungkas (Warehouse Head)" class="w-full px-3 py-2 rounded-xl border border-slate-200">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Unggah Foto Surat Jalan Basah & Serah Terima (POD)</label>
                <input type="file" name="pod_file" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700">
            </div>

            <p class="text-[10px] text-slate-400 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                *Konfirmasi serah terima kargo akan otomatis mengubah status menjadi <strong>DELIVERED</strong> dan menerbitkan draf <strong>Invoice Penagihan Klien</strong> pada modul Finance & Accounting.
            </p>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-pod').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-all shadow-xs active:scale-95">Simpan POD & Terbitkan Invoice</button>
            </div>
        </form>
    </div>
</div>

<script>
function openGateOutModal(shipmentId, shipmentNumber) {
    const modal = document.getElementById('modal-gate-out');
    const form = document.getElementById('form-gate-out');
    form.action = `/core/traffic/${shipmentId}/gate-out`;
    modal.classList.remove('hidden');
}

function openPodModal(shipmentId, shipmentNumber) {
    const modal = document.getElementById('modal-pod');
    const form = document.getElementById('form-pod');
    form.action = `/core/traffic/${shipmentId}/deliver-pod`;
    modal.classList.remove('hidden');
}
</script>
@endsection
