<x-layouts::app :title="__('Kelompok KKN')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Kelompok KKN') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Kelola tim kerja mahasiswa, penugasan DPL, dan lokasi KKN.') }}</flux:text>
            </div>
        </div>
        <flux:separator />
        <livewire:admin.groups />
    </div>
</x-layouts::app>
