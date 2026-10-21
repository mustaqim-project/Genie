@php
    $stages = \App\Models\Shipment::STAGES_PIPELINE;
    $picsMap = $shipment->stage_pics_map;
    $allUsers = \App\Models\User::with(['roles', 'employee'])->where('is_active', true)->orderBy('name')->get();
    $authUser = auth()->user();
    $canManagePic = $authUser?->hasRole('R-OWN') || $authUser?->hasRole('R-ADM') || $authUser?->hasRole('R-DOC-SPV') || $authUser?->isSuperAdmin();
@endphp

<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs p-5 sm:p-6 mb-6"
     x-data="{
        showPicModal: false,
        activeStageKey: '',
        activeStageTitle: '',
        activeStageRole: '',
        currentPicId: '',
        currentBackupId: '',
        selectedPicId: '',
        selectedBackupId: '',
        openModal(key, title, role, picId, backupId) {
            this.activeStageKey = key;
            this.activeStageTitle = title;
            this.activeStageRole = role;
            this.currentPicId = picId || '';
            this.currentBackupId = backupId || '';
            this.selectedPicId = picId || '';
            this.selectedBackupId = backupId || '';
            this.showPicModal = true;
        }
     }">

    <!-- Card Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-black/[0.06] dark:border-white/[0.08] mb-4 gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight">
                    Tim & PIC Project (Pembagian Tugas 11 Pos)
                </h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                    Delegasi SLA
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Penanggung jawab staf operasional per pos tahapan kerja (PIC Utama & Backup), kontrol target SLA, dan alokasi beban kerja.
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($canManagePic)
            <form action="{{ route('core.document.assign-auto-pic', $shipment->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        onclick="return confirm('Tugaskan PIC secara otomatis ke staf yang memiliki peran sesuai dengan beban kerja terendah?')"
                        class="btn-apple-filled !py-2 !px-3 text-xs inline-flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Assign Otomatis (Beban Terendah)</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Table of 11 Workflow Pos & Handlers -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-slate-500 dark:text-slate-400 font-semibold border-b border-black/[0.06] dark:border-white/[0.06]">
                <tr>
                    <th class="py-3 px-3.5 w-12 text-center">Pos</th>
                    <th class="py-3 px-3.5">Tahap Alur Kerja</th>
                    <th class="py-3 px-3.5">Peran Bertanggung Jawab</th>
                    <th class="py-3 px-3.5">PIC Pelaksana (Utama & Backup)</th>
                    <th class="py-3 px-3.5 text-center">Target SLA</th>
                    <th class="py-3 px-3.5 text-center">Status Pos</th>
                    <th class="py-3 px-3.5 text-right">Aksi Penugasan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                @foreach($stages as $i => $stg)
                    @php
                        $stKey = $stg['key'];
                        $posNumber = $i + 1;
                        $stageStatus = $shipment->getStageStatus($i);
                        $picObj = $picsMap[$stKey] ?? null;
                        $primaryUser = $picObj?->user;
                        $backupUser = $picObj?->backup;
                        $isCurrentStage = ($stageStatus === 'cur');
                        $userCanTakeOver = $authUser && $isCurrentStage && ($authUser->hasRole($stg['role']) || $canManagePic) && ($primaryUser?->id !== $authUser->id);
                    @endphp
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors {{ $isCurrentStage ? 'bg-[#007AFF]/[0.03] dark:bg-[#007AFF]/[0.06]' : '' }}">
                        <!-- Pos Number -->
                        <td class="py-3.5 px-3.5 text-center">
                            @if($stageStatus === 'done')
                                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white font-bold text-[11px] inline-flex items-center justify-center">✓</span>
                            @elseif($stageStatus === 'cur')
                                <span class="w-6 h-6 rounded-full bg-[#007AFF] text-white font-bold text-[11px] inline-flex items-center justify-center ring-2 ring-[#007AFF]/30">{{ $posNumber }}</span>
                            @else
                                <span class="w-6 h-6 rounded-full bg-black/5 dark:bg-white/10 text-slate-500 font-medium text-[11px] inline-flex items-center justify-center">{{ $posNumber }}</span>
                            @endif
                        </td>

                        <!-- Tahap Title -->
                        <td class="py-3.5 px-3.5">
                            <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>{{ $stg['title'] }}</span>
                                @if($isCurrentStage)
                                    <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                Aksi: {{ $stg['action'] }}
                            </div>
                        </td>

                        <!-- Peran -->
                        <td class="py-3.5 px-3.5">
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-black/5 dark:bg-white/10 text-slate-700 dark:text-slate-300">
                                {{ $stg['role_name'] }}
                            </span>
                        </td>

                        <!-- PIC Pelaksana -->
                        <td class="py-3.5 px-3.5">
                            <div class="flex flex-wrap items-center gap-1.5">
                                @if($primaryUser)
                                    <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#007AFF]/10 text-[#007AFF] dark:text-[#64B5FF] font-medium text-[11px] border border-[#007AFF]/20">
                                        <i data-lucide="user" class="w-3 h-3"></i>
                                        <strong>{{ $primaryUser->name }}</strong>
                                        <span class="text-[9px] uppercase px-1 rounded bg-[#007AFF]/20">Utama</span>
                                    </div>
                                    @if($backupUser)
                                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-black/5 dark:bg-white/10 text-slate-600 dark:text-slate-400 font-medium text-[10px]">
                                            <span>{{ $backupUser->name }}</span>
                                            <span class="text-[9px] uppercase px-1 rounded bg-black/10 dark:bg-white/20">Backup</span>
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 text-rose-500 dark:text-rose-400 font-semibold text-[11px]">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        <span>Belum ada PIC</span>
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Target SLA -->
                        <td class="py-3.5 px-3.5 text-center font-mono">
                            <span class="text-slate-600 dark:text-slate-400 font-semibold">{{ $stg['sla_hours'] }} Jam</span>
                        </td>

                        <!-- Status Pos -->
                        <td class="py-3.5 px-3.5 text-center">
                            @if($stageStatus === 'done')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    Selesai
                                </span>
                            @elseif($stageStatus === 'cur')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/15 text-[#007AFF] dark:text-[#64B5FF] border border-[#007AFF]/20">
                                    Berjalan
                                </span>
                            @elseif($stageStatus === 'skip')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-white/10 text-slate-500">
                                    Dilewati
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-white/10 text-slate-400 dark:text-slate-500">
                                    Menunggu
                                </span>
                            @endif
                        </td>

                        <!-- Aksi Penugasan -->
                        <td class="py-3.5 px-3.5 text-right space-x-1">
                            @if($userCanTakeOver)
                                <form action="{{ route('core.document.take-over-pic', $shipment->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="process_code" value="{{ $stKey }}">
                                    <button type="submit" 
                                            class="btn-apple-tinted !py-1 !px-2.5 text-[11px] cursor-pointer"
                                            title="Ambil alih pengerjaan tahap ini sebagai PIC">
                                        Ambil Alih
                                    </button>
                                </form>
                            @endif

                            @if($canManagePic)
                                <button type="button" 
                                        @click="openModal('{{ $stKey }}', '{{ $stg['title'] }}', '{{ $stg['role'] }}', '{{ $primaryUser?->id }}', '{{ $backupUser?->id }}')"
                                        class="btn-apple-gray !py-1 !px-2.5 text-[11px] cursor-pointer">
                                    {{ $primaryUser ? 'Ubah PIC' : '+ Set PIC' }}
                                </button>
                                @if($primaryUser)
                                    <form action="{{ route('core.document.remove-pic', $shipment->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Lepas penugasan PIC untuk tahap {{ $stg['title'] }}?')">
                                        @csrf
                                        <input type="hidden" name="process_code" value="{{ $stKey }}">
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 p-1 cursor-pointer" title="Lepas PIC">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- MODAL PENUGASAN PIC (ATUR / GANTI PIC) -->
    <div x-show="showPicModal" 
         class="fixed inset-0 z-[250] overflow-y-auto flex items-center justify-center p-4 bg-black/70 backdrop-blur-md"
         role="dialog"
         aria-modal="true"
         x-cloak>
        <div class="relative bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/10 dark:border-white/10 shadow-2xl max-w-md w-full p-6 text-xs animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 mb-4">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-black dark:text-white" x-text="'Atur PIC: ' + activeStageTitle"></h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Tugaskan staf operasional penanggung jawab pos</p>
                    </div>
                </div>
                <button type="button" @click="showPicModal = false" class="p-1 rounded text-slate-400 hover:text-black dark:hover:text-white cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('core.document.set-pic', $shipment->id) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="process_code" :value="activeStageKey">

                <div>
                    <label class="block font-semibold text-slate-800 dark:text-slate-200 mb-1">
                        Pilih PIC Utama <span class="text-rose-500">*</span>
                    </label>
                    <select name="user_id" x-model="selectedPicId" required class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white focus:outline-none focus:border-[#007AFF]">
                        <option value="">-- Pilih Karyawan PIC Utama --</option>
                        @foreach($allUsers as $u)
                            @php
                                $emp = $u->employee;
                                $uRoles = $u->roles->pluck('name')->join(', ');
                                $activeLoad = \App\Models\ShipmentPic::where('user_id', $u->id)
                                    ->whereHas('shipment', fn($sq) => $sq->whereNull('closed_at'))
                                    ->count();
                            @endphp
                            <option value="{{ $u->id }}">
                                {{ $u->name }} ({{ $uRoles ?: 'Staf' }}) — Beban: {{ $activeLoad }} tahap
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-800 dark:text-slate-200 mb-1">
                        Pilih PIC Backup (Cadangan / Pendamping)
                    </label>
                    <select name="backup_id" x-model="selectedBackupId" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white focus:outline-none focus:border-[#007AFF]">
                        <option value="">-- Tanpa PIC Backup --</option>
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}">
                                {{ $u->name }} ({{ $u->roles->pluck('name')->join(', ') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-black/5 dark:border-white/5 text-[11px] text-slate-500 dark:text-slate-400">
                    <i data-lucide="info" class="w-3.5 h-3.5 inline text-[#007AFF] mr-1"></i>
                    Staf yang ditugaskan akan mendapatkan notifikasi penugasan dan pengingat batas waktu target SLA pengerjaan.
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                    <button type="button" @click="showPicModal = false" class="btn-apple-gray !h-8 text-[11px] cursor-pointer">Batal</button>
                    <button type="submit" class="btn-apple-filled !h-8 text-[11px] flex items-center space-x-1 cursor-pointer">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>Simpan Penugasan PIC</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
