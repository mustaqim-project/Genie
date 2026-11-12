@extends('layouts.app')

@section('title', 'Papan Tugas & Delegasi Rekan (To-Do)')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">F23 Core Task</span>
                <span class="text-xs text-neutral-400">Genie ERP v2.0</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white mt-1">Papan Delegasi Tugas (To-Do Kanban)</h1>
            <p class="text-sm text-neutral-400">Pantau tugas operasional pabean, batas waktu dokumen, dan pendelegasian antar rekan kerja.</p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Filter View Scope -->
            <div class="inline-flex rounded-xl bg-neutral-900 border border-neutral-800 p-1">
                <a href="{{ route('todos.index', ['scope' => 'my_tasks']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $viewScope === 'my_tasks' ? 'bg-indigo-600 text-white shadow-sm' : 'text-neutral-400 hover:text-white' }}">Tugas Saya</a>
                <a href="{{ route('todos.index', ['scope' => 'delegated']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $viewScope === 'delegated' ? 'bg-indigo-600 text-white shadow-sm' : 'text-neutral-400 hover:text-white' }}">Didelegasikan</a>
                <a href="{{ route('todos.index', ['scope' => 'all']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $viewScope === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-neutral-400 hover:text-white' }}">Semua Divisi</a>
            </div>

            <!-- Tombol Buat Tugas -->
            <button onclick="document.getElementById('modalNewTodo').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-indigo-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Tugas Baru
            </button>
        </div>
    </div>

    <!-- Kanban Grid (3 Kolom Apple HIG Bento) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Kolom 1: TODO -->
        <div class="bg-neutral-900/60 backdrop-blur-xl border border-neutral-800/80 rounded-2xl p-4 flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-800 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    <h3 class="font-semibold text-sm text-neutral-200">Antrean (To-Do)</h3>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-md bg-neutral-800 text-neutral-400 font-mono">{{ $kanban['TODO']->count() }}</span>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto max-h-[calc(100vh-280px)] pr-1">
                @forelse($kanban['TODO'] as $t)
                    @include('core.todos._todo_card', ['todo' => $t, 'targetStatus' => 'IN_PROGRESS', 'actionLabel' => 'Mulai'])
                @empty
                    <div class="py-8 text-center text-xs text-neutral-500 border border-dashed border-neutral-800 rounded-xl">Tidak ada tugas antrean.</div>
                @endforelse
            </div>
        </div>

        <!-- Kolom 2: IN PROGRESS -->
        <div class="bg-neutral-900/60 backdrop-blur-xl border border-neutral-800/80 rounded-2xl p-4 flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-800 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-400 animate-pulse"></span>
                    <h3 class="font-semibold text-sm text-neutral-200">Sedang Dikerjakan</h3>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-md bg-neutral-800 text-neutral-400 font-mono">{{ $kanban['IN_PROGRESS']->count() }}</span>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto max-h-[calc(100vh-280px)] pr-1">
                @forelse($kanban['IN_PROGRESS'] as $t)
                    @include('core.todos._todo_card', ['todo' => $t, 'targetStatus' => 'DONE', 'actionLabel' => 'Selesaikan'])
                @empty
                    <div class="py-8 text-center text-xs text-neutral-500 border border-dashed border-neutral-800 rounded-xl">Tidak ada tugas aktif.</div>
                @endforelse
            </div>
        </div>

        <!-- Kolom 3: DONE -->
        <div class="bg-neutral-900/60 backdrop-blur-xl border border-neutral-800/80 rounded-2xl p-4 flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-800 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    <h3 class="font-semibold text-sm text-neutral-200">Selesai (Done)</h3>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-md bg-neutral-800 text-neutral-400 font-mono">{{ $kanban['DONE']->count() }}</span>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto max-h-[calc(100vh-280px)] pr-1">
                @forelse($kanban['DONE'] as $t)
                    @include('core.todos._todo_card', ['todo' => $t, 'targetStatus' => 'TODO', 'actionLabel' => 'Buka Ulang'])
                @empty
                    <div class="py-8 text-center text-xs text-neutral-500 border border-dashed border-neutral-800 rounded-xl">Belum ada tugas selesai.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal Buat Tugas Baru -->
<div id="modalNewTodo" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm hidden p-4">
    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-neutral-800">
            <h3 class="font-bold text-white text-base">Buat Tugas & Delegasikan</h3>
            <button onclick="document.getElementById('modalNewTodo').classList.add('hidden')" class="text-neutral-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('todos.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-neutral-300 mb-1">Judul Tugas *</label>
                <input type="text" name="title" required placeholder="Contoh: Periksa Kelengkapan COO & BL Project ATS-202611-001" class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-300 mb-1">Deskripsi & Catatan</label>
                <textarea name="description" rows="2" placeholder="Detail instruksi untuk penerima tugas..." class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-neutral-300 mb-1">Prioritas</label>
                    <select name="priority" class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-2 text-xs text-white">
                        <option value="LOW">Rendah (Low)</option>
                        <option value="MEDIUM" selected>Normal (Medium)</option>
                        <option value="HIGH">Tinggi (High)</option>
                        <option value="URGENT">Mendesak (Urgent)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-300 mb-1">Tenggat Waktu</label>
                    <input type="date" name="due_date" value="{{ now()->addDays(2)->toDateString() }}" class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-2 text-xs text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-neutral-300 mb-1">Tugaskan Kepada (PIC)</label>
                    <select name="assigned_to_user_id" class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-2 text-xs text-white">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $u->id === auth()->id() ? 'selected' : '' }}>{{ $u->name }} ({{ $u->role }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-300 mb-1">Terkait Project Shipment</label>
                    <select name="shipment_id" class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-2 text-xs text-white">
                        <option value="">-- Umum / Tanpa Shipment --</option>
                        @foreach($shipments as $s)
                            <option value="{{ $s->id }}">{{ $s->project_no }} - {{ $s->nomor_aju ?? $s->nomor_bl_awb }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Checklist Item Mini -->
            <div>
                <label class="block text-xs font-semibold text-neutral-300 mb-1">Checklist Pekerjaan (Opsional)</label>
                <div class="space-y-2">
                    <input type="text" name="checklist_items[]" placeholder="Langkah 1: Verifikasi dokumen..." class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-1.5 text-xs text-white">
                    <input type="text" name="checklist_items[]" placeholder="Langkah 2: Hubungi pihak shipping line..." class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-3 py-1.5 text-xs text-white">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-800">
                <button type="button" onclick="document.getElementById('modalNewTodo').classList.add('hidden')" class="px-4 py-2 bg-neutral-800 hover:bg-neutral-700 text-neutral-300 rounded-xl text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold">Simpan & Delegasikan</button>
            </div>
        </form>
    </div>
</div>
@endsection
