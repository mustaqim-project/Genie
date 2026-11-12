<div class="bg-neutral-950/70 border border-neutral-800/80 rounded-xl p-3.5 space-y-3 hover:border-neutral-700 transition">
    <div class="flex items-start justify-between gap-2">
        <span class="px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide uppercase
            @if($todo->priority === 'URGENT') bg-rose-500/10 text-rose-400 border border-rose-500/20
            @elseif($todo->priority === 'HIGH') bg-amber-500/10 text-amber-400 border border-amber-500/20
            @elseif($todo->priority === 'LOW') bg-neutral-800 text-neutral-400
            @else bg-blue-500/10 text-blue-400 border border-blue-500/20 @endif">
            {{ $todo->priority }}
        </span>

        @if($todo->due_date)
            <span class="text-[11px] text-neutral-400 font-mono flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ $todo->due_date->format('d M') }}
            </span>
        @endif
    </div>

    <div>
        <h4 class="text-xs font-semibold text-white leading-snug">{{ $todo->title }}</h4>
        @if($todo->description)
            <p class="text-[11px] text-neutral-400 mt-1 line-clamp-2 leading-relaxed">{{ $todo->description }}</p>
        @endif
    </div>

    @if($todo->shipment)
        <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-neutral-900 border border-neutral-800 text-[10px] text-indigo-400">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
            {{ $todo->shipment->project_no }}
        </div>
    @endif

    <!-- Checklists Mini Progress -->
    @if($todo->checklists->count() > 0)
        @php
            $doneCount = $todo->checklists->where('is_completed', true)->count();
            $totalCount = $todo->checklists->count();
            $pct = round(($doneCount / $totalCount) * 100);
        @endphp
        <div class="space-y-1">
            <div class="flex items-center justify-between text-[10px] text-neutral-400">
                <span>Checklist</span>
                <span>{{ $doneCount }}/{{ $totalCount }} ({{ $pct }}%)</span>
            </div>
            <div class="w-full h-1.5 bg-neutral-900 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    @endif

    <!-- Footer Card: Assignee & Action -->
    <div class="flex items-center justify-between pt-2 border-t border-neutral-800/80">
        <div class="flex items-center gap-1.5">
            <div class="w-5 h-5 rounded-full bg-neutral-800 text-neutral-300 text-[10px] flex items-center justify-center font-bold">
                {{ substr($todo->assignee?->name ?? 'U', 0, 1) }}
            </div>
            <span class="text-[11px] text-neutral-300 font-medium">{{ $todo->assignee?->name ?? 'Belum Ditugaskan' }}</span>
        </div>

        <form action="{{ route('todos.status', $todo->id) }}" method="POST">
            @csrf
            <input type="hidden" name="status" value="{{ $targetStatus }}">
            <button type="submit" class="text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 hover:underline">
                {{ $actionLabel }} &rarr;
            </button>
        </form>
    </div>
</div>
