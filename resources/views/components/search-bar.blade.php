@props([
    'placeholder' => null,
    'model' => 'search',
    'class' => 'w-full sm:w-64'
])

@php
    $placeholder = $placeholder ?? __('Pencarian...');
@endphp

<flux:input 
    wire:model.live.debounce.300ms="{{ $model }}" 
    :placeholder="$placeholder" 
    icon="magnifying-glass" 
    size="sm" 
    {{ $attributes->merge(['class' => $class]) }} 
/>
