<x-layouts::app :title="__('Buku Pembimbingan')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Buku Pembimbingan') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Catat sesi konsultasi dan pembimbingan bersama DPL.') }}</flux:text>
            </div>
            <flux:button variant="filled" icon="plus">{{ __('Tambah Catatan') }}</flux:button>
        </div>
        <flux:separator />
        <livewire:mahasiswa.mentoring-logs />
    </div>
</x-layouts::app>
