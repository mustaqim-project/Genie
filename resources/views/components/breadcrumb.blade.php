@props([
    'items' => [],
])

@if (!empty($items))
    <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
        @foreach ($items as $index => $item)
            @if ($loop->last || empty($item['url']))
                <span class="text-black dark:text-white font-semibold truncate max-w-[200px] sm:max-w-none">{{ $item['label'] }}</span>
            @else
                <a href="{{ $item['url'] }}" class="hover:text-[#007AFF] transition-colors truncate max-w-[150px] sm:max-w-none">{{ $item['label'] }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40 shrink-0"></i>
            @endif
        @endforeach
    </nav>
@endif
