@props(['icon', 'color' => 'zinc', 'label', 'value'])

<flux:card class="flex items-center gap-3">
    <flux:icon :name="$icon" variant="mini" @class([
        match($color) {
            'blue', 'purple', 'accent' => 'text-accent',
            'green', 'success' => 'text-emerald-500 dark:text-emerald-400',
            'amber', 'warning' => 'text-amber-500 dark:text-amber-400',
            'red', 'danger' => 'text-red-500 dark:text-red-400',
            'zinc', 'neutral' => 'text-zinc-500 dark:text-zinc-400',
            default => 'text-zinc-500 dark:text-zinc-400',
        },
    ]) />
    <div>
        <flux:text>{{ $label }}</flux:text>
        <flux:heading size="xl">{{ $value }}</flux:heading>
    </div>
</flux:card>
