<x-layouts::app :title="__('Rancangan Dokumen')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Rancangan Dokumen') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Pantau rancangan program kerja dan dokumen tiap tim KKN.') }}</flux:text>
            </div>
        </div>
        <flux:separator />
        <livewire:admin.documents />
    </div>
</x-layouts::app>
