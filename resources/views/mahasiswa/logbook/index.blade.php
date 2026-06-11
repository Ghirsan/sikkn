<x-layouts::app :title="__('Logbook')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Logbook') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Catat aktivitas harian KKN Anda di lapangan.') }}</flux:text>
            </div>
            <flux:button variant="filled" icon="plus">{{ __('Tambah Entri') }}</flux:button>
        </div>
        <flux:separator />
        <livewire:mahasiswa.logbook />
    </div>
</x-layouts::app>
