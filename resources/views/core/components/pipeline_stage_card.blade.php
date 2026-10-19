@php
    $stageIndex = $shipment->stage_index;
    $stageMeta = $shipment->current_stage_meta;
    $user = auth()->user();
    $userRoleCodes = $user ? $user->roles()->pluck('code')->toArray() : [];
    $isOwnerOrAdmin = in_array('R-OWN', $userRoleCodes) || in_array('R-ADM', $userRoleCodes);
    $isStageRole = in_array($stageMeta['role'], $userRoleCodes) || $isOwnerOrAdmin;
@endphp

<div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-6">
    <!-- 1. Header Kartu Tahap Aktif -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3 mb-5">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-sky-100 text-sky-800 border border-sky-300">
                    TAHAP {{ $stageIndex < 11 ? ($stageIndex + 1) : 11 }}
                </span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    {{ $stageMeta['title'] }}
                </h2>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Dikerjakan oleh: <strong class="text-slate-800">{{ $stageMeta['role_name'] }}</strong> &bull; Target SLA: <span class="font-bold text-slate-700 font-mono">{{ $stageMeta['sla_hours'] }} Jam</span>
            </p>
        </div>

        <div>
            @if($stageIndex < 11)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 border border-amber-200 text-amber-800 font-mono">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Menunggu Tindakan
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 border border-emerald-200 text-emerald-800 font-mono">
                    ✓ Selesai & Lunas
                </span>
            @endif
        </div>
    </div>

    <!-- 2. Lock Note Otorisasi / Peringatan PIC -->
    @if(!$isStageRole && $stageIndex < 11)
        <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center space-x-2.5">
                <span class="text-lg">🔒</span>
                <span>Tahap ini hanya dapat dikerjakan oleh <strong>{{ $stageMeta['role_name'] }}</strong>. Anda login dengan peran berbeda.</span>
            </div>
            @if($isOwnerOrAdmin)
                <span class="px-2 py-0.5 rounded bg-amber-200 text-amber-900 font-bold text-[10px] uppercase font-mono">Akses Pimpinan (Bypass)</span>
            @endif
        </div>
    @endif

    <!-- 3. Konten Panel Interaktif Sesuai Tahapan (Panel Prototype) -->
    <div>
        {{-- TAHAP 0: DOKUMEN (doc) --}}
        @if($stageIndex === 0)
            <div class="space-y-4">
                <p class="text-xs text-slate-600 leading-relaxed">
                    Verifikasi kelengkapan berkas fisik dari klien sebelum melangkah ke analisis HS Code.
                </p>
                <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden">
                    @foreach($shipment->documents as $doc)
                        <div class="p-3.5 flex items-center justify-between text-xs bg-slate-50/50 hover:bg-slate-50">
                            <div>
                                <span class="font-bold text-slate-800">{{ $doc->catatan ?? $doc->jenis_dokumen }}</span>
                                <span class="text-slate-400 block text-[11px] font-mono mt-0.5">{{ $doc->nama_file ?? 'Belum ada berkas terunggah' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono {{ $doc->status === 'VERIFIED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $doc->status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="pt-2 flex items-center gap-3">
                    @if($isStageRole)
                        <form action="{{ route('core.document.verify-all', $shipment->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-sm transition-all">
                                ✓ Verifikasi Lengkap & Lanjut ke HS Code &rarr;
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        {{-- TAHAP 1: HS & LARTAS (hs) --}}
        @elseif($stageIndex === 1)
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-sky-50/70 border border-sky-200 text-xs text-sky-900 leading-relaxed">
                    AI BTKI Engine telah memetakan uraian kargo packing list ke 8-digit pos tarif HS Code dan mendeteksi perizinan Lartas.
                </div>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="p-3">Pos Tarif (HS)</th>
                                <th class="p-3">Uraian Barang</th>
                                <th class="p-3">Kecocokan AI</th>
                                <th class="p-3">Status Lartas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($shipment->items as $item)
                                <tr>
                                    <td class="p-3 font-mono font-bold text-sky-700">{{ $item->kode_hs }}</td>
                                    <td class="p-3 text-slate-800">{{ $item->uraian_barang }}</td>
                                    <td class="p-3 font-mono">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ ($item->hs_confidence_score ?? 1.0) >= 0.85 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ round(($item->hs_confidence_score ?? 1.0) * 100) }}%
                                        </span>
                                    </td>
                                    <td class="p-3 font-mono text-[11px]">
                                        {{ $item->customsDocuments->isNotEmpty() ? 'Wajib Lartas (' . $item->customsDocuments->count() . ' Izin)' : 'Bebas Non-Lartas' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-slate-400">Belum ada item kargo terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pt-2">
                    @if($isStageRole)
                        <a href="{{ route('core.document.generate-pib-peb', $shipment->id) }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-sm transition-all">
                            Setujui HS Code & Susun Draf Pabean &rarr;
                        </a>
                    @endif
                </div>
            </div>

        {{-- TAHAP 2: DOKUMEN PABEAN (pib) --}}
        @elseif($stageIndex === 2)
            <div class="space-y-4">
                <p class="text-xs text-slate-600">
                    Pemeriksaan pra-pengiriman (*pre-flight validation*) sebelum diserahkan ke sistem Bea Cukai:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <span>Field Wajib & Kurs NDBM</span>
                        <span class="text-emerald-600 font-bold">✓ Lengkap</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <span>Validasi HS Code BTKI 2022</span>
                        <span class="text-emerald-600 font-bold">✓ Valid</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <span>Perizinan Tambahan (Lartas)</span>
                        <span class="text-emerald-600 font-bold">✓ Sesuai</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <span>Nol Blocking Error Skema</span>
                        <span class="text-emerald-600 font-bold">✓ Siap Kirim</span>
                    </div>
                </div>
                <div class="pt-2">
                    @if($isStageRole)
                        <form action="{{ route('core.document.submit-ceisa', $shipment->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md transition-all">
                                🚀 Approve & Kirim ke CEISA 4.0 Open API
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        {{-- TAHAP 3: BILLING PABEAN (bill) --}}
        @elseif($stageIndex === 3)
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900">
                    Nomor Pengajuan (AJU) terdaftar di CEISA: <strong class="font-mono text-sm">{{ $shipment->ceisaDocument?->nomor_aju ?? 'AJU-20261019-001' }}</strong>.
                    Kode Billing MPN G3: <strong class="font-mono text-sm text-sky-800">{{ $shipment->ceisaDocument?->kode_billing ?? 'BILL-9918231' }}</strong>.
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Bea Masuk</span>
                        <span class="font-bold font-mono">Rp {{ number_format($shipment->estimated_taxes['bea_masuk'] ?? 8400000, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">PPN Impor (11%)</span>
                        <span class="font-bold font-mono">Rp {{ number_format($shipment->estimated_taxes['ppn'] ?? 4100000, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">PPh Pasal 22</span>
                        <span class="font-bold font-mono">Rp {{ number_format($shipment->estimated_taxes['pph'] ?? 932000, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-slate-200 text-sm font-bold text-slate-900">
                        <span>Total Billing Pabean</span>
                        <span class="text-sky-700 font-mono">Rp {{ number_format($shipment->estimated_taxes['total_billing'] ?? 13432000, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="pt-2">
                    @if($isStageRole)
                        <form action="{{ route('core.do.send-email', $shipment->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all">
                                ✓ Konfirmasi Pembayaran Billing & Lanjut ke Tim DO &rarr;
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        {{-- TAHAP 4: PELAYARAN & DO (do) --}}
        @elseif($stageIndex === 4)
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Pelayaran</span>
                        <span class="font-bold">{{ $shipment->shippingVendor->name ?? 'MAERSK LINE' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Estimasi Tiba (ETA)</span>
                        <span class="font-bold font-mono">{{ $shipment->eta_date?->format('d/m/Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Free Time Tersisa</span>
                        <span class="font-bold font-mono {{ $shipment->is_demurrage_warning ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $shipment->days_left_free_time ?? 5 }} Hari
                        </span>
                    </div>
                </div>
                <div class="pt-2">
                    @if($isStageRole)
                        <form action="{{ route('core.do.release-do', $shipment->id) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="nomor_do" required placeholder="Nomor Rilis DO Pelayaran" class="px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            <input type="date" name="do_expired_at" required value="{{ now()->addDays(5)->format('Y-m-d') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                                Terbitkan DO & Teruskan ke Traffic &rarr;
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        {{-- TAHAP 5: KEDATANGAN & PENETAPAN JALUR (arr) --}}
        @elseif($stageIndex === 5)
            <div class="space-y-4">
                <p class="text-xs text-slate-600">
                    Prasyarat pabean terpenuhi: PIB terkirim, billing terbayar, DO rilis. Tetapkan respon jalur pabean resmi dari CEISA:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <form action="{{ route('core.traffic.customs-status', $shipment->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="channel" value="HIJAU">
                        <button type="submit" class="w-full p-4 rounded-xl border-2 border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 text-left transition-all group">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-black text-sm">JALUR HIJAU</span>
                                <span class="px-2 py-0.5 rounded bg-emerald-200 text-emerald-800 text-[10px] font-bold">Smart Skip</span>
                            </div>
                            <span class="text-[11px] text-emerald-700 block">Langsung rilis SPPB & siap Gate Out. Tahap Hold dilewati.</span>
                        </button>
                    </form>

                    <form action="{{ route('core.traffic.customs-status', $shipment->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="channel" value="KUNING">
                        <button type="submit" class="w-full p-4 rounded-xl border-2 border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-900 text-left transition-all">
                            <div class="font-black text-sm mb-1">JALUR KUNING</div>
                            <span class="text-[11px] text-amber-700 block">Pemeriksaan dokumen pabean oleh pemeriksa Bea Cukai.</span>
                        </button>
                    </form>

                    <form action="{{ route('core.traffic.customs-status', $shipment->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="channel" value="MERAH">
                        <button type="submit" class="w-full p-4 rounded-xl border-2 border-rose-300 bg-rose-50 hover:bg-rose-100 text-rose-900 text-left transition-all">
                            <div class="font-black text-sm mb-1">JALUR MERAH</div>
                            <span class="text-[11px] text-rose-700 block">Pemeriksaan fisik (behandle) kontainer di TPS pelabuhan.</span>
                        </button>
                    </form>
                </div>
            </div>

        {{-- TAHAP 6: HOLD & NOTUL (hold) --}}
        @elseif($stageIndex === 6)
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900">
                    <strong>Pemeriksaan Pabean Aktif:</strong> Jalur {{ $shipment->customs_channel ?? 'MERAH' }}. Selesaikan 4 langkah berikut sebelum SPPB dapat diterbitkan:
                </div>
                <div class="space-y-2 text-xs">
                    @php
                        $steps = $shipment->customs_hold_steps ?? [
                            ['name' => 'Kunjungan pemeriksa Bea Cukai', 'is_completed' => true],
                            ['name' => 'Dokumen tambahan / perizinan dipenuhi', 'is_completed' => true],
                            ['name' => 'Bongkar & pemeriksaan fisik kontainer', 'is_completed' => false],
                            ['name' => 'Pengembalian segel pelayaran resmi', 'is_completed' => false],
                        ];
                    @endphp
                    @foreach($steps as $sIdx => $st)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <span class="text-slate-800">{{ $sIdx + 1 }}. {{ $st['name'] }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono {{ ($st['is_completed'] ?? false) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                {{ ($st['is_completed'] ?? false) ? 'Selesai' : 'Belum' }}
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="pt-2">
                    <form action="{{ route('core.traffic.customs-status', $shipment->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="channel" value="HIJAU">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                            ✓ Tandai Pemeriksaan Selesai & Terbitkan SPPB
                        </button>
                    </form>
                </div>
            </div>

        {{-- TAHAP 7: SPPB & GATE OUT (gate) --}}
        @elseif($stageIndex === 7)
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                    <div class="flex justify-between"><span>Surat Persetujuan Pengeluaran Barang (SPPB)</span><span class="text-emerald-600 font-bold">✓ Terbit Resmi</span></div>
                    <div class="flex justify-between"><span>Status DO Pelayaran</span><span class="text-emerald-600 font-bold">✓ Rilis & Valid</span></div>
                    <div class="flex justify-between"><span>Biaya Terminal (Lift-on / Lift-off)</span><span class="text-emerald-600 font-bold">✓ Siap Bayar</span></div>
                </div>
                <div class="pt-2">
                    <form action="{{ route('core.traffic.gate-out', $shipment->id) }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="truck_plate_number" required placeholder="No. Polisi Truk (misal: B 9182 UY)" class="px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold transition-all shadow-sm">
                            Konfirmasi Gate Out Pelabuhan &rarr;
                        </button>
                    </form>
                </div>
            </div>

        {{-- TAHAP 8: PENGIRIMAN / TRUCKING (send) --}}
        @elseif($stageIndex === 8)
            <div class="space-y-4">
                <p class="text-xs text-slate-600">
                    Peringkat vendor pengiriman berdasarkan matriks tarif terendah dan skor performa SLA:
                </p>
                <form action="{{ route('core.traffic.deliver-pod', $shipment->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="receiver_name" value="Gudang Utama Klien">
                    <div class="space-y-2 text-xs">
                        <label class="p-3 rounded-xl border-2 border-sky-400 bg-sky-50/50 flex items-center justify-between cursor-pointer">
                            <div class="flex items-center space-x-2.5">
                                <input type="radio" name="selected_vendor" value="1" checked class="text-sky-600">
                                <div>
                                    <span class="font-bold text-slate-900">Surya Logistik Mandiri</span>
                                    <span class="text-slate-500 block text-[11px]">Tarif: Rp 2.400.000 &bull; Skor: 4.8 / 5.0</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-sky-200 text-sky-800 text-[10px] font-bold font-mono">Peringkat #1</span>
                        </label>

                        <label class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer">
                            <div class="flex items-center space-x-2.5">
                                <input type="radio" name="selected_vendor" value="2" class="text-sky-600">
                                <div>
                                    <span class="font-bold text-slate-900">Mitra Trans Nusantara</span>
                                    <span class="text-slate-500 block text-[11px]">Tarif: Rp 2.250.000 &bull; Skor: 4.2 / 5.0</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-[10px] font-bold font-mono">Peringkat #2</span>
                        </label>
                    </div>

                    <!-- Input Alasan Jika Bukan Peringkat 1 -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Alasan Pemilihan Vendor (Wajib jika bukan Peringkat 1)</label>
                        <textarea name="trucking_vendor_reason" rows="2" placeholder="Tulis alasan jika memilih armada selain peringkat 1..." class="w-full p-2.5 rounded-xl border border-slate-300 text-xs"></textarea>
                    </div>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold transition-all shadow-sm">
                        Berangkatkan Armada Truk Menuju Gudang Klien &rarr;
                    </button>
                </form>
            </div>

        {{-- TAHAP 9: POD & CLOSING (pod) --}}
        @elseif($stageIndex === 9 || $stageIndex === 10)
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-sky-50/80 border border-sky-200 text-xs text-sky-900 leading-relaxed">
                    <strong>Kebijakan Closing Operasional:</strong> Begitu bukti foto Surat Jalan bertanda terima (POD) diunggah, shipment resmi ditutup (*CLOSED*). Closing tidak menunggu pelunasan invoice klien.
                </div>
                <form action="{{ route('core.traffic.deliver-pod', $shipment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Penerima Barang di Gudang Klien *</label>
                        <input type="text" name="receiver_name" required placeholder="Contoh: Pak Hendra (Kepala Gudang)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Bukti Surat Jalan / POD Bertanda Terima Fisik *</label>
                        <input type="file" name="pod_file" required accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                    </div>

                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition-all">
                        ✓ Upload POD & Tutup Shipment (CLOSED)
                    </button>
                </form>
            </div>

        {{-- TAHAP 11: INVOICE KLIEN / SELESAI --}}
        @else
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-sm mb-0.5">Shipment Selesai Penuh (Closed & Invoiced)</h3>
                    <p class="text-emerald-700">Seluruh 11 tahapan pabean dan logistik telah selesai. Tagihan dan piutang dipantau di modul Keuangan.</p>
                </div>
                <span class="text-2xl">🎉</span>
            </div>
        @endif
    </div>
</div>
