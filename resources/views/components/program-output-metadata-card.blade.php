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
@endphp

<div class="space-y-2 rounded-lg border border-zinc-200 p-3 dark:border-white/10">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <flux:text class="font-medium">{{ $title }}</flux:text>
            <flux:text variant="subtle" class="text-xs">
                {{ $siteName }}{{ $typeLabel ? ' · '.$typeLabel : '' }}
            </flux:text>
        </div>
        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="shrink-0 text-sm font-medium text-accent hover:underline">
            {{ __('Buka tautan') }}
        </a>
    </div>

    @if($description)
        <flux:text variant="subtle" class="text-sm">{{ $description }}</flux:text>
    @endif

    <flux:text class="truncate text-xs text-zinc-500" title="{{ $url }}">{{ $url }}</flux:text>
</div>