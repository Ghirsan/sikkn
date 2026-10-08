<x-layouts::app :title="__('Logbook Mahasiswa')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Logbook Mahasiswa') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Pemantauan catatan kegiatan harian mahasiswa KKN.') }}</flux:text>
            </div>
        </div>
        <flux:separator />
        <livewire:observer.logbooks />
    </div>
</x-layouts::app>
