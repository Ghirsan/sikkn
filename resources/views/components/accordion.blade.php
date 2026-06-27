@props([
    'heading',
    'description' => null,
    'defaultOpen' => true,
    'contentClass' => 'pt-4',
])

<div class="block pt-4 first:pt-0 pb-4 last:pb-0 border-b last:border-b-0 border-zinc-800/10 dark:border-white/10" x-data="{ open: {{ $defaultOpen ? 'true' : 'false' }} }">
    <button type="button" @click="open = !open" class="group flex items-center w-full text-start text-sm font-medium justify-between [&>svg]:ms-6 text-zinc-800 dark:text-white cursor-pointer">
        <div class="flex-1">
            <flux:heading size="lg">{{ $heading }}</flux:heading>
            @if($description)
                <flux:text class="text-sm mt-1 text-zinc-500 dark:text-zinc-400">{{ $description }}</flux:text>
            @endif
        </div>
        <flux:icon.chevron-up variant="mini" x-show="open" class="shrink-0 text-zinc-800 dark:text-white" />
        <flux:icon.chevron-down variant="mini" x-show="!open" style="display: none;" class="shrink-0 text-zinc-300 dark:text-zinc-400 group-hover:text-zinc-800 dark:group-hover:text-white" />
    </button>
    <div x-show="open" x-collapse>
        <div class="{{ $contentClass }}">
            {{ $slot }}
        </div>
    </div>
</div>
