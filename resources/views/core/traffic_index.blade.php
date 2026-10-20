@extends('layouts.app')

@section('title', 'Tim Traffic & Lapangan — Penjaluran, Gate Out & POD')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">

    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style from Cooca Core) -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black/70 dark:text-white/70 font-medium">Core Bisnis</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-semibold">Tim Traffic</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">
                    Traffic, Penjaluran & Lapangan Pelabuhan
                </h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                    Gate Out & POD
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5 leading-snug">
                Monitoring respon penjaluran CEISA (Hijau/Kuning/Merah), behandle, pengeluaran truk (Gate Out) dan POD serah terima.
            </p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('core.document.index') }}" class="btn-apple-gray w-full sm:w-auto">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Dokumen</span>
            </a>
        </div>
    </header>

    <!-- 2. 4 CUSTOMS CHANNEL FILTER PILLS (Apple HIG Status Indicators) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-[12px] font-semibold">
        <a href="{{ route('core.traffic.index', ['channel' => 'HIJAU']) }}" 
           class="p-3.5 rounded-[14px] border {{ request('channel') === 'HIJAU' ? 'bg-[#34C759]/15 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158] shadow-xs' : 'bg-white dark:bg-[#1C1C1E] border-black/5 dark:border-white/5 text-black/70 dark:text-white/70 hover:bg-black/[0.02] dark:hover:bg-white/[0.02]' }} flex items-center justify-between transition-all active:scale-[0.99]">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#34C759] animate-pulse"></span>
                <span>Jalur Hijau (SPPB)</span>
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] font-bold">Siap Gate Out</span>
        </a>
        <a href="{{ route('core.traffic.index', ['channel' => 'KUNING']) }}" 
           class="p-3.5 rounded-[14px] border {{ request('channel') === 'KUNING' ? 'bg-[#FF9500]/15 border-[#FF9500]/30 text-[#B25E00] dark:text-[#FF9F0A] shadow-xs' : 'bg-white dark:bg-[#1C1C1E] border-black/5 dark:border-white/5 text-black/70 dark:text-white/70 hover:bg-black/[0.02] dark:hover:bg-white/[0.02]' }} flex items-center justify-between transition-all active:scale-[0.99]">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#FF9500]"></span>
                <span>Jalur Kuning (SPJK)</span>
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#FF9500]/20 text-[#B25E00] dark:text-[#FF9F0A] font-bold">Periksa Dok</span>
        </a>
        <a href="{{ route('core.traffic.index', ['channel' => 'MERAH']) }}" 
           class="p-3.5 rounded-[14px] border {{ request('channel') === 'MERAH' ? 'bg-[#FF3B30]/15 border-[#FF3B30]/30 text-[#C41E17] dark:text-[#FF453A] shadow-xs' : 'bg-white dark:bg-[#1C1C1E] border-black/5 dark:border-white/5 text-black/70 dark:text-white/70 hover:bg-black/[0.02] dark:hover:bg-white/[0.02]' }} flex items-center justify-between transition-all active:scale-[0.99]">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#FF3B30]"></span>
                <span>Jalur Merah (SPJM)</span>
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#FF3B30]/20 text-[#C41E17] dark:text-[#FF453A] font-bold">Behandle Fisik</span>
        </a>
        <a href="{{ route('core.traffic.index', ['channel' => 'HOLD']) }}" 
           class="p-3.5 rounded-[14px] border {{ request('channel') === 'HOLD' ? 'bg-[#AF52DE]/15 border-[#AF52DE]/30 text-[#8E44AD] dark:text-[#BF5AF2] shadow-xs' : 'bg-white dark:bg-[#1C1C1E] border-black/5 dark:border-white/5 text-black/70 dark:text-white/70 hover:bg-black/[0.02] dark:hover:bg-white/[0.02]' }} flex items-center justify-between transition-all active:scale-[0.99]">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#AF52DE]"></span>
                <span>Sengketa / Notul</span>
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#AF52DE]/20 text-[#8E44AD] dark:text-[#BF5AF2] font-bold">SPTNP / NPD</span>
        </a>
    </div>

    <!-- 3. TABLE TRAFFIC WORKFLOW -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden flex flex-col">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="font-semibold text-[13px] text-black dark:text-white flex items-center space-x-2">
                <span>Antrian Pergerakan Kargo & Armada Truk</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-mono">{{ $shipments->total() }} Kargo</span>
            </div>
            <span class="text-[12px] text-black/45 dark:text-white/45 hidden sm:block">Pergerakan fisik TPS menuju pabrik klien</span>
        </div>

        <div class="table-responsive flex-1">
            <table class="w-full text-left text-[13px]">
                <thead class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10">
                    <tr>
                        <th class="py-3 px-4">No. Shipment & B/L</th>
                        <th class="py-3 px-4">Importir</th>
                        <th class="py-3 px-4">Jalur Pabean</th>
                        <th class="py-3 px-4">Status Rilis DO</th>
                        <th class="py-3 px-4">Status Pengiriman</th>
                        <th class="py-3 px-4 text-right">Aksi Lapangan & Trucking</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse($shipments as $shp)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 font-mono">
                                <span class="font-bold text-black dark:text-white">{{ $shp->shipment_number ?? $shp->project_no }}</span>
                                <div class="text-[11px] text-black/45 dark:text-white/45 font-sans mt-0.5">BL: {{ $shp->bl_number ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-black dark:text-white">{{ $shp->client->name ?? '-' }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45">Tujuan: Gudang Pabrik</div>
                            </td>
                            <td class="py-3 px-4">
                                @php $channel = $shp->customs_channel ?? 'HIJAU'; @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold font-mono
                                    {{ $channel === 'HIJAU' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : ($channel === 'MERAH' ? 'bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]' : 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]') }}">
                                    JALUR {{ $channel }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($shp->do_is_released)
                                    <span class="text-[#248A3D] dark:text-[#30D158] font-bold font-mono text-[11px] flex items-center gap-1">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span>RILIS ({{ $shp->do_number }})</span>
                                    </span>
                                @else
                                    <span class="text-[#FF3B30] font-bold font-mono text-[11px] flex items-center gap-1">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        <span>BELUM RILIS</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold font-mono
                                    {{ $shp->status === 'DELIVERED' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : ($shp->status === 'GATE_OUT' ? 'bg-[#007AFF]/15 text-[#007AFF]' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/70 dark:text-white/70') }}">
                                    {{ $shp->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                                @if($shp->status === 'GATE_OUT')
                                    <!-- Tombol Konfirmasi POD Modal Trigger -->
                                    <button type="button" 
                                            onclick="openPodModal('{{ $shp->id }}', '{{ $shp->shipment_number ?? $shp->project_no }}')"
                                            class="btn-apple-filled !bg-[#34C759] hover:!bg-[#248A3D] !h-8 !px-3 !text-[11px]">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span>Konfirmasi POD</span>
                                    </button>
                                @elseif($shp->status !== 'DELIVERED')
                                    <!-- Tombol Gate Out Modal Trigger -->
                                    <button type="button" 
                                            onclick="openGateOutModal('{{ $shp->id }}', '{{ $shp->shipment_number ?? $shp->project_no }}')"
                                            {{ !$shp->do_is_released ? 'disabled title="Wajib rilis DO terlebih dahulu"' : '' }}
                                            class="btn-apple-filled !h-8 !px-3 !text-[11px] {{ !$shp->do_is_released ? 'opacity-40 cursor-not-allowed' : '' }}">
                                        <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                        <span>Gate Out TPS</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[#248A3D] dark:text-[#30D158] font-semibold text-[11px] bg-[#34C759]/15 px-2.5 py-1 rounded-full border border-[#34C759]/25">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                        <span>Kargo Selesai (POD)</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                                <i data-lucide="truck" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                <div>Belum ada shipment pada antrian Tim Traffic.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-black/5 dark:border-white/5">
            {{ $shipments->links() }}
        </div>
    </div>

</div>

<!-- Modal Gate Out TPS (Apple HIG z-[200] Standalone Modal Architecture) -->
<div id="modal-gate-out" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="app-modal-dialog app-modal-dialog-md rounded-t-[20px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] transform transition-all">
        <div class="w-12 h-1.5 bg-black/10 dark:bg-white/10 rounded-full mx-auto mb-4 sm:hidden"></div>
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 mb-4">
            <h3 class="text-[16px] font-bold text-black dark:text-white">Eksekusi Pengeluaran Truk (Gate Out TPS)</h3>
            <button type="button" onclick="document.getElementById('modal-gate-out').classList.add('hidden')" class="p-1 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-gate-out" action="" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nomor Polisi Truk Trailer <span class="text-[#FF3B30]">*</span></label>
                <input type="text" name="truck_plate_number" required placeholder="Contoh: B 9281 UEM" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
            </div>

            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nama Pengemudi / Supir <span class="text-[#FF3B30]">*</span></label>
                <input type="text" name="driver_name" required placeholder="Contoh: Joko Susilo" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
            </div>

            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nomor Surat Jalan Ekspedisi <span class="text-[#FF3B30]">*</span></label>
                <input type="text" name="surat_jalan_number" required placeholder="Contoh: SJ-ATS-202610-0081" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
            </div>

            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-gate-out').classList.add('hidden')" class="btn-apple-gray !h-9 text-[12px]">Batal</button>
                <button type="submit" class="btn-apple-filled !h-9 text-[12px]">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Izin Gate Out</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal POD Delivery (Apple HIG z-[200] Standalone Modal Architecture) -->
<div id="modal-pod" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="app-modal-dialog app-modal-dialog-md rounded-t-[20px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] transform transition-all">
        <div class="w-12 h-1.5 bg-black/10 dark:bg-white/10 rounded-full mx-auto mb-4 sm:hidden"></div>
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 mb-4">
            <h3 class="text-[16px] font-bold text-black dark:text-white">Konfirmasi Bukti Terima (POD)</h3>
            <button type="button" onclick="document.getElementById('modal-pod').classList.add('hidden')" class="p-1 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-pod" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nama Penerima Barang di Gudang Klien <span class="text-[#FF3B30]">*</span></label>
                <input type="text" name="receiver_name" required placeholder="Contoh: Bpk. Bambang Pamungkas (Warehouse Head)" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
            </div>

            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Unggah Foto Surat Jalan Basah & Serah Terima (POD)</label>
                <input type="file" name="pod_file" class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-2 file:py-1.5 file:px-3 file:rounded-[8px] file:border-0 file:bg-black/[0.05] dark:file:bg-white/[0.08] file:text-black dark:file:text-white">
            </div>

            <p class="text-[11px] text-black/50 dark:text-white/50 bg-black/[0.02] dark:bg-white/[0.02] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5">
                *Konfirmasi serah terima kargo akan otomatis mengubah status menjadi <strong>DELIVERED</strong> dan menerbitkan draf <strong>Invoice Penagihan Klien</strong> pada modul Finance & Accounting.
            </p>

            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-pod').classList.add('hidden')" class="btn-apple-gray !h-9 text-[12px]">Batal</button>
                <button type="submit" class="btn-apple-filled !bg-[#34C759] hover:!bg-[#248A3D] !h-9 text-[12px]">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Simpan POD & Terbitkan Invoice</span>
                </button>
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
    if (window.lucide) window.lucide.createIcons();
}

function openPodModal(shipmentId, shipmentNumber) {
    const modal = document.getElementById('modal-pod');
    const form = document.getElementById('form-pod');
    form.action = `/core/traffic/${shipmentId}/deliver-pod`;
    modal.classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
}
</script>
@endsection
