@extends('layouts.app')

@section('title', 'Tim DO — Tracing & Delivery Order Pelayaran')

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
                <span class="text-black dark:text-white font-semibold">Tim DO</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">
                    Manajemen Delivery Order & Pelayaran
                </h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/10 text-[#FF9500]">
                    Penebusan DO & Free Time
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5 leading-snug">
                Otomasi kirim email invoice ke pelayaran, tracking batas bebas (free time), dan penebusan DO pelayaran.
            </p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('core.document.index') }}" class="btn-apple-gray w-full sm:w-auto">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Dokumen</span>
            </a>
        </div>
    </header>

    <!-- 2. ALERT EARLY WARNING DEMURRAGE (Apple HIG Banner Style) -->
    <div class="p-4 rounded-[14px] bg-[#FF9500]/12 border border-[#FF9500]/20 text-[13px] text-[#B25E00] dark:text-[#FF9F0A] flex items-start space-x-3 shadow-2xs backdrop-blur-md">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-[#FF9500] shrink-0 mt-0.5"></i>
        <div>
            <strong class="font-bold">Monitoring Free Time Kontainer:</strong> Kontainer pelayaran wajib ditebus DO-nya dan dikembalikan sebelum batas free time berakhir (maksimal 7–14 hari) untuk menghindari biaya denda *demurrage/detention*.
        </div>
    </div>

    <!-- 3. TABLE DO WORKFLOW -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden flex flex-col">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="font-semibold text-[13px] text-black dark:text-white flex items-center space-x-2">
                <span>Antrian Shipment Tim DO</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-mono">{{ $shipments->total() }} Data</span>
            </div>
            <div class="flex items-center space-x-2 text-[12px]">
                <form method="GET" action="{{ route('core.do.index') }}" class="flex items-center space-x-2">
                    <select name="vendor_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-black dark:text-white text-[12px] focus:outline-none focus:border-[#007AFF]">
                        <option value="">-- Semua Maskapai Pelayaran --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="table-responsive flex-1">
            <table class="w-full text-left text-[13px]">
                <thead class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10">
                    <tr>
                        <th class="py-3 px-4">No. Shipment & B/L</th>
                        <th class="py-3 px-4">Maskapai Pelayaran</th>
                        <th class="py-3 px-4">Kapal & Voyage</th>
                        <th class="py-3 px-4">Status DO</th>
                        <th class="py-3 px-4">Batas Free Time</th>
                        <th class="py-3 px-4 text-right">Aksi Operasional</th>
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
                                <div class="font-semibold text-black dark:text-white">{{ $shp->shippingVendor->name ?? 'MAERSK LINE' }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 font-mono">{{ $shp->shippingVendor->email ?? 'id.import@maersk.com' }}</div>
                            </td>
                            <td class="py-3 px-4 text-black/80 dark:text-white/80">
                                <div>{{ $shp->vessel_name ?? '-' }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45">Voy: {{ $shp->voyage_number ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @if($shp->do_is_released)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold font-mono bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        &check; DO RELEASED
                                    </span>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 font-mono mt-0.5">No: {{ $shp->do_number }}</div>
                                @elseif($shp->status === 'DO_REQUESTED')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold font-mono bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]">
                                        EMAIL TERKIRIM
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold font-mono bg-black/[0.06] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                                        MENUNGGU PERMINTAAN
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-black dark:text-white tabular-nums">{{ $shp->days_until_free_time_expires ?? 7 }} Hari Tersisa</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">s/d {{ now()->addDays(7)->format('d/m/Y') }}</div>
                            </td>
                            <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                                @if(!$shp->do_is_released)
                                    <!-- Otomasi Kirim Email -->
                                    <form action="{{ route('core.do.send-email', $shp->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="btn-apple-tinted !h-8 !px-2.5 !text-[11px]" title="Kirim Email Permintaan Invoice ke Pelayaran">
                                            <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                                            <span>Email Pelayaran</span>
                                        </button>
                                    </form>

                                    <!-- Tombol Rilis DO Modal Trigger -->
                                    <button type="button" 
                                            onclick="openReleaseModal('{{ $shp->id }}', '{{ $shp->shipment_number ?? $shp->project_no }}')"
                                            class="btn-apple-filled !h-8 !px-3 !text-[11px]">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                        <span>Rilis DO</span>
                                    </button>
                                @endif

                                <!-- Cetak Surat Permohonan DO -->
                                <a href="{{ route('pdf.do-request', $shp->id) }}" target="_blank"
                                   class="btn-apple-gray !h-8 !px-2.5 !text-[11px]" title="Lihat / Cetak Surat Permohonan DO">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                    <span>PDF Surat</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                                <i data-lucide="clipboard-check" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                <p class="font-medium">Belum Ada Antrian Shipment Tim DO</p>
                                <p class="text-[11px] mt-0.5 opacity-70">Shipment akan muncul otomatis setelah SPPB / Billing pabean terbit dari Tim Document.</p>
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

<!-- Modal Release DO (Apple HIG z-[200] Standalone Modal Architecture) -->
<div id="modal-release-do" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="app-modal-dialog app-modal-dialog-md rounded-t-[20px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] transform transition-all">
        <div class="w-12 h-1.5 bg-black/10 dark:bg-white/10 rounded-full mx-auto mb-4 sm:hidden"></div>
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 mb-4">
            <h3 class="text-[16px] font-bold text-black dark:text-white">Konfirmasi Rilis Delivery Order (DO)</h3>
            <button type="button" onclick="document.getElementById('modal-release-do').classList.add('hidden')" class="p-1 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-release-do" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nomor DO Resmi dari Pelayaran <span class="text-[#FF3B30]">*</span></label>
                <input type="text" name="do_number" required placeholder="Contoh: DO-MAE-202610-0921" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
            </div>

            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Masa Berlaku DO Berakhir (Valid Until) <span class="text-[#FF3B30]">*</span></label>
                <input type="date" name="do_expired_at" required value="{{ now()->addDays(7)->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
            </div>

            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Unggah Berkas DO Asli (PDF/Scan)</label>
                <input type="file" name="do_file" class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-2 file:py-1.5 file:px-3 file:rounded-[8px] file:border-0 file:bg-black/[0.05] dark:file:bg-white/[0.08] file:text-black dark:file:text-white">
            </div>

            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-release-do').classList.add('hidden')" class="btn-apple-gray !h-9 text-[12px]">Batal</button>
                <button type="submit" class="btn-apple-filled !h-9 text-[12px]">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan & Rilis DO</span>
                </button>
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
    if (window.lucide) window.lucide.createIcons();
}
</script>
@endsection
