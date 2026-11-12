@extends('layouts.app')

@section('title', 'Review Layar Terbagi — ' . $document->file?->original_name)

@section('content')
<div x-data="splitScreenReview()" class="max-w-[1560px] mx-auto px-4 sm:px-6 space-y-4 pt-3 pb-20">

    <!-- Header Toolbar -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 px-4 py-3 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('core.import.show', $document->batch_id) }}" class="p-1 rounded-[8px] hover:bg-black/5 dark:hover:bg-white/10 text-black/60 dark:text-white/60 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-[16px] font-bold text-black dark:text-white">
                        {{ $document->file?->original_name }}
                    </h1>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                        {{ $document->document_type }}
                    </span>
                    <span class="text-[11px] text-black/45 dark:text-white/45">
                        Batch: {{ $document->batch?->batch_no }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Kanan -->
        <div class="flex items-center gap-2">
            <button type="button" @click="showJumpModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] text-[12px] font-semibold bg-[#FF9500]/10 hover:bg-[#FF9500]/20 text-[#FF9500] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                Mulai dari Tahap (F16)
            </button>

            <form action="{{ route('core.import.apply', $document->id) }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-[10px] text-[12px] font-semibold bg-[#34C759] hover:bg-[#2DB34E] text-white shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Terapkan ke Proyek
                </button>
            </form>
        </div>
    </header>

    <!-- Split-Screen Grid Layout (50% Kiri : 50% Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

        <!-- PANEL KIRI: Form Ekstraksi Otomatis (6 Kolom) -->
        <div class="lg:col-span-6 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-4 max-h-[82vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div>
                    <h3 class="text-[14px] font-bold text-black dark:text-white">Formulir Hasil Ekstraksi Otomatis</h3>
                    <p class="text-[11px] text-black/45 dark:text-white/45">Klik field untuk melihat posisi koordinat teks pada PDF sumber.</p>
                </div>
                <div class="flex items-center gap-1.5 text-[11px]">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#34C759]/10 text-[#34C759] font-medium">&ge;90% Tinggi</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#FF9500]/10 text-[#FF9500] font-medium">70-89% Sedang</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] font-medium">Diedit</span>
                </div>
            </div>

            <!-- Daftar Field Form Terdeteksi -->
            <div class="space-y-3.5">
                @forelse($document->fields as $field)
                @php
                    $score = (float) $field->confidence_score;
                    $badgeClass = $field->status === 'EDITED'
                        ? 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20'
                        : ($score >= 90
                            ? 'bg-[#34C759]/10 text-[#34C759] border-[#34C759]/20'
                            : ($score >= 70
                                ? 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20'
                                : 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20'));
                @endphp
                <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/10 transition hover:border-[#007AFF]/50"
                     @click="setActiveField('{{ $field->id }}', {{ json_encode($field->bounding_box) }})">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-[12px] font-semibold text-black/80 dark:text-white/80">
                            {{ $field->field_label }}
                        </label>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold border {{ $badgeClass }}">
                            {{ $field->status === 'EDITED' ? 'Koreksi Staf' : number_format($score, 1) . '%' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="text"
                               x-ref="field_{{ $field->id }}"
                               value="{{ $field->effective_value }}"
                               @focus="setActiveField('{{ $field->id }}', {{ json_encode($field->bounding_box) }})"
                               class="w-full rounded-[8px] bg-white dark:bg-black/20 border border-black/10 dark:border-white/15 px-3 py-1.5 text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        
                        <button type="button"
                                @click="saveField('{{ $field->id }}')"
                                class="px-2.5 py-1.5 rounded-[8px] bg-black/5 dark:bg-white/10 hover:bg-[#007AFF] hover:text-white text-[12px] font-medium transition shrink-0">
                            Simpan
                        </button>
                    </div>

                    @if($field->status === 'EDITED')
                    <span class="block text-[10px] text-black/40 dark:text-white/40 mt-1">
                        Nilai Asli Ekstraksi: <span class="line-through">{{ $field->original_value }}</span>
                    </span>
                    @endif
                </div>
                @empty
                <div class="py-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                    Tidak ada field terdeteksi. Silakan periksa berkas sumber.
                </div>
                @endforelse
            </div>

            <!-- Validasi Silang Dokumen -->
            @if($document->validations->isNotEmpty())
            <div class="pt-3 border-t border-black/5 dark:border-white/10">
                <h4 class="text-[12px] font-bold text-black/70 dark:text-white/70 mb-2">Hasil Evaluasi Validasi Silang (V01–V10)</h4>
                <div class="space-y-1.5">
                    @foreach($document->validations as $v)
                    <div class="flex items-center gap-2 text-[12px] {{ $v->is_passed ? 'text-[#34C759]' : 'text-[#FF3B30]' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            @if($v->is_passed)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            @endif
                        </svg>
                        <span>[{{ $v->rule_code }}] {{ $v->message }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- PANEL KANAN: Penampil PDF Sumber Interaktif (6 Kolom) -->
        <div class="lg:col-span-6 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-3 h-[82vh] flex flex-col">
            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10 shrink-0">
                <div class="flex items-center gap-2">
                    <h3 class="text-[14px] font-bold text-black dark:text-white">Penampil Berkas Dokumen Sumber</h3>
                    <span class="text-[11px] text-black/45 dark:text-white/45">Halaman 1</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ Storage::disk('public')->url($document->file?->file_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[12px] font-semibold text-[#007AFF] hover:underline">
                        Buka di Tab Baru
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                    </a>
                </div>
            </div>

            <!-- PDF Viewer Frame dengan Highlight Indicator -->
            <div class="relative flex-1 rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 bg-[#525659]">
                <iframe src="{{ Storage::disk('public')->url($document->file?->file_path) }}#toolbar=0" class="w-full h-full border-0"></iframe>

                <!-- Active Bounding Box Highlight Overlay Indicator -->
                <div x-show="activeBoundingBox"
                     class="absolute pointer-events-none border-2 border-[#007AFF] bg-[#007AFF]/20 rounded transition-all duration-300"
                     :style="getBoxStyle()">
                </div>
            </div>
        </div>

    </div>

    <!-- MODAL F16: WIZARD "MULAI DARI TAHAP" (ENTRY STAGE WIZARD) -->
    <div x-show="showJumpModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;">
        <div class="w-full max-w-lg rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/15 p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold text-[12px]">F16</span>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Lompat Tahap & Mulai Proyek</h3>
                </div>
                <button type="button" @click="showJumpModal = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('core.import.jump', $document->shipment_id ?? 'new') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Pilih Tahap Mulai Aktif (Entry Stage)</label>
                    <select name="target_stage" required class="w-full rounded-[10px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/15 px-3 py-2 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        @foreach($prerequisites as $p)
                            <option value="{{ $p->stage_code }}">
                                {{ $p->stage_name }} {{ $p->is_approval_required ? '(Wajib Approval ' . $p->approver_role . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Tahap-tahap sebelumnya akan ditandai otomatis sebagai <span class="font-semibold text-black dark:text-white">Data Bawaan (IMPORTED)</span> dan dikecualikan dari SLA.</p>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Perlakuan Keuangan Data Historis</label>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2.5 p-2.5 rounded-[10px] border border-black/10 dark:border-white/10 cursor-pointer hover:bg-black/[0.02]">
                            <input type="radio" name="financial_treatment" value="OPENING_BALANCE" checked class="mt-0.5 text-[#007AFF]">
                            <div>
                                <span class="text-[13px] font-semibold block text-black dark:text-white">Saldo Awal (Tanpa Jurnal Baru)</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Dipakai jika transaksi lama sudah dibukukan di sistem sebelumnya. Mencegah jurnal ganda.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 p-2.5 rounded-[10px] border border-black/10 dark:border-white/10 cursor-pointer hover:bg-black/[0.02]">
                            <input type="radio" name="financial_treatment" value="HISTORICAL_POSTING" class="mt-0.5 text-[#007AFF]">
                            <div>
                                <span class="text-[13px] font-semibold block text-black dark:text-white">Posting Historis (Buat Jurnal Otomatis)</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Menerbitkan jurnal umum bertanggal lampau jika periode pembukuan masih berstatus terbuka.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Justifikasi / Alasan Lompat Tahap</label>
                    <textarea name="reason" rows="2" required placeholder="Contoh: Pengapalan lama telah selesai pabean di luar sistem, kini masuk proses DO pelayaran..." class="w-full rounded-[10px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/15 px-3 py-2 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showJumpModal = false" class="px-4 py-2 rounded-[10px] text-[13px] font-medium bg-black/5 dark:bg-white/10 text-black dark:text-white">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-[10px] text-[13px] font-semibold bg-[#FF9500] hover:bg-[#E08500] text-white">Mulai dari Tahap Ini</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function splitScreenReview() {
    return {
        showJumpModal: false,
        activeFieldId: null,
        activeBoundingBox: null,

        setActiveField(fieldId, box) {
            this.activeFieldId = fieldId;
            this.activeBoundingBox = box;
        },

        getBoxStyle() {
            if (!this.activeBoundingBox) return '';
            const b = this.activeBoundingBox;
            return `left: ${b.x}px; top: ${b.y}px; width: ${b.width}px; height: ${b.height}px;`;
        },

        async saveField(fieldId) {
            const inputEl = this.$refs['field_' + fieldId];
            if (!inputEl) return;

            const val = inputEl.value;

            try {
                const res = await fetch(`/core/import/fields/${fieldId}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ corrected_value: val })
                });

                const data = await res.json();
                if (data.status === 'success') {
                    alert('Koreksi berhasil disimpan!');
                }
            } catch (err) {
                alert('Gagal menyimpan koreksi field.');
            }
        }
    }
}
</script>
@endsection
