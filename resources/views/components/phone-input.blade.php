@props([
    'label' => null,
    'name' => null,
    'countryCode' => null,
])

@php($inputId = $attributes->get('id', $name))

<flux:field>
    @if ($label)
        <flux:label :for="$inputId">{{ $label }}</flux:label>
    @endif

    @if ($countryCode)
        <flux:input.group>
            <flux:input.group.prefix>{{ $countryCode }}</flux:input.group.prefix>
            <flux:input
                type="tel"
                inputmode="tel"
                autocomplete="tel"
                :id="$inputId"
                {{ $attributes->except('id') }}
            />
        </flux:input.group>
    @else
        <flux:input
            type="tel"
            inputmode="tel"
            autocomplete="tel"
            :id="$inputId"
            {{ $attributes->except('id') }}
        />
    @endif

    @if ($name)
        <flux:error :name="$name" />
    @endif
</flux:field>
