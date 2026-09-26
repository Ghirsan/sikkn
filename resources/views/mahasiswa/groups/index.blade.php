<x-layouts::app :title="__('Kelompok Saya')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('Kelompok Saya') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Informasi tim KKN dan lokasi penugasan Anda.') }}</flux:text>
        </div>
        <flux:separator />
        <livewire:mahasiswa.my-group />
    </div>
</x-layouts::app>
