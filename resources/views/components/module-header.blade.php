@props([
    'title'       => '',
    'subtitle'    => null,
    'module'      => null,
    'breadcrumbs' => [],
    'badge'       => null,
])

<header {{ $attributes->merge(['class' => 'rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs transition-all']) }}>
    <div class="min-w-0 flex-1">
        @if (!empty($breadcrumbs))
            <x-breadcrumb :items="$breadcrumbs" />
        @endif
        <div class="flex items-center gap-2.5">
            <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight break-words sm:truncate">{{ $title }}</h1>
            @if ($badge)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.06] dark:bg-white/[0.1] text-black/70 dark:text-white/70">
                    {{ $badge }}
                </span>
            @endif
        </div>
        @if ($subtitle)
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5 leading-snug">{{ $subtitle }}</p>
        @endif
    </div>

    @php
        $actionContent = isset($actions) && $actions->isNotEmpty() ? $actions : (isset($slot) && $slot->isNotEmpty() ? $slot : null);
    @endphp
    @if ($actionContent)
        <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0 flex-wrap sm:flex-nowrap">
            {{ $actionContent }}
        </div>
    @endif
</header>
