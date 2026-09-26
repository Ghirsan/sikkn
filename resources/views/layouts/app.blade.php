<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        <flux:breadcrumbs class="mb-6">
            <flux:breadcrumbs.item icon="home" href="{{ route('dashboard') }}" wire:navigate />
            @foreach ($breadcrumbs ?? [['label' => $title ?? __('Halaman')]] as $breadcrumb)
                @if (isset($breadcrumb['url']))
                    <flux:breadcrumbs.item href="{{ $breadcrumb['url'] }}" wire:navigate>
                        {{ $breadcrumb['label'] }}
                    </flux:breadcrumbs.item>
                @else
                    <flux:breadcrumbs.item>{{ $breadcrumb['label'] }}</flux:breadcrumbs.item>
                @endif
            @endforeach
        </flux:breadcrumbs>

        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
