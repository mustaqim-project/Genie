@extends('layouts.app')

@section('title', 'Kunjungan Sales Lapangan (Geofencing 200m)')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">F18 CRM Field</span>
                <span class="text-xs text-neutral-400">Genie ERP v2.0</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white mt-1">Kunjungan Sales Lapangan (Geofencing 200m)</h1>
            <p class="text-sm text-neutral-400">Verifikasi lokasi nyata, foto bukti pertemuan, dan riwayat negosiasi klien importir/eksportir.</p>
        </div>

        <div>
            <button onclick="openCheckInModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-emerald-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Check-in Lokasi Klien
            </button>
        </div>
    </div>

    <!-- Bento Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-neutral-900/60 border border-neutral-800 rounded-2xl p-4">
            <span class="text-xs text-neutral-400">Total Kunjungan</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="bg-neutral-900/60 border border-neutral-800 rounded-2xl p-4">
            <span class="text-xs text-neutral-400">Kunjungan Hari Ini</span>
            <div class="text-2xl font-bold text-emerald-400 mt-1">{{ number_format($stats['today']) }}</div>
        </div>
        <div class="bg-neutral-900/60 border border-neutral-800 rounded-2xl p-4">
            <span class="text-xs text-neutral-400">Di Luar Radius (>200m)</span>
            <div class="text-2xl font-bold text-amber-400 mt-1">{{ number_format($stats['out_of_radius']) }}</div>
        </div>
        <div class="bg-neutral-900/60 border border-neutral-800 rounded-2xl p-4">
            <span class="text-xs text-neutral-400">Deal Closed (Closing)</span>
            <div class="text-2xl font-bold text-indigo-400 mt-1">{{ number_format($stats['deal_closed']) }}</div>
        </div>
    </div>

    <!-- Tabel Riwayat Kunjungan -->
    <div class="bg-neutral-900/60 border border-neutral-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-neutral-300">
                <thead class="bg-neutral-950 text-neutral-400 font-semibold border-b border-neutral-800">
                    <tr>
                        <th class="px-4 py-3">Waktu & Sales</th>
                        <th class="px-4 py-3">Klien Target</th>
                        <th class="px-4 py-3">Jarak Geofence</th>
                        <th class="px-4 py-3">Hasil Negosiasi</th>
                        <th class="px-4 py-3">Catatan / Alasan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/60">
                    @forelse($visits as $v)
                    <tr class="hover:bg-neutral-800/30 transition">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-white">{{ $v->salesUser?->name }}</div>
                            <div class="text-[11px] text-neutral-500 font-mono">{{ $v->check_in_time->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-white">{{ $v->client?->name }}</span>
                            <div class="text-[11px] text-neutral-400">{{ $v->client?->address ?? 'Lokasi belum diset' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($v->is_out_of_radius)
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                    {{ $v->distance_meters }} m (Luar Radius)
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    {{ $v->distance_meters }} m (Valid)
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold
                                @if($v->result_status === 'DEAL_CLOSE') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                @elseif($v->result_status === 'INTERESTED') bg-indigo-500/10 text-indigo-400 border border-indigo-500/20
                                @elseif($v->result_status === 'REJECTED') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                @else bg-neutral-800 text-neutral-300 @endif">
                                {{ $v->result_status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 max-w-xs truncate">
                            @if($v->out_of_radius_reason)
                                <div class="text-[11px] text-amber-400 font-medium">[Alasan Geofence]: {{ $v->out_of_radius_reason }}</div>
                            @endif
                            <div class="text-[11px] text-neutral-400 truncate">{{ $v->notes ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if(!$v->check_out_time)
                                <button onclick="openCheckOutModal('{{ $v->id }}')" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold">
                                    Check-out
                                </button>
                            @else
                                <span class="text-[11px] text-neutral-500 font-mono">Selesai ({{ $v->check_out_time->format('H:i') }})</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-neutral-500">Belum ada catatan kunjungan sales lapangan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-neutral-800">
            {{ $visits->links() }}
        </div>
    </div>
</div>

<!-- Modal Check-In Geofence -->
<div id="modalCheckIn" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm hidden p-4">
    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="font-bold text-white text-base">Check-in Kunjungan Sales</h3>
        <p class="text-xs text-neutral-400 mt-1">Sistem otomatis mendeteksi GPS perangkat untuk memvalidasi batas radius 200 meter.</p>

        <form action="{{ route('crm.sales-visits.check-in') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-neutral-300 mb-1">Pilih Klien *</label>
                <select name="client_id" required class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-2 text-xs text-white">
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-neutral-300 mb-1">Latitude</label>
                    <input type="text" id="geoLat" name="latitude" required readonly class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-2 text-xs text-neutral-400 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-300 mb-1">Longitude</label>
                    <input type="text" id="geoLng" name="longitude" required readonly class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-2 text-xs text-neutral-400 font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-300 mb-1">Alasan Check-in Luar Radius (Jika > 200m)</label>
                <input type="text" name="out_of_radius_reason" placeholder="Contoh: Bertemu di kafe/kantor cabang klien..." class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-2 text-xs text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-300 mb-1">Catatan Awal</label>
                <textarea name="notes" rows="2" placeholder="Tujuan agenda pertemuan hari ini..." class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-2 text-xs text-white"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-800">
                <button type="button" onclick="document.getElementById('modalCheckIn').classList.add('hidden')" class="px-4 py-2 bg-neutral-800 text-neutral-300 rounded-xl text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold">Simpan Check-in</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCheckInModal() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            document.getElementById('geoLat').value = pos.coords.latitude.toFixed(7);
            document.getElementById('geoLng').value = pos.coords.longitude.toFixed(7);
            document.getElementById('modalCheckIn').classList.remove('hidden');
        }, function(err) {
            alert('Gagal mendapatkan lokasi GPS perangkat. Pastikan izin lokasi aktif.');
            document.getElementById('geoLat').value = '-6.2088000';
            document.getElementById('geoLng').value = '106.8456000';
            document.getElementById('modalCheckIn').classList.remove('hidden');
        });
    } else {
        alert('Geolocation tidak didukung oleh browser Anda.');
    }
}
</script>
@endsection
