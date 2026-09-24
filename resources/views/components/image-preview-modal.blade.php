@props([
    'name',
    'url',
])

<flux:modal name="{{ $name }}" class="max-w-4xl w-full space-y-4">
    <div>
        <flux:heading size="lg">{{ __('Preview Gambar') }}</flux:heading>
    </div>
    <div>
        <img src="{{ $url }}" alt="Preview" class="w-full h-auto max-h-[75vh] object-contain rounded-lg border border-zinc-200 dark:border-zinc-700" />
    </div>
</flux:modal>
