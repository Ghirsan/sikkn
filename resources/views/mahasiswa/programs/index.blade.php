<x-layouts::app :title="__('Program Saya')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Program Kerja Saya') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Kelola usulan program kerja KKN Anda.') }}</flux:text>
            </div>
            <flux:button variant="filled" icon="plus">{{ __('Ajukan Program') }}</flux:button>
        </div>
        <flux:separator />
        <livewire:mahasiswa.programs />
    </div>
</x-layouts::app>
