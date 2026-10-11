@props([
    'label' => null,
    'description' => null,
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'disabled' => false,
])

@php
    $wireModel = $attributes->wire('model');
    $hasWireModel = $wireModel->directive() ? true : false;
    $id = $attributes->get('id', 'slider-' . Str::random(8));
@endphp

<div
    x-data="{
        @if($hasWireModel)
            value: @entangle($attributes->wire('model')),
        @else
            value: {{ $attributes->get('value', $min) }},
        @endif
        min: {{ (float) $min }},
        max: {{ (float) $max }},
        step: {{ (float) $step }},
        isFocused: false,
        get percentage() {
            let val = parseFloat(this.value);
            if (isNaN(val)) return 0;
            let clamped = Math.min(Math.max(val, this.min), this.max);
            if (this.max === this.min) return 0;
            return ((clamped - this.min) / (this.max - this.min)) * 100;
        },
        clamp() {
            let val = parseFloat(this.value);
            if (isNaN(val)) val = this.min;
            this.value = Math.min(Math.max(val, this.min), this.max);
            this.$dispatch('input', this.value);
            this.$dispatch('change', this.value);
        }
    }"
    x-modelable="value"
    {{ $attributes->except(['wire:model', 'wire:model.live', 'wire:model.live.debounce.150ms', 'wire:model.live.debounce.300ms', 'value', 'id'])->merge(['class' => 'w-full block']) }}
    data-flux-field=""
>
    @if($label)
        <label for="{{ $id }}-input" class="inline-flex items-center text-sm font-medium text-zinc-800 dark:text-white mb-2" data-flux-label="">
            {{ $label }}
        </label>
    @endif

    @if($description)
        <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">
            {{ $description }}
        </div>
    @endif

    <div class="flex items-center gap-4">
        <!-- Slider Control Track -->
        <div class="flex flex-col justify-center w-full min-h-6 isolate select-none relative touch-none @if($disabled) opacity-50 @endif" data-flux-control="" data-flux-slider="">
            <div class="h-full flex flex-col justify-center" data-flux-slider-track="">
                <div data-flux-slider-track="" class="shrink-0 relative h-1.5 bg-zinc-200 rounded-full dark:bg-white/10 select-none">
                    <!-- Indicator / Active track -->
                    <div class="relative w-full h-full rounded-full overflow-hidden select-none">
                        <div data-flux-slider-indicator="" class="absolute inset-y-0 left-0 bg-accent rounded-full" :style="'width: ' + percentage + '%;'"></div>
                    </div>

                    <!-- Thumb knob -->
                    <div data-flux-slider-thumb="" 
                         class="absolute top-1/2 size-4 rounded-full bg-white ring ring-black/15 shadow-[0px_1px_2px_0px_rgba(0,0,0,.05),0px_2px_4px_0px_rgba(0,0,0,.1)] select-none -translate-y-1/2 -translate-x-1/2 dark:ring-black/30 pointer-events-none"
                         :class="{ 'ring-2 ring-zinc-800 dark:ring-white ring-offset-2 ring-offset-white dark:ring-offset-zinc-900': isFocused }"
                         :style="'left: calc(' + percentage + '% + ' + ((0.5 - percentage / 100) * 8) + 'px);'">
                    </div>
                </div>
            </div>

            <!-- Native range input overlay for interaction and accessibility -->
            <input type="range" 
                   id="{{ $id }}-range"
                   x-model.number="value" 
                   min="{{ $min }}" 
                   max="{{ $max }}" 
                   step="{{ $step }}"
                   @input="$dispatch('input', value); $dispatch('change', value);"
                   @focus="isFocused = true"
                   @blur="isFocused = false"
                   @if($disabled) disabled @endif
                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10 disabled:cursor-not-allowed"
                   aria-label="{{ $label ?? 'Slider' }}"
            >
        </div>

        <!-- Number Input Box -->
        <div class="relative block group/input w-20 shrink-0 tabular-nums" data-flux-input="">
            <input type="number" 
                   id="{{ $id }}-input"
                   x-model.number="value" 
                   min="{{ $min }}" 
                   max="{{ $max }}" 
                   step="{{ $step }}"
                   @input="$dispatch('input', value); $dispatch('change', value);"
                   @blur="clamp()"
                   @keydown.enter="clamp()"
                   @if($disabled) disabled @endif
                   class="w-full border block disabled:shadow-none dark:shadow-none appearance-none text-sm rounded-md py-1.5 h-8 leading-[1.125rem] px-2.5 bg-white dark:bg-white/10 dark:disabled:bg-white/[7%] text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200 dark:border-white/10 dark:disabled:border-white/5 focus:outline-hidden focus:ring-2 focus:ring-accent"
            >
        </div>
    </div>
</div>
