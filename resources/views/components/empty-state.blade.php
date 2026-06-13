@props(['icon' => 'inbox', 'heading', 'description' => null])

<div class="px-6 py-12 text-center">
    <flux:icon :name="$icon" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
    <flux:heading size="lg" class="mt-4">{{ $heading }}</flux:heading>
    @if($description)
        <flux:text class="mt-2">{{ $description }}</flux:text>
    @endif
    {{ $slot }}
</div>
