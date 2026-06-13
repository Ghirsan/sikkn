@props(['icon', 'color' => 'zinc', 'label', 'value'])

<flux:card class="flex items-center gap-3">
    <flux:icon :name="$icon" variant="mini" @class([
        match($color) {
            'blue' => 'text-blue-500',
            'green' => 'text-green-500',
            'amber' => 'text-amber-500',
            'red' => 'text-red-500',
            'purple' => 'text-purple-500',
            'neutral' => 'text-neutral-500',
            default => 'text-zinc-500',
        },
    ]) />
    <div>
        <flux:text>{{ $label }}</flux:text>
        <flux:heading size="xl">{{ $value }}</flux:heading>
    </div>
</flux:card>
