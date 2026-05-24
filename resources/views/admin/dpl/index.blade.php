<x-layouts::app :title="__('Daftar DPL')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Daftar DPL') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Database Dosen Pembimbing Lapangan dan beban bimbingan.') }}</flux:text>
            </div>
            <flux:button variant="filled" icon="plus">{{ __('Tambah DPL') }}</flux:button>
        </div>
        <flux:separator />
        <livewire:admin.dpls />
    </div>
</x-layouts::app>
