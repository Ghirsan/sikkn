@props([
    'placeholder' => __('Pencarian...'),
    'model' => 'search',
    'class' => 'w-full sm:w-64'
])

<flux:input 
    wire:model.live.debounce.300ms="{{ $model }}" 
    :placeholder="$placeholder" 
    icon="magnifying-glass" 
    size="sm" 
    {{ $attributes->merge(['class' => $class]) }} 
/>
