@props([
    'name' => null,
    'type' => null,
    'url',
    'metadata' => [],
])

@php
    $host = parse_url($url, PHP_URL_HOST);
    $title = $metadata['title'] ?? $name ?? $url;
    $description = $metadata['description'] ?? null;
    $siteName = $metadata['site_name'] ?? $host;

    $typeLabel = match ($type) {
        'pdf' => __('PDF'),
        'video' => __('Video'),
        'image' => __('Gambar'),
        'lainnya' => __('Lainnya'),
        default => null,
    };

    $icon = match ($type) {
        'pdf' => 'document-text',
        'video' => 'play-circle',
        'image' => 'photo',
        default => 'link',
    };
@endphp

<flux:card class="overflow-hidden flex items-center min-h-10 w-full p-2">

    {{-- Icon --}}
    <div class="p-2.5 shrink-0 flex items-center">
        <flux:icon :name="$icon" class="size-5 text-zinc-500" />
    </div>

    {{-- Content --}}
    <div class="flex-1 min-w-0 overflow-hidden py-2 me-3 flex flex-col justify-center gap-0.5">
        <flux:text
            class="text-sm font-medium truncate"
            title="{{ $title }}"
        >
            {{ $title }}
        </flux:text>

        <flux:text
            variant="subtle"
            class="text-xs truncate"
        >
            {{ $siteName }}{{ $typeLabel ? ' · '.$typeLabel : '' }}
        </flux:text>

        @if($description)
            <flux:text
                variant="subtle"
                class="text-xs truncate"
                title="{{ $description }}"
            >
                {{ $description }}
            </flux:text>
        @endif
    </div>

    {{-- Action --}}
    <div class="p-1.5 shrink-0 flex items-center">
        <flux:button
            href="{{ $url }}"
            target="_blank"
            rel="noopener noreferrer"
            variant="subtle"
            size="sm"
            icon="arrow-top-right-on-square"
            :aria-label="__('Buka tautan')"
            :title="__('Buka tautan')"
        />
    </div>
</flux:card>
