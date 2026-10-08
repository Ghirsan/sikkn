<x-layouts::app :title="__('Dokumen Tim')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Dokumen Tim') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Dokumen LRK dan LPK kelompok KKN.') }}</flux:text>
            </div>
        </div>
        <flux:separator />
        <livewire:observer.documents />
    </div>
</x-layouts::app>
