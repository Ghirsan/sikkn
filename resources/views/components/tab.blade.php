@props(['selected' => false])

<button 
    type="button" 
    {{ $attributes->merge(['class' => 'flex whitespace-nowrap gap-2 items-center px-2 -mb-px border-b-[2px] border-transparent text-sm font-medium text-zinc-400 dark:text-white/50 data-selected:border-(--color-accent-content) data-selected:text-(--color-accent-content) hover:data-selected:text-(--color-accent-content) hover:text-zinc-800 dark:hover:text-white [&[disabled]]:opacity-50 dark:[&[disabled]]:opacity-75 [&[disabled]]:cursor-default [&[disabled]]:pointer-events-none']) }}
    @if($selected) data-selected="true" @endif
    role="tab"
    aria-selected="{{ $selected ? 'true' : 'false' }}"
>
    {{ $slot }}
</button>
