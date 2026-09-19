@props([
    'title' => null,
    'subtitle' => null,
])

<div class="overflow-hidden flex items-center shadow-xs bg-white dark:bg-white/10 min-h-10 text-base sm:text-sm rounded-lg w-full border border-zinc-200 border-b-zinc-300/80 dark:border-white/10">
    <div class="p-2.5 shrink-0 flex items-center">
        {{ $icon ?? '' }}
    </div>
    <div class="flex-1 overflow-hidden py-2 me-3 flex flex-col justify-center gap-0.5">
        <div class="text-sm font-medium text-zinc-700 dark:text-white/80 whitespace-nowrap overflow-hidden text-ellipsis">{{ $title }}</div>
        <div class="text-xs text-zinc-500">{{ $subtitle }}</div>
    </div>
    @if(isset($action))
    <div class="p-1.5 shrink-0 flex items-center">
        {{ $action }}
    </div>
    @endif
</div>
