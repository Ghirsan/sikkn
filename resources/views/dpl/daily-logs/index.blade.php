<x-layouts::app :title="__('Logbook Mahasiswa')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('Logbook Mahasiswa') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Tinjau dan sahkan catatan harian kegiatan individu mahasiswa bimbingan.') }}</flux:text>
        </div>
        <flux:separator />
        <livewire:dpl.student-logs />
    </div>
</x-layouts::app>
