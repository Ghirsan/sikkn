@props([
    'modelName',
    'previewUrl' => null,
    'checkAction',
    'verifiedName',
    'label' => null,
    'placeholder' => null,
    'description' => null,
])

<div
    {{ $attributes->merge(['class' => 'space-y-3']) }}
    x-data
    x-on:input.debounce.800ms="$wire.call('{{ $checkAction }}')"
>
    <flux:input
        wire:model.live.debounce.800ms="{{ $modelName }}"
        :label="$label"
        :placeholder="$placeholder"
        aria-describedby="{{ $modelName }}-description"
    />

    @if($description)
        <flux:text id="{{ $modelName }}-description" variant="subtle" class="text-xs">
            {{ $description }}
        </flux:text>
    @endif

    <flux:error name="{{ $modelName }}" />

    @if($previewUrl)
        <flux:card
            variant="soft"
            class="flex items-center justify-center overflow-hidden p-0"
            style="width: 180px; height: 180px;"
            x-data="{ loaded: false, failed: false }"
        >
            <div class="p-3 text-center" x-show="!loaded && !failed">
                <flux:text variant="subtle" class="text-sm">{{ __('Memuat pratinjau...') }}</flux:text>
            </div>

            <img
                src="{{ $previewUrl }}"
                alt="{{ __('Pratinjau foto dokumentasi') }}"
                class="max-h-full max-w-full object-contain"
                x-show="loaded"
                x-on:load="loaded = true; $wire.set('{{ $verifiedName }}', true)"
                x-on:error="failed = true; $wire.set('{{ $verifiedName }}', false)"
                x-cloak
            >

            <div class="p-3 text-center" x-show="failed" x-cloak>
                <flux:callout variant="danger" icon="exclamation-triangle" class="p-2">
                    <flux:callout.text class="text-xs">
                        {{ __('Gambar tidak dapat dimuat. Periksa izin berbagi dan tautannya.') }}
                    </flux:callout.text>
                </flux:callout>
            </div>
        </flux:card>
    @endif
</div>
