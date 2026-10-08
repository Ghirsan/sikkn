<x-layouts::app :title="__('Mahasiswa Per Prodi')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Mahasiswa Per Prodi') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Pemantauan keikutsertaan mahasiswa dalam KKN.') }}</flux:text>
            </div>
        </div>
        <flux:separator />
        <livewire:observer.students />
    </div>
</x-layouts::app>
