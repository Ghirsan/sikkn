<x-layouts::app :title="__('Dokumen Tim KKN')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Dokumen Tim KKN') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Pantau status kelengkapan dan berkas dokumen LRK & LPK tiap tim KKN.') }}</flux:text>
            </div>
        </div>
        <flux:separator />
        <livewire:admin.documents />
    </div>
</x-layouts::app>
